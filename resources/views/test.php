<?php

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\ScrapedWebsite;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class WebsiteScraperController extends Controller
{
    public function showForm()
    {
        return view('scraper-form');
    }

    public function processFile(Request $request)
    {
        // Remove PHP execution time limit
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $request->validate([
            'url_file' => 'required|file|mimes:txt|max:10240'
        ]);

        try {
            // Store uploaded file
            $file = $request->file('url_file');
            $filePath = $file->store('url-uploads');
            $fullFilePath = Storage::path($filePath);

            // Python paths
            $pythonPath = PHP_OS_FAMILY === 'Windows'
                ? base_path('venv\Scripts\python.exe')
                : base_path('venv/bin/python');

            $pythonScript = base_path('python_scripts/website_scraper.py');

            // Output file
            $outputFilename = 'scraped_results_' . time() . '.json';
            $outputPath = Storage::path('scraped-results/' . $outputFilename);

            // Create directory if doesn't exist
            $outputDir = dirname($outputPath);
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            // Build command
            $command = [
                $pythonPath,
                $pythonScript,
                $fullFilePath,
                $outputPath
            ];

            // Execute Python script in background
            $process = new Process($command);
            $process->setTimeout(null); // No timeout
            $process->setIdleTimeout(null);

            // Run asynchronously
            $process->start();

            // Store process ID for monitoring
            $pid = $process->getPid();
            Storage::put('scraper_pids/'.$pid.'.txt', $pid);

            return redirect()->route('scraper.results')
                             ->with('success', 'Scraping started in background! PID: '.$pid);

        } catch (\Exception $e) {
            Log::error("File processing error: " . $e->getMessage());
            return back()->with('error', 'Processing failed: ' . $e->getMessage());
        }
    }

    private function saveResults($outputPath)
    {
        // Same as before
    }

    public function showResults()
    {
        $websites = ScrapedWebsite::paginate(20);
        return view('scraper-results', compact('websites'));
    }

    public function checkStatus($pid)
    {
        $pidFile = 'scraper_pids/'.$pid.'.txt';

        if (!Storage::exists($pidFile)) {
            return response()->json(['status' => 'completed']);
        }

        // Check if process is still running
        $command = PHP_OS_FAMILY === 'Windows'
            ? "tasklist /FI \"PID eq $pid\""
            : "ps -p $pid";

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful() || empty($process->getOutput())) {
            Storage::delete($pidFile);
            return response()->json(['status' => 'completed']);
        }

        return response()->json(['status' => 'running']);
    }
}
