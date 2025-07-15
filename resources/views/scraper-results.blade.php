<!DOCTYPE html>
<html>
<head>
    <title>Scraping Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .website-image {
            max-width: 50px;
            max-height: 50px;
            border-radius: 4px;
        }
        .badge-custom {
            font-size: 0.85em;
            margin-right: 4px;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Scraping Results</h1>
            <a href="{{ route('scraper.form') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Back to Upload
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Website</th>
                                <th>Title</th>
                                <th>Image</th>
                                <th>Contact Info</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($websites as $website)
                            <tr>
                                <td>
                                    <a href="{{ $website->url }}" target="_blank" class="text-decoration-none">
                                        {{ $website->url }}
                                    </a>
                                </td>
                                <td>{{ $website->title ?? 'N/A' }}</td>
                                <td>
                                    @if($website->image)
                                        <img src="{{ $website->image }}" alt="Logo" class="website-image">
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($website->phone_numbers)
                                        @foreach(json_decode($website->phone_numbers) as $phone)
                                            <span class="badge bg-info badge-custom">{{ $phone }}</span>
                                        @endforeach
                                    @endif

                                    @if($website->emails)
                                        @foreach(json_decode($website->emails) as $email)
                                            <span class="badge bg-success badge-custom">{{ $email }}</span>
                                        @endforeach
                                    @endif

                                    @if(!$website->phone_numbers && !$website->emails)
                                        <span class="text-muted">No contact found</span>
                                    @endif
                                </td>
                                <td>
                                    @if($website->error)
                                        <span class="badge bg-danger">Error</span>
                                    @else
                                        <span class="badge bg-success">Success</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $websites->links() }}
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            <strong>Note:</strong> Scraping results are automatically saved to the database.
            Upload the same file again to update existing records.
        </div>
    </div>
</body>
</html>
