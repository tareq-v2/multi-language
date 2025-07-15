<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use App\Models\MoodHistory;

class MoodController extends Controller
{
    public function analyze(Request $request)
    {
        Log::info('Mood analysis process started');
        set_time_limit(120); // 2 minutes timeout
        ini_set('memory_limit', '256M');

        try {
            // Validate input
            $request->validate([
                'text' => 'required|string|min:3|max:500'
            ]);
            Log::info('Input validation passed');

            $text = $request->input('text');
            Log::debug("Analyzing text: " . substr($text, 0, 50) . "...");

            // Python configuration
            $pythonPath = PHP_OS_FAMILY === 'Windows'
                ? base_path('venv\Scripts\python.exe')
                : base_path('venv/bin/python');
            $scriptPath = base_path('python_scripts/mood_analyzer.py');

            Log::info("Python path: $pythonPath");
            Log::info("Script path: $scriptPath");

            // Verify Python executable exists
            if (!file_exists($pythonPath)) {
                throw new \Exception("Python executable not found at: $pythonPath");
            }

            // Verify Python script exists
            if (!file_exists($scriptPath)) {
                throw new \Exception("Python script not found at: $scriptPath");
            }

            // Create log file
            $logPath = Storage::path('mood_logs/' . time() . '.log');
            $logDir = dirname($logPath);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
                Log::info("Created log directory: $logDir");
            }

            // Build command
            $command = [
                $pythonPath,
                $scriptPath,
                escapeshellarg($text)
            ];

            Log::info("Executing command: " . implode(' ', $command));

            // Run Python process
            $process = new Process($command);
            $process->setTimeout(60); // 60 seconds timeout
            $process->start();

            // Wait for process to finish with progress logging
            $startTime = time();
            while ($process->isRunning()) {
                sleep(1);
                $duration = time() - $startTime;
                Log::debug("Process running for {$duration}s...");
            }

            // Capture output
            $output = $process->getOutput();
            $errorOutput = $process->getErrorOutput();
            $exitCode = $process->getExitCode();

            Log::info("Python exit code: $exitCode");
            Log::debug("Python output:\n" . $output);

            if (!empty($errorOutput)) {
                Log::error("Python errors:\n" . $errorOutput);
            }

            // Save to log file
            file_put_contents($logPath, "COMMAND: " . implode(' ', $command) . "\n\n");
            file_put_contents($logPath, "EXIT CODE: $exitCode\n\nOUTPUT:\n$output\n\nERRORS:\n$errorOutput", FILE_APPEND);

            // Handle Python errors
            if ($exitCode !== 0) {
                throw new \Exception("Python script failed with exit code $exitCode. Check log: " . basename($logPath));
            }

            // Parse JSON output
            $result = json_decode($output, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("JSON decode error: " . json_last_error_msg());
            }

            if (isset($result['error'])) {
                Log::error("Python analysis error: " . $result['error']);
                throw new \Exception("Analysis failed: " . $result['error']);
            }

            // Save to history
            $history = MoodHistory::create([
                'text' => $text,
                'mood' => $result['mood'],
                'polarity' => $result['polarity'],
                'subjectivity' => $result['subjectivity']
            ]);
            Log::info("Mood analysis saved: ID {$history->id}");

            // Get Spotify playlist
            $playlist = $this->getSpotifyPlaylist($result['mood']);
            Log::debug("Using playlist: $playlist");

            return response()->json([
                'mood' => $result['mood'],
                'playlist' => $playlist,
                'analysis' => [
                    'polarity' => $result['polarity'],
                    'subjectivity' => $result['subjectivity']
                ],
                'history_id' => $history->id
            ]);

        } catch (\Exception $e) {
            Log::error("Analysis error: " . $e->getMessage());
            Log::error("Exception trace: " . $e->getTraceAsString());

            return response()->json([
                'error' => 'Analysis failed: ' . $e->getMessage(),
                'log_file' => basename($logPath ?? 'unknown')
            ], 500);
        }
    }

    public function history()
    {
        $history = MoodHistory::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($history);
    }

    private function getSpotifyPlaylist($mood)
    {
        $playlists = [
            'happy' => [
                'https://open.spotify.com/embed/playlist/37i9dQZF1DXdPec7aLTmlC?utm_source=generator', // Happy Hits!
                'https://open.spotify.com/embed/playlist/37i9dQZF1DX9tPFwDMOaN1?utm_source=generator', // Feel Good Friday
                'https://open.spotify.com/embed/playlist/37i9dQZF1DX1H4LbvY4OJi?utm_source=generator', // Happy Pop
                'https://open.spotify.com/embed/playlist/37i9dQZF1DWSf2RDTDayIx?utm_source=generator', // Happy Beats
                'https://open.spotify.com/embed/playlist/37i9dQZF1DX4dyzvuaRJ0n?utm_source=generator'  // mint: Happy Pop
            ],
            'sad' => [
                'https://open.spotify.com/embed/playlist/37i9dQZF1DX7qK8ma5wgG1?utm_source=generator', // Sad Songs
                'https://open.spotify.com/embed/playlist/37i9dQZF1DX3YSRoSdA634?utm_source=generator', // Life Sucks
                'https://open.spotify.com/embed/playlist/37i9dQZF1DWVV27DiNWxkR?utm_source=generator', // Sad Indie
                'https://open.spotify.com/embed/playlist/37i9dQZF1DWSqBruwoIXkA?utm_source=generator', // Sad Hour
                'https://open.spotify.com/embed/playlist/37i9dQZF1DX7gIoKXt0gmx?utm_source=generator'  // All The Feels
            ],
            'calm' => [
                'https://open.spotify.com/embed/playlist/37i9dQZF1DX4WYpdgoIcn6?utm_source=generator', // Chill Hits
                'https://open.spotify.com/embed/playlist/37i9dQZF1DWZd79rJ6a7lp?utm_source=generator', // Soul Relaxation
                'https://open.spotify.com/embed/playlist/37i9dQZF1DX4sWSpwq3LiO?utm_source=generator', // Peaceful Meditation
                'https://open.spotify.com/embed/playlist/37i9dQZF1DX1s9knjP51Oa?utm_source=generator', // Calm Vibes
                'https://open.spotify.com/embed/playlist/37i9dQZF1DX3Ogo9pFvBkY?utm_source=generator'  // Ambient Relaxation
            ],
            'neutral' => [
                'https://open.spotify.com/embed/playlist/37i9dQZF1EQncLwOalG3K7?utm_source=generator', // mellow
                'https://open.spotify.com/embed/playlist/37i9dQZF1EIgNZCaOGb0Mi?utm_source=generator', // Easy
                'https://open.spotify.com/embed/playlist/37i9dQZF1EIgLoMYV2NQ0v?utm_source=generator', // Background Jazz
                'https://open.spotify.com/embed/playlist/37i9dQZF1DX4OR8pnFkwhR?utm_source=generator', // Just Good Music
                'https://open.spotify.com/embed/playlist/37i9dQZF1DWTwnEm1IYyoj?utm_source=generator'  // Soft Pop Hits
            ],
            'energetic' => [
                'https://open.spotify.com/embed/playlist/37i9dQZF1DX0XUsuxWHRQd?utm_source=generator', // Rap Caviar
                'https://open.spotify.com/embed/playlist/37i9dQZF1DX9rVeQ0kNLOd?utm_source=generator', // Gaming Central
                'https://open.spotify.com/embed/playlist/37i9dQZF1DX1lVhptIYRda?utm_source=generator', // Hot Country
                'https://open.spotify.com/embed/playlist/37i9dQZF1DX4o1oenSJRJd?utm_source=generator'  // All Out 2000s
            ],
            'focused' => [
                'https://open.spotify.com/embed/playlist/37i9dQZF1DWWQRwui0ExPn?utm_source=generator', // lofi beats
                'https://open.spotify.com/embed/playlist/37i9dQZF1DX9uKNf5jGX6m?utm_source=generator', // Deep Focus
                'https://open.spotify.com/embed/playlist/37i9dQZF1DWT5lkChsPmpy?utm_source=generator', // Study Zone
                'https://open.spotify.com/embed/playlist/37i9dQZF1DWV7EzJMK2FUI?utm_source=generator'  // Jazz in the Background
            ],
            'romantic' => [
                'https://open.spotify.com/embed/playlist/37i9dQZF1DWY4xHQp97fN6?utm_source=generator', // Get It On
                'https://open.spotify.com/embed/playlist/37i9dQZF1DX50QitC6Oqtn?utm_source=generator', // Love Live
                'https://open.spotify.com/embed/playlist/37i9dQZF1DX2A29LI7xHn1?utm_source=generator'  // Sentimental Ballads
            ]
        ];

        // Default moods
        $moods = ['happy', 'sad', 'calm', 'neutral', 'energetic', 'focused', 'romantic'];

        // Select random playlist for the mood
        if (in_array($mood, $moods) && !empty($playlists[$mood])) {
            return $playlists[$mood][array_rand($playlists[$mood])];
        }

        // Fallback to neutral if mood not found
        return $playlists['neutral'][array_rand($playlists['neutral'])];
    }
}
