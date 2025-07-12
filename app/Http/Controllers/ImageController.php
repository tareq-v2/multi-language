<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImageController extends Controller
{
    public function showEditor()
    {
        $operations = [
            'grayscale' => 'Grayscale',
            'invert' => 'Invert Colors',
            'edge' => 'Edge Detection',
            'blur' => 'Gaussian Blur',
            'pixelate' => 'Pixelate',
            'thermal' => 'Thermal Effect',
            'sepia' => 'Sepia Tone',
            'vignette' => 'Vignette',
            'sharpen' => 'Sharpen',
            'contrast' => 'Increase Contrast',
            'brightness' => 'Brightness Boost',
            'color_boost' => 'Color Boost',
            'emboss' => 'Emboss',
            'sketch' => 'Pencil Sketch',
            'oil_paint' => 'Oil Painting',
        ];

        return view('editor', compact('operations'));
    }

    public function processImage(Request $request)
    {
        // Validate request
        $request->validate([
            'image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:10240',
                'dimensions:max_width=6000,max_height=6000'
            ],
            'operation' => 'required|in:grayscale,invert,edge,blur,pixelate,thermal,sepia,vignette,sharpen,contrast,brightness,color_boost,emboss,sketch,oil_paint'
        ]);

        try {
            // Create directories
            Storage::disk('public')->makeDirectory('uploads');
            Storage::disk('public')->makeDirectory('processed');

            // Store uploaded file
            $uploadPath = $request->file('image')->store('uploads', 'public');
            $fullUploadPath = Storage::disk('public')->path($uploadPath);

            // Prepare output
            $outputFilename = 'processed_' . time() . '.jpg';
            $outputPath = Storage::disk('public')->path("processed/{$outputFilename}");

            // Python paths
            $pythonPath = PHP_OS_FAMILY === 'Windows'
                ? base_path('venv\Scripts\python.exe')
                : base_path('venv/bin/python');

            $pythonScript = base_path('python_scripts/image_processor.py');

            // Build command with parameters
            $command = [
                $pythonPath,
                $pythonScript,
                $fullUploadPath,
                $outputPath,
                $request->operation
            ];
            $optionalParams = ['blur_radius', 'brightness_level', 'contrast_level', 'vignette_strength'];
            foreach ($optionalParams as $param) {
                if ($request->has($param)) {
                    array_push($command, "--$param", $request->$param);
                }
            }

            // Add optional parameters
            if ($request->has('blur_radius')) {
                array_push($command, '--blur_radius', $request->blur_radius);
            }

            if ($request->has('brightness_level')) {
                array_push($command, '--brightness_level', $request->brightness_level);
            }

            if ($request->has('contrast_level')) {
                array_push($command, '--contrast_level', $request->contrast_level);
            }

            if ($request->has('vignette_strength')) {
                array_push($command, '--vignette_strength', $request->vignette_strength);
            }

            // Execute Python script
            $process = new Process($command);
            $process->setTimeout(180); // 3 minutes
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \Exception($process->getErrorOutput());
            }

            // Return JSON response
            return response()->json([
                'success' => true,
                'processed_url' => Storage::url("processed/{$outputFilename}")
            ]);

        } catch (\Exception $e) {
            Log::error("Image processing failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Processing failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
