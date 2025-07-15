<!DOCTYPE html>
<html>
<head>
    <title>Website Scraper</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .log-container {
            max-height: 400px;
            overflow-y: auto;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4">Website Information Scraper</h1>
        
        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
            @if(session('log_file'))
            <div class="mt-3">
                <a href="{{ route('scraper.log', ['filename' => session('log_file')]) }}" 
                   target="_blank" class="btn btn-info">
                    View Processing Log
                </a>
            </div>
            @endif
        </div>
        @endif
        
        @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
            @if(session('log_file'))
            <div class="mt-3">
                <a href="{{ route('scraper.log', ['filename' => session('log_file')]) }}" 
                   target="_blank" class="btn btn-danger">
                    View Error Log
                </a>
            </div>
            @endif
        </div>
        @endif
        
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('scraper.process') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="url_file" class="form-label">Upload URL List (Text File)</label>
                        <input class="form-control" type="file" id="url_file" name="url_file" required accept=".txt">
                        <div class="form-text">
                            File should contain one URL per line. Quotes will be automatically removed.
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Start Scraping</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>