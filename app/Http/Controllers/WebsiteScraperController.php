<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\ScrapedWebsite;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\DB;

class WebsiteScraperController extends Controller
{
    public function processFile(Request $request)
    {
        Log::info('Scraper process started');
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        try {
            // Validate input
            $request->validate([
                'url_file' => 'required|file|mimes:txt|max:10240'
            ]);
            Log::info('File validation passed');

            // Store uploaded file
            $file = $request->file('url_file');
            $filePath = $file->store('url-uploads');
            $fullFilePath = Storage::path($filePath);
            Log::info("File stored at: $fullFilePath");

            // Python paths
            $pythonPath = PHP_OS_FAMILY === 'Windows'
                ? base_path('venv\Scripts\python.exe')
                : base_path('venv/bin/python');
            
            $pythonScript = base_path('python_scripts/website_scraper.py');
            Log::info("Python path: $pythonPath");
            Log::info("Script path: $pythonScript");

            // Verify Python executable exists
            if (!file_exists($pythonPath)) {
                throw new \Exception("Python executable not found at: $pythonPath");
            }
            
            // Verify Python script exists
            if (!file_exists($pythonScript)) {
                throw new \Exception("Python script not found at: $pythonScript");
            }

            // Output file
            $outputFilename = 'scraped_results_' . time() . '.json';
            $outputPath = Storage::path('scraped-results/' . $outputFilename);
            $outputDir = dirname($outputPath);
            Log::info("Output path: $outputPath");

            // Create directory if doesn't exist
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
                Log::info("Created output directory: $outputDir");
            }

            // Build command
            $command = [
                $pythonPath,
                $pythonScript,
                $fullFilePath,
                $outputPath
            ];
            
            // Create log file
            $logPath = Storage::path('scraper_logs/' . time() . '.log');
            $logDir = dirname($logPath);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            // Run command with detailed logging
            $process = new Process($command);
            $process->setTimeout(null);
            
            // Capture all output
            $process->start();
            $process->wait();
            
            // Log process output
            $output = $process->getOutput();
            $errorOutput = $process->getErrorOutput();
            $exitCode = $process->getExitCode();
            
            Log::info("Python exit code: $exitCode");
            Log::info("Python output:\n" . $output);
            Log::info("Python errors:\n" . $errorOutput);
            
            // Save output to log file
            file_put_contents($logPath, "EXIT CODE: $exitCode\n\nOUTPUT:\n$output\n\nERRORS:\n$errorOutput");

            // Check for errors
            if ($exitCode !== 0) {
                throw new \Exception("Python script failed with exit code $exitCode. Check log: " . basename($logPath));
            }
            
            // Check if output file was created
            if (!file_exists($outputPath)) {
                throw new \Exception("Output file not created at: $outputPath");
            }
            
            // Import results
            $this->saveResults($outputPath);
            
            return redirect()->route('scraper.results')
                ->with('success', 'Scraping completed successfully!')
                ->with('log_file', basename($logPath));

        } catch (\Exception $e) {
            Log::error("File processing error: " . $e->getMessage());
            Log::error("Exception trace: " . $e->getTraceAsString());
            return back()->with('error', 'Processing failed: ' . $e->getMessage());
        }
    }

    private function saveResults($outputPath)
    {
        Log::info("Starting result import from: $outputPath");
        
        if (!file_exists($outputPath)) {
            throw new \Exception("Result file not found at: $outputPath");
        }

        $json = file_get_contents($outputPath);
        
        if (empty($json)) {
            throw new \Exception("Result file is empty");
        }
        
        Log::info("Loaded JSON content, size: " . strlen($json) . " bytes");

        $results = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMsg = "JSON decode error: " . json_last_error_msg();
            Log::error($errorMsg);
            Log::error("JSON snippet: " . substr($json, 0, 500));
            throw new \Exception($errorMsg);
        }
        
        Log::info("Decoded JSON, found " . count($results) . " records");

        DB::transaction(function () use ($results) {
            $count = 0;
            $errors = 0;
            
            foreach ($results as $result) {
                try {
                    if (empty($result['url'])) {
                        Log::warning("Skipping record without URL: " . json_encode($result));
                        continue;
                    }
                    
                    ScrapedWebsite::updateOrCreate(
                        ['url' => $result['url']],
                        [
                            'title' => $result['title'] ?? null,
                            'description' => $result['description'] ?? null,
                            'image' => $result['image'] ?? null,
                            'phone_numbers' => $result['phone'] ? json_encode($result['phone']) : null,
                            'emails' => $result['email'] ? json_encode($result['email']) : null,
                            'error' => $result['error'] ?? null,
                        ]
                    );
                    $count++;
                } catch (\Exception $e) {
                    $errors++;
                    Log::error("Failed to save record for {$result['url']}: " . $e->getMessage());
                }
            }
            
            Log::info("Imported $count records with $errors errors");
        });
    }

    public function showResults()
    {
        $websites = ScrapedWebsite::paginate(20);
        return view('scraper-results', compact('websites'));
    }
    
    public function showForm()
    {
        return view('scraper-form');
    }
    
    public function showLog($filename)
    {
        $logPath = Storage::path('scraper_logs/' . $filename);
        
        if (!file_exists($logPath)) {
            abort(404, 'Log file not found');
        }
        
        return response()->file($logPath);
    }
}