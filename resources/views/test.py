import sys
import json
import os
import re
import time
import certifi
import urllib3
import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin
from fake_useragent import UserAgent
from requests.adapters import HTTPAdapter
from requests.packages.urllib3.util.retry import Retry

# Disable SSL warnings
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

def get_website_info(url):
    result = {
        'url': url,
        'title': None,
        'description': None,
        'image': None,
        'phone': [],
        'email': [],
        'error': None
    }

    try:
        # Clean URL from quotes and whitespace
        url = url.strip().strip('"')

        # Add protocol if missing
        if not url.startswith('http'):
            url = 'http://' + url

        # Use rotating user agents
        ua = UserAgent()
        headers = {'User-Agent': ua.random}

        # Create session with retry logic
        session = requests.Session()
        retry_strategy = Retry(
            total=3,
            backoff_factor=0.5,
            status_forcelist=[429, 500, 502, 503, 504],
            allowed_methods=["GET"]
        )
        adapter = HTTPAdapter(max_retries=retry_strategy)
        session.mount("https://", adapter)
        session.mount("http://", adapter)

        # Use system CA bundle
        ca_bundle = certifi.where()

        response = session.get(
            url,
            headers=headers,
            timeout=20,
            allow_redirects=True,
            verify=ca_bundle  # Enable SSL verification
        )
        response.raise_for_status()

        # Handle encoding
        if response.encoding == 'ISO-8859-1':
            response.encoding = 'utf-8'

        soup = BeautifulSoup(response.text, 'html.parser')

        # Get title
        if soup.title and soup.title.string:
            result['title'] = soup.title.string.strip()

        # Get description
        meta_desc = soup.find('meta', attrs={'name': 'description'})
        if not meta_desc:
            meta_desc = soup.find('meta', attrs={'property': 'og:description'})

        if meta_desc and meta_desc.get('content'):
            result['description'] = meta_desc['content'].strip()[:500]

        # Get image
        icon_link = soup.find('link', rel=re.compile('icon|apple-touch-icon', re.I))
        if icon_link and icon_link.get('href'):
            result['image'] = urljoin(url, icon_link['href'])
        else:
            logo = soup.find('img', src=re.compile(r'logo', re.I))
            if logo and logo.get('src'):
                result['image'] = urljoin(url, logo['src'])
            else:
                # Fallback to Google favicon service
                result['image'] = f"https://www.google.com/s2/favicons?domain={url}&sz=64"

        # Find phone numbers
        phone_pattern = re.compile(r'(\+?\d{1,3}[-.\s]?)?\(?\d{2,4}\)?[-.\s]?\d{2,4}[-.\s]?\d{3,4}')
        text_content = soup.get_text()
        phones = set(phone_pattern.findall(text_content))
        result['phone'] = list(phones) if phones else []

        # Find email addresses
        email_pattern = re.compile(r'\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b')
        emails = set(email_pattern.findall(text_content))
        result['email'] = list(emails) if emails else []

    except Exception as e:
        result['error'] = str(e)

    return result

def process_text_file(file_path):
    """Process text file with URLs, handling quoted entries"""
    urls = []
    with open(file_path, 'r', encoding='utf-8', errors='ignore') as file:
        for line in file:
            # Clean line: remove quotes, whitespace, and empty lines
            clean_line = line.strip().strip('"')
            if clean_line and not clean_line.startswith('#'):
                urls.append(clean_line)
    return list(set(urls))  # Remove duplicates

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python website_scraper.py <input_file> <output_json>")
        sys.exit(1)

    input_file = sys.argv[1]
    output_json = sys.argv[2]

    try:
        # Get URLs from text file
        urls = process_text_file(input_file)
        total = len(urls)
        print(f"Found {total} unique URLs to process")

        # Resume processing if possible
        processed_urls = set()
        results = []

        if os.path.exists(output_json):
            try:
                with open(output_json, 'r', encoding='utf-8') as f:
                    results = json.load(f)
                    processed_urls = {r['url'] for r in results}
                    print(f"Resuming from existing file with {len(results)} records")
            except:
                pass

        # Process each URL
        for i, url in enumerate(urls):
            if url in processed_urls:
                print(f"Skipping already processed URL ({i+1}/{total}): {url}")
                continue

            print(f"Processing {i+1}/{total}: {url}")
            result = get_website_info(url)
            results.append(result)

            # Save progress every 10 URLs
            if (i + 1) % 10 == 0 or i == total - 1:
                with open(output_json, 'w', encoding='utf-8') as f:
                    json.dump(results, f, ensure_ascii=False, indent=2)
                print(f"Saved progress ({len(results)} URLs processed)")

            # Be polite - delay between requests
            time.sleep(1)

        print(f"Successfully processed {len(results)} websites")

    except Exception as e:
        print(f"Fatal error: {str(e)}")
        sys.exit(1)
