from PIL import Image, ImageFilter, ImageOps, ImageEnhance
import sys
import os
import numpy as np
import traceback
import argparse

def apply_sepia(img):
    """Apply sepia tone effect"""
    img = img.convert('RGB')
    width, height = img.size
    pixels = img.load()
    for py in range(height):
        for px in range(width):
            r, g, b = pixels[px, py]
            tr = min(255, int(0.393 * r + 0.769 * g + 0.189 * b))
            tg = min(255, int(0.349 * r + 0.686 * g + 0.168 * b))
            tb = min(255, int(0.272 * r + 0.534 * g + 0.131 * b))
            pixels[px, py] = (tr, tg, tb)
    return img

def apply_vignette(img, strength=2.0):
    """Apply vignette effect with adjustable strength"""
    img = img.convert('RGB')
    arr = np.array(img)
    height, width, _ = arr.shape

    # Create vignette mask
    x = np.linspace(-1, 1, width)
    y = np.linspace(-1, 1, height)
    X, Y = np.meshgrid(x, y)
    mask = 1 - np.clip(np.sqrt(X**2 + Y**2) * strength, 0, 1)
    mask = np.expand_dims(mask, axis=2)

    # Apply mask to each channel
    arr = arr.astype('float32')
    arr = arr * mask
    arr = np.clip(arr, 0, 255).astype('uint8')
    return Image.fromarray(arr)

def process_image(input_path, output_path, operation, params=None):
    try:
        img = Image.open(input_path)

        # Handle transparency and convert to RGB
        if img.mode in ['RGBA', 'LA', 'P']:
            # Convert to RGBA if not already
            if img.mode != 'RGBA':
                img = img.convert('RGBA')

            # Composite onto white background
            background = Image.new('RGBA', img.size, (255, 255, 255, 255))
            img = Image.alpha_composite(background, img)
            img = img.convert('RGB')
        elif img.mode == 'L':
            # Grayscale images are fine as-is
            pass
        else:
            # Convert all other modes to RGB
            img = img.convert('RGB')

        # Convert to operation-specific mode
        if operation in ['grayscale', 'edge', 'sketch']:
            if img.mode != 'L':
                img = img.convert('L')
        else:
            if img.mode != 'RGB':
                img = img.convert('RGB')

        # Process operation with parameters
        if operation == 'grayscale':
            # Already converted above
            pass

        elif operation == 'invert':
            img = ImageOps.invert(img)

        elif operation == 'edge':
            img = img.filter(ImageFilter.FIND_EDGES)

        elif operation == 'blur':
            radius = params.get('blur_radius', 5) if params else 5
            img = img.filter(ImageFilter.GaussianBlur(radius))

        elif operation == 'pixelate':
            small = img.resize((32, 32), resample=Image.NEAREST)
            img = small.resize(img.size, resample=Image.NEAREST)

        elif operation == 'thermal':
            arr = np.array(img)
            thermal_arr = arr[:, :, 0] * 0.8 + arr[:, :, 1] * 0.1 + arr[:, :, 2] * 0.1
            img = Image.fromarray(thermal_arr.astype('uint8')).convert('RGB')

        elif operation == 'sepia':
            img = apply_sepia(img)

        elif operation == 'vignette':
            strength = params.get('vignette_strength', 2.0) if params else 2.0
            img = apply_vignette(img, strength)

        elif operation == 'sharpen':
            enhancer = ImageEnhance.Sharpness(img)
            img = enhancer.enhance(2.0)

        elif operation == 'contrast':
            level = params.get('contrast_level', 1.5) if params else 1.5
            enhancer = ImageEnhance.Contrast(img)
            img = enhancer.enhance(level)

        elif operation == 'brightness':
            level = params.get('brightness_level', 1.3) if params else 1.3
            enhancer = ImageEnhance.Brightness(img)
            img = enhancer.enhance(level)

        elif operation == 'color_boost':
            enhancer = ImageEnhance.Color(img)
            img = enhancer.enhance(1.7)

        elif operation == 'emboss':
            img = img.filter(ImageFilter.EMBOSS)

        elif operation == 'sketch':
            img = img.filter(ImageFilter.CONTOUR)

        elif operation == 'oil_paint':
            img = img.filter(ImageFilter.SMOOTH_MORE).filter(ImageFilter.SMOOTH_MORE)
            # Use a predefined kernel for oil painting effect
            kernel = np.array([[1, 2, 1], [2, 4, 2], [1, 2, 1]])
            img = img.filter(ImageFilter.Kernel((3, 3), kernel.flatten(), scale=np.sum(kernel)))

        # Ensure output directory exists
        os.makedirs(os.path.dirname(output_path), exist_ok=True)

        # Save as JPEG with quality setting
        img.save(output_path, quality=95)
        print(f"Successfully processed image: {output_path}")

    except Exception as e:
        print(f"Error processing image: {str(e)}")
        traceback.print_exc()
        sys.exit(1)

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Process images with various effects')
    parser.add_argument('input_path', help='Path to input image')
    parser.add_argument('output_path', help='Path to save processed image')
    parser.add_argument('operation', help='Effect to apply')
    parser.add_argument('--blur_radius', type=float, default=5, help='Blur radius for Gaussian blur')
    parser.add_argument('--brightness_level', type=float, default=1.3, help='Brightness level (1.0 = original)')
    parser.add_argument('--contrast_level', type=float, default=1.5, help='Contrast level (1.0 = original)')
    parser.add_argument('--vignette_strength', type=float, default=2.0, help='Vignette strength')

    args = parser.parse_args()

    # Create params dictionary
    params = {
        'blur_radius': args.blur_radius,
        'brightness_level': args.brightness_level,
        'contrast_level': args.contrast_level,
        'vignette_strength': args.vignette_strength
    }

    # Process the image with parameters
    process_image(args.input_path, args.output_path, args.operation, params)
