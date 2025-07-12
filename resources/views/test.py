# ... existing imports ...

def process_image(input_path, output_path, operation, params=None):
    try:
        img = Image.open(input_path)

        # Convert all images to RGB/RGBA and remove alpha by compositing on white
        if img.mode in ['RGBA', 'LA', 'P']:
            if img.mode != 'RGBA':
                img = img.convert('RGBA')
            background = Image.new('RGBA', img.size, (255, 255, 255, 255))
            img = Image.alpha_composite(background, img)
            img = img.convert('RGB')
        elif img.mode not in ['RGB', 'L']:
            img = img.convert('RGB')

        # Convert to operation-specific mode
        if operation in ['grayscale', 'edge', 'sketch']:
            if img.mode != 'L':
                img = img.convert('L')
        else:
            if img.mode != 'RGB':
                img = img.convert('RGB')

        # ... existing operation processing code ...

        # Ensure output directory exists
        os.makedirs(os.path.dirname(output_path), exist_ok=True)

        # Save as JPEG (remove quality parameter for PNG if needed)
        img.save(output_path, quality=95)

    except Exception as e:
        # ... error handling ...
