<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PixelPalette Pro | Hybrid Image Editor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="csrf-token" content="{{csrf_token()}}">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #ff7e5f;
            --dark: #1d3557;
            --light: #f8f9fa;
            --success: #4cc9f0;
            --warning: #f72585;
            --card-bg: #ffffff;
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1d2b64, #f8cdda);
            min-height: 100vh;
            /* padding: 20px; */
            position: relative;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            padding: 30px 0;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        header h1 {
            font-size: 3rem;
            margin-bottom: 10px;
            background: linear-gradient(to right, var(--accent), #feb47b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        header p {
            font-size: 1.3rem;
            max-width: 800px;
            margin: 0 auto;
            color: #e9ecef;
            font-weight: 300;
        }

        .editor-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            min-height: 80vh;
        }

        .control-panel {
            background: linear-gradient(to bottom, #3a3a6d, #25254d);
            padding: 30px;
            color: white;
            display: flex;
            flex-direction: column;
        }

        .control-panel h2 {
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 1.8rem;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            font-size: 1.1rem;
        }

        .upload-area {
            border: 2px dashed #5e72e4;
            border-radius: 15px;
            padding: 30px 20px;
            text-align: center;
            margin-bottom: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(94, 114, 228, 0.1);
        }

        .upload-area:hover {
            background: rgba(94, 114, 228, 0.2);
            transform: translateY(-3px);
        }

        .upload-area i {
            font-size: 3.5rem;
            margin-bottom: 15px;
            color: #5e72e4;
        }

        #file-name {
            margin-top: 10px;
            font-size: 1rem;
            color: #adb5bd;
        }

        .stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stat-card h3 {
            color: white;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .stat-card p {
            color: #e9ecef;
            font-size: 0.95rem;
        }

        .preview-section {
            padding: 30px;
            display: flex;
            flex-direction: column;
        }

        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .preview-title {
            font-size: 2rem;
            color: #343a40;
            font-weight: 700;
        }

        .image-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            flex-grow: 1;
        }

        .image-box {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            background: var(--card-bg);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .image-box:hover {
            transform: translateY(-5px);
        }

        .image-box h3 {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            padding: 18px;
            margin: 0;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .image-preview {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
            min-height: 350px;
            background: #f8f9fa;
            position: relative;
            overflow: hidden;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 380px;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            transition: all 0.5s ease;
        }

        .image-actions {
            padding: 18px;
            text-align: center;
            background: #edf2ff;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .action-btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
        }

        .download-btn {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        .download-btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
        }

        .effects-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 30px 0 20px;
        }

        .effects-title {
            font-size: 1.8rem;
            color: #343a40;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .process-btn {
            padding: 14px 30px;
            border-radius: 12px;
            border: none;
            font-size: 1.2rem;
            background: linear-gradient(to right, var(--accent), #feb47b);
            color: white;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(254, 180, 123, 0.4);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .process-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(254, 180, 123, 0.5);
        }

        .process-btn:active {
            transform: translateY(1px);
        }

        .process-btn:disabled {
            background: #adb5bd;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
            opacity: 0.7;
        }

        .effects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .effect-icon {
            background: var(--card-bg);
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            box-shadow: var(--card-shadow);
            font-size: 1.8rem;
            color: var(--primary);
        }

        .effect-icon:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
            background: #edf2ff;
        }

        .effect-icon.active {
            transform: scale(1.1);
            background: var(--primary);
            color: white;
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.3);
        }

        .effect-icon.active::after {
            content: '';
            position: absolute;
            top: -5px;
            right: -5px;
            width: 25px;
            height: 25px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-family: 'Font Awesome 6 Free';
            content: '\f00c';
            font-size: 12px;
            z-index: 3;
            border: 2px solid white;
        }

        .effect-tooltip {
            position: absolute;
            top: -35px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .effect-icon:hover .effect-tooltip {
            opacity: 1;
        }

        .effect-tooltip::after {
            content: '';
            position: absolute;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
            border-width: 4px;
            border-style: solid;
            border-color: transparent transparent rgba(0, 0, 0, 0.8) transparent;
        }

        .param-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .param-modal.active {
            opacity: 1;
            visibility: visible;
        }

        .param-content {
            background: white;
            border-radius: 20px;
            padding: 30px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            position: relative;
            transform: translateY(20px);
            transition: transform 0.4s ease;
        }

        .param-modal.active .param-content {
            transform: translateY(0);
        }

        .param-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .param-header h3 {
            font-size: 1.8rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            color: var(--dark);
            transition: color 0.3s ease;
        }

        .close-btn:hover {
            color: var(--warning);
        }

        .param-group {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .param-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .param-slider {
            width: 100%;
            height: 8px;
            -webkit-appearance: none;
            background: #d1d8ff;
            border-radius: 4px;
            outline: none;
        }

        .param-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--primary);
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(67, 97, 238, 0.4);
        }

        .param-value {
            text-align: center;
            font-weight: 600;
            color: var(--primary);
            margin-top: 10px;
            font-size: 1.2rem;
        }

        footer {
            text-align: center;
            padding: 30px 0;
            color: white;
            margin-top: 40px;
            font-size: 1.1rem;
        }

        /* Progress indicator */
        .progress-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            z-index: 10;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .progress-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .progress-bar {
            width: 80%;
            height: 22px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            margin-top: 20px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            transition: width 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: progressShine 1.5s infinite;
        }

        @keyframes progressShine {
            100% {
                left: 150%;
            }
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s infinite linear;
            margin-bottom: 20px;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        .error-message {
            color: #ff6b6b;
            font-weight: 600;
            margin-top: 10px;
            text-align: center;
            display: none;
            padding: 10px;
            border-radius: 8px;
            background: rgba(255, 107, 107, 0.1);
        }

        .success-animation {
            animation: successPulse 0.5s ease;
        }

        @keyframes successPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.03); }
            100% { transform: scale(1); }
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 12px;
            background: var(--primary);
            color: white;
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.3);
            transform: translateX(150%);
            transition: transform 0.4s ease;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.error {
            background: var(--warning);
        }

        @media (max-width: 1100px) {
            .editor-container {
                grid-template-columns: 1fr;
            }

            .image-container {
                grid-template-columns: 1fr;
            }

            .effects-grid {
                grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
            }
        }

        @media (max-width: 768px) {
            header h1 {
                font-size: 2.5rem;
            }

            header p {
                font-size: 1.1rem;
            }

            .preview-title, .effects-title {
                font-size: 1.6rem;
            }

            .process-btn {
                padding: 12px 20px;
                font-size: 1.1rem;
            }

            .effect-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-palette"></i> PixelPalette Pro</h1>
            <p>Hybrid AI-powered image processing with Laravel & Python</p>
        </header>

        <div class="editor-container">
            <div class="control-panel">
                <h2><i class="fas fa-sliders-h"></i> Control Panel</h2>

                <div class="form-group">
                    <label for="image-upload"><i class="fas fa-upload"></i> Upload Image</label>
                    <div class="upload-area" id="upload-area">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Drag & drop your image here</p>
                        <p>or</p>
                        <button type="button" id="browse-btn" class="process-btn">
                            <i class="fas fa-folder-open"></i> Browse Files
                        </button>
                        <div id="file-name">No file selected</div>
                        <input type="file" name="image" id="image-upload" accept="image/*" hidden>
                        <div id="file-error" class="error-message"></div>
                    </div>
                </div>

                <div class="stats">
                    <div class="stat-card">
                        <h3><i class="fas fa-microchip"></i> System Info</h3>
                        <p>PHP 8.1.2</p>
                        <p>Python 3.10</p>
                        <p>PIL/Pillow 9.4.0</p>
                    </div>

                    <div class="stat-card">
                        <h3><i class="fas fa-bolt"></i> Performance</h3>
                        <p>Real-time processing</p>
                        <p>GPU acceleration</p>
                        <p>Hybrid architecture</p>
                    </div>
                </div>
            </div>

            <div class="preview-section">
                <div class="preview-header">
                    <div class="preview-title">
                        <i class="fas fa-image"></i> Image Preview
                    </div>
                    <div>
                        <span style="background: var(--primary); color: white; padding: 8px 15px; border-radius: 30px; font-weight: 600;">
                            Hybrid Mode
                        </span>
                    </div>
                </div>

                <div class="image-container">
                    <div class="image-box">
                        <h3><i class="fas fa-file-image"></i> Original Image</h3>
                        <div class="image-preview" id="original-preview-container">
                            <div class="text-muted" id="original-placeholder">No image uploaded</div>
                            <img id="original-img" style="display:none">
                            <div class="progress-overlay" id="original-progress">
                                <div class="spinner"></div>
                                <p>Loading your image...</p>
                            </div>
                        </div>
                        <div class="image-actions">
                            <a href="#" id="download-original" download class="action-btn download-btn" style="display:none">
                                <i class="fas fa-download"></i> Download Original
                            </a>
                        </div>
                    </div>

                    <div class="image-box">
                        <h3><i class="fas fa-magic"></i> Processed Image</h3>
                        <div class="image-preview" id="processed-preview-container">
                            <div class="text-muted" id="processed-placeholder">Processed image will appear here</div>
                            <img id="processed-img" style="display:none">
                            <div class="progress-overlay" id="processing-progress">
                                <div class="spinner"></div>
                                <p>Processing image...</p>
                                <div class="progress-bar">
                                    <div class="progress-fill" id="progress-fill"></div>
                                </div>
                            </div>
                        </div>
                        <div class="image-actions">
                            <a href="#" id="download-processed" download class="action-btn download-btn" style="display:none">
                                <i class="fas fa-download"></i> Download Result
                            </a>
                        </div>
                    </div>
                </div>

                <div class="effects-header">
                    <div class="effects-title">
                        <i class="fas fa-wand-magic-sparkles"></i> Effects Library
                    </div>
                    <button id="process-btn" class="process-btn" disabled>
                        <i class="fas fa-bolt"></i> Apply Effect
                    </button>
                </div>
                <div id="effect-error" class="error-message">Please select an effect</div>

                <div class="effects-grid">
                    <div class="effect-icon" data-effect="grayscale">
                        <i class="fas fa-moon"></i>
                        <span class="effect-tooltip">Grayscale</span>
                    </div>
                    <div class="effect-icon" data-effect="invert">
                        <i class="fas fa-adjust"></i>
                        <span class="effect-tooltip">Invert Colors</span>
                    </div>
                    <div class="effect-icon" data-effect="edge">
                        <i class="fas fa-draw-polygon"></i>
                        <span class="effect-tooltip">Edge Detection</span>
                    </div>
                    <div class="effect-icon" data-effect="blur">
                        <i class="fas fa-blur"></i>
                        <span class="effect-tooltip">Gaussian Blur</span>
                    </div>
                    <div class="effect-icon" data-effect="pixelate">
                        <i class="fas fa-th-large"></i>
                        <span class="effect-tooltip">Pixelate</span>
                    </div>
                    <div class="effect-icon" data-effect="thermal">
                        <i class="fas fa-fire"></i>
                        <span class="effect-tooltip">Thermal Effect</span>
                    </div>
                    <div class="effect-icon" data-effect="sepia">
                        <i class="fas fa-umbrella-beach"></i>
                        <span class="effect-tooltip">Sepia Tone</span>
                    </div>
                    <div class="effect-icon" data-effect="vignette">
                        <i class="fas fa-circle-notch"></i>
                        <span class="effect-tooltip">Vignette</span>
                    </div>
                    <div class="effect-icon" data-effect="sharpen">
                        <i class="fas fa-cut"></i>
                        <span class="effect-tooltip">Sharpen</span>
                    </div>
                    <div class="effect-icon" data-effect="contrast">
                        <i class="fas fa-sun"></i>
                        <span class="effect-tooltip">Increase Contrast</span>
                    </div>
                    <div class="effect-icon" data-effect="brightness">
                        <i class="fas fa-lightbulb"></i>
                        <span class="effect-tooltip">Brightness Boost</span>
                    </div>
                    <div class="effect-icon" data-effect="color_boost">
                        <i class="fas fa-fill-drip"></i>
                        <span class="effect-tooltip">Color Boost</span>
                    </div>
                    <div class="effect-icon" data-effect="emboss">
                        <i class="fas fa-mountain"></i>
                        <span class="effect-tooltip">Emboss</span>
                    </div>
                    <div class="effect-icon" data-effect="sketch">
                        <i class="fas fa-pencil-alt"></i>
                        <span class="effect-tooltip">Pencil Sketch</span>
                    </div>
                    <div class="effect-icon" data-effect="oil_paint">
                        <i class="fas fa-paint-brush"></i>
                        <span class="effect-tooltip">Oil Painting</span>
                    </div>
                </div>
            </div>
        </div>

        <footer>
            <p>PixelPalette Pro &copy; 2023 | Hybrid Image Processing with Laravel & Python</p>
        </footer>
    </div>

    <!-- Floating Parameters Modal -->
    <div class="param-modal" id="param-modal">
        <div class="param-content">
            <div class="param-header">
                <h3><i class="fas fa-sliders-h"></i> Effect Parameters</h3>
                <button class="close-btn">&times;</button>
            </div>

            <div class="param-group">
                <label for="blur-param"><i class="fas fa-blur"></i> Blur Radius</label>
                <input type="range" min="1" max="20" value="5" class="param-slider" id="blur-param">
                <div class="param-value" id="blur-value">5</div>
            </div>

            <div class="param-group">
                <label for="brightness-param"><i class="fas fa-sun"></i> Brightness</label>
                <input type="range" min="0.1" max="3" step="0.1" value="1.3" class="param-slider" id="brightness-param">
                <div class="param-value" id="brightness-value">1.3</div>
            </div>

            <div class="param-group">
                <label for="contrast-param"><i class="fas fa-sliders-h"></i> Contrast</label>
                <input type="range" min="0.1" max="3" step="0.1" value="1.5" class="param-slider" id="contrast-param">
                <div class="param-value" id="contrast-value">1.5</div>
            </div>

            <div class="param-group">
                <label for="vignette-param"><i class="fas fa-circle-notch"></i> Vignette Strength</label>
                <input type="range" min="0.5" max="5" step="0.1" value="2.0" class="param-slider" id="vignette-param">
                <div class="param-value" id="vignette-value">2.0</div>
            </div>
        </div>
    </div>

    <div class="notification" id="notification">
        <i class="fas fa-check-circle"></i>
        <span id="notification-message">Effect applied successfully!</span>
    </div>

<script>
    $(document).ready(function() {
        // Get CSRF token safely
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (!csrfToken) {
            console.error('CSRF token not found');
            alert('Security token missing. Please refresh the page.');
            return;
        }

        // DOM elements
        const uploadArea = $('#upload-area');
        const fileInput = $('#image-upload');
        const fileName = $('#file-name');
        const browseBtn = $('#browse-btn');
        const processBtn = $('#process-btn');
        const effectIcons = $('.effect-icon');
        const fileError = $('#file-error');
        const effectError = $('#effect-error');
        const originalPlaceholder = $('#original-placeholder');
        const originalImg = $('#original-img');
        const processedPlaceholder = $('#processed-placeholder');
        const processedImg = $('#processed-img');
        const originalProgress = $('#original-progress');
        const processingProgress = $('#processing-progress');
        const progressFill = $('#progress-fill');
        const downloadOriginal = $('#download-original');
        const downloadProcessed = $('#download-processed');
        const notification = $('#notification');
        const notificationMessage = $('#notification-message');

        // Parameter elements
        const paramModal = $('#param-modal');
        const closeBtn = $('.close-btn');
        const blurSlider = $('#blur-param');
        const brightnessSlider = $('#brightness-param');
        const contrastSlider = $('#contrast-param');
        const vignetteSlider = $('#vignette-param');
        const blurValue = $('#blur-value');
        const brightnessValue = $('#brightness-value');
        const contrastValue = $('#contrast-value');
        const vignetteValue = $('#vignette-value');

        // State variables
        let selectedEffect = null;
        let uploadedImage = null;
        let isProcessing = false;

        // Initialize
        updateProcessButton();

        // Initialize parameter sliders
        blurSlider.on('input', function() {
            blurValue.text($(this).val());
        });

        brightnessSlider.on('input', function() {
            brightnessValue.text($(this).val());
        });

        contrastSlider.on('input', function() {
            contrastValue.text($(this).val());
        });

        vignetteSlider.on('input', function() {
            vignetteValue.text($(this).val());
        });

        // Event listeners
        browseBtn.on('click', function() {
            fileInput.click();
        });

        uploadArea.on('dragover', function(e) {
            e.preventDefault();
            uploadArea.css({
                'borderColor': '#3a0ca3',
                'backgroundColor': 'rgba(58, 12, 163, 0.1)'
            });
        });

        uploadArea.on('dragleave', function() {
            uploadArea.css({
                'borderColor': '#5e72e4',
                'backgroundColor': 'rgba(94, 114, 228, 0.1)'
            });
        });

        uploadArea.on('drop', function(e) {
            e.preventDefault();
            if (e.originalEvent.dataTransfer.files.length) {
                fileInput.prop('files', e.originalEvent.dataTransfer.files);
                handleFileUpload(fileInput.prop('files')[0]);
            }
            uploadArea.css({
                'borderColor': '#5e72e4',
                'backgroundColor': 'rgba(94, 114, 228, 0.1)'
            });
        });

        fileInput.on('change', function() {
            if (this.files.length) {
                handleFileUpload(this.files[0]);
            }
        });

        effectIcons.on('click', function() {
            if (isProcessing) return;

            effectIcons.removeClass('active');
            $(this).addClass('active');
            selectedEffect = $(this).data('effect');

            // Show parameters for effects that have them
            if (['blur', 'brightness', 'contrast', 'vignette'].includes(selectedEffect)) {
                paramModal.addClass('active');
            }

            // Visual feedback
            $(this).addClass('success-animation');
            setTimeout(() => {
                $(this).removeClass('success-animation');
            }, 500);

            if (effectError) effectError.hide();
            updateProcessButton();
        });

        // Close modal when clicking close button
        closeBtn.on('click', function() {
            paramModal.removeClass('active');
        });

        // Close modal when clicking outside the content
        paramModal.on('click', function(e) {
            if ($(e.target).is(paramModal)) {
                paramModal.removeClass('active');
            }
        });

        // Close modal with ESC key
        $(document).on('keydown', function(e) {
            if (e.key === "Escape") {
                paramModal.removeClass('active');
            }
        });

        processBtn.on('click', processImage);

        // Functions
        function handleFileUpload(file) {
            // Reset errors
            if (fileError) {
                fileError.hide();
                fileError.text('');
            }

            // Validate file
            if (!file.type.match('image.*')) {
                if (fileError) {
                    fileError.text('Please select a valid image file (JPEG, PNG, etc.)');
                    fileError.show();
                }
                return;
            }

            if (file.size > 5 * 1024 * 1024) { // 5MB
                if (fileError) {
                    fileError.text('File size exceeds 5MB limit');
                    fileError.show();
                }
                return;
            }

            fileName.text(file.name);
            uploadedImage = file;

            // Show loading indicator
            if (originalProgress) originalProgress.addClass('active');
            if (originalPlaceholder) originalPlaceholder.hide();
            if (originalImg) originalImg.hide();

            // Create preview
            const reader = new FileReader();
            reader.onload = function(e) {
                // Set image source
                if (originalImg) {
                    originalImg.attr('src', e.target.result);
                    originalImg.show();
                    originalImg.on('load', function() {
                        $(this).addClass('success-animation');
                        setTimeout(() => {
                            $(this).removeClass('success-animation');
                        }, 500);
                    });
                }

                // Show download button
                if (downloadOriginal) {
                    downloadOriginal.show();
                    downloadOriginal.attr('href', e.target.result);
                }

                // Hide loading indicator
                setTimeout(() => {
                    if (originalProgress) originalProgress.removeClass('active');
                }, 500);
            };

            reader.readAsDataURL(file);
            updateProcessButton();
        }

        function updateProcessButton() {
            if (processBtn) {
                processBtn.prop('disabled', !(uploadedImage && selectedEffect) || isProcessing);
            }
        }

        function processImage() {
            if (isProcessing) return;

            // Validate selection
            if (!uploadedImage) {
                if (fileError) {
                    fileError.text('Please select an image');
                    fileError.show();
                }
                return;
            }

            if (!selectedEffect) {
                if (effectError) effectError.show();
                return;
            }

            // Reset errors
            if (fileError) fileError.hide();
            if (effectError) effectError.hide();

            // Show processing indicator
            isProcessing = true;
            updateProcessButton();

            if (processingProgress) processingProgress.addClass('active');
            if (processedPlaceholder) processedPlaceholder.hide();
            if (processedImg) {
                processedImg.hide();
                processedImg.attr('src', '');
            }
            if (downloadProcessed) downloadProcessed.hide();

            if (progressFill) progressFill.css('width', '10%');

            // Create FormData
            const formData = new FormData();
            formData.append('image', uploadedImage);
            formData.append('operation', selectedEffect);
            formData.append('_token', csrfToken);

            // Add parameters if applicable
            if (selectedEffect === 'blur') {
                formData.append('blur_radius', blurSlider.val());
            }
            if (selectedEffect === 'brightness') {
                formData.append('brightness_level', brightnessSlider.val());
            }
            if (selectedEffect === 'contrast') {
                formData.append('contrast_level', contrastSlider.val());
            }
            if (selectedEffect === 'vignette') {
                formData.append('vignette_strength', vignetteSlider.val());
            }

            // Simulate progress
            let progress = 10;
            const progressInterval = setInterval(() => {
                progress = Math.min(progress + 2, 90);
                if (progressFill) progressFill.css('width', `${progress}%`);
            }, 200);

            // Send AJAX request
            $.ajax({
                url: "{{ route('process.image') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    clearInterval(progressInterval);
                    if (progressFill) progressFill.css('width', '100%');

                    setTimeout(() => {
                        isProcessing = false;
                        updateProcessButton();

                        if (processingProgress) processingProgress.removeClass('active');

                        if (data.success) {
                            // Update processed image
                            if (processedImg) {
                                processedImg.attr('src', data.processed_url);
                                processedImg.show();
                                processedImg.on('load', function() {
                                    $(this).addClass('success-animation');
                                    setTimeout(() => {
                                        $(this).removeClass('success-animation');
                                    }, 500);

                                    // Show success notification
                                    showNotification('Effect applied successfully!');
                                });
                            }

                            // Update download link
                            if (downloadProcessed) {
                                downloadProcessed.show();
                                downloadProcessed.attr('href', data.processed_url);
                            }
                        } else {
                            showNotification(data.error || 'Processing failed', true);
                        }
                    }, 500);
                },
                error: function(xhr, status, error) {
                    clearInterval(progressInterval);
                    isProcessing = false;
                    updateProcessButton();

                    if (processingProgress) processingProgress.removeClass('active');
                    console.error('Processing error:', error);
                    showNotification('Processing failed: ' + error, true);
                }
            });
        }

        function showNotification(message, isError = false) {
            if (!notification || !notificationMessage) return;

            notificationMessage.text(message);

            if (isError) {
                notification.addClass('error');
            } else {
                notification.removeClass('error');
            }

            notification.addClass('show');

            setTimeout(() => {
                notification.removeClass('show');
            }, 3000);
        }
    });
</script>
</body>
</html>
