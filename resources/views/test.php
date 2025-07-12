<?php

public function processImage(Request $request)
{
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

    // ... existing directory setup ...

    try {
        // ... file storage code ...

        $command = [
            $pythonPath,
            $pythonScript,
            $fullUploadPath,
            $outputPath,
            $request->operation
        ];

        // Add ALL optional parameters
        $optionalParams = ['blur_radius', 'brightness_level', 'contrast_level', 'vignette_strength'];
        foreach ($optionalParams as $param) {
            if ($request->has($param)) {
                array_push($command, "--$param", $request->$param);
            }
        }

        // ... process execution ...
    } catch (\Exception $e) {
        // ... error handling ...
    }
}
