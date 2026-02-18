# Image Upload Guide

## Where Images Are Saved

### Directory Structure
```
Craft Royale/
├── uploads/
│   ├── products/          ← Product images (main + additional)
│   ├── categories/        ← Category images
│   └── reviews/          ← Review images
```

### Image Storage Locations

1. **Product Images**: `uploads/products/`
   - Main product image
   - Additional product images (gallery)
   - Used in: `admin/add_product.php`

2. **Category Images**: `uploads/categories/`
   - Category banner/icon images
   - Used in: `admin/add_category.php`

3. **Review Images**: `uploads/reviews/`
   - Customer review images
   - Used in: Review submission

## Setting Up Directories

### Automatic Setup (Recommended)
1. Go to: `admin/setup_upload_directories.php`
2. This will automatically create all required directories
3. Check if directories are writable

### Manual Setup
Create these directories manually:
```
uploads/
uploads/products/
uploads/categories/
uploads/reviews/
```

Make sure they have write permissions (755 or 777).

## Image Naming Convention

### Product Images
- **Main Image**: `{timestamp}_{original_filename}`
  - Example: `1767360840_product-image.jpg`
- **Additional Images**: `{timestamp}_{index}_{original_filename}`
  - Example: `1767360840_0_image1.jpg`, `1767360840_1_image2.jpg`

### Category Images
- Format: `{timestamp}_{original_filename}`
- Example: `1767360840_category-banner.jpg`

## Best Practices

### 1. File Size
- Recommended: Under 2MB per image
- Maximum: 5MB (adjust in PHP settings if needed)

### 2. Image Formats
- Supported: JPG, JPEG, PNG, GIF, WebP
- Recommended: JPG for photos, PNG for graphics

### 3. Image Dimensions
- Product images: 800x800px to 1200x1200px (square recommended)
- Category images: 1200x400px (banner format)

### 4. Security
- Images are validated before upload
- Unique filenames prevent conflicts
- Only image files are accepted

## Accessing Images

### In PHP Code
```php
// Product image
<img src="uploads/products/<?= $product['image'] ?>">

// Category image
<img src="uploads/categories/<?= $category['image'] ?>">
```

### In Admin Panel
- Product images: `../uploads/products/{filename}`
- Category images: `../uploads/categories/{filename}`

### On Client Side
- Product images: `uploads/products/{filename}`
- Category images: `uploads/categories/{filename}`

## Troubleshooting

### Images Not Uploading?
1. Check directory exists: `admin/setup_upload_directories.php`
2. Check permissions: Directories should be writable (755 or 777)
3. Check PHP settings:
   - `upload_max_filesize` in php.ini
   - `post_max_size` in php.ini
   - `max_file_uploads` in php.ini

### Images Not Displaying?
1. Check file path is correct
2. Check file exists in the directory
3. Check file permissions (should be readable: 644)

### Permission Issues?
On Windows (XAMPP): Usually works by default
On Linux: Run `chmod 755 uploads` and subdirectories

## Database Storage

**Important**: Only the **filename** is stored in the database, NOT the actual image file.

- Products table: `image` column stores filename (e.g., `1767360840_product.jpg`)
- Products table: `images` column stores JSON array of additional image filenames
- Categories table: `image` column stores filename

## Example Workflow

1. **Admin uploads product image**:
   - File selected: `my-product.jpg`
   - Saved as: `1767360840_my-product.jpg` in `uploads/products/`
   - Database stores: `1767360840_my-product.jpg`

2. **Client views product**:
   - Database provides: `1767360840_my-product.jpg`
   - HTML displays: `<img src="uploads/products/1767360840_my-product.jpg">`
   - Browser loads from: `uploads/products/1767360840_my-product.jpg`

## Quick Setup Commands

If you need to create directories manually via command line:

**Windows (PowerShell)**:
```powershell
New-Item -ItemType Directory -Path "uploads\products"
New-Item -ItemType Directory -Path "uploads\categories"
New-Item -ItemType Directory -Path "uploads\reviews"
```

**Linux/Mac**:
```bash
mkdir -p uploads/products
mkdir -p uploads/categories
mkdir -p uploads/reviews
chmod 755 uploads
chmod 755 uploads/*
```

## Summary

- **Product Images**: `uploads/products/`
- **Category Images**: `uploads/categories/`
- **Review Images**: `uploads/reviews/`
- **Setup**: Run `admin/setup_upload_directories.php`
- **Database**: Stores only filenames, not actual files










