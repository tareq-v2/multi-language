from textblob import TextBlob
import sys
import json
import logging

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)

def analyze_mood(text):
    """Analyze text sentiment and return mood category"""
    try:
        analysis = TextBlob(text)
        polarity = analysis.sentiment.polarity
        subjectivity = analysis.sentiment.subjectivity

        # Determine mood based on sentiment
        if polarity > 0.3:
            mood = "happy"
        elif polarity < -0.3:
            mood = "sad"
        else:
            if subjectivity > 0.6:
                mood = "calm"
            else:
                mood = "neutral"

        return {
            "mood": mood,
            "polarity": polarity,
            "subjectivity": subjectivity,
            "error": None
        }

    except Exception as e:
        logging.error(f"Analysis error: {str(e)}")
        return {
            "mood": None,
            "polarity": None,
            "subjectivity": None,
            "error": str(e)
        }

if __name__ == "__main__":
    try:
        # Read input from command line arguments
        text = sys.argv[1] if len(sys.argv) > 1 else ""

        if not text:
            result = {"error": "No text provided"}
        else:
            result = analyze_mood(text)

        print(json.dumps(result))

    except Exception as e:
        print(json.dumps({"error": str(e)}))
