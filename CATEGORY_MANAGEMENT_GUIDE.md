# Category & Subcategory Management Guide

## Overview
A complete admin module for managing categories and subcategories has been created. You can now add, edit, and delete categories and subcategories directly from the admin panel.

## Features

### 1. Manage Categories Page (`admin/manage_categories.php`)
- View all categories and subcategories in one place
- See subcategory count for each category
- Edit or delete categories and subcategories
- Clean, organized interface

### 2. Add/Edit Category (`admin/add_category.php`)
- Add new categories
- Edit existing categories
- Upload category images
- Simple, user-friendly form

### 3. Add/Edit Subcategory (`admin/add_subcategory.php`)
- Add new subcategories under any category
- Edit existing subcategories
- Auto-generates URL-friendly slugs
- Links subcategories to parent categories

## How to Use

### Adding a Category

1. Go to: `admin/manage_categories.php`
2. Click "Add Category" button
3. Enter category name (e.g., "Embroidery", "Beads", "Embellishments")
4. (Optional) Upload a category image
5. Click "Add Category"

### Adding a Subcategory

1. Go to: `admin/manage_categories.php`
2. Click "Add Subcategory" button
3. Select the parent category from dropdown
4. Enter subcategory name (e.g., "French Wire / Dabka")
5. Click "Add Subcategory"
   - The URL slug will be auto-generated automatically

### Editing Categories/Subcategories

1. Go to: `admin/manage_categories.php`
2. Find the category or subcategory you want to edit
3. Click the "Edit" button
4. Make your changes
5. Click "Update"

### Deleting Categories/Subcategories

1. Go to: `admin/manage_categories.php`
2. Find the item you want to delete
3. Click the "Delete" button
4. Confirm the deletion
   - **Warning**: Deleting a category will also delete all its subcategories

## Quick Start: Adding Embroidery Subcategories

If you want to quickly add all embroidery subcategories:

1. First, make sure "Embroidery" category exists
   - If not, add it via "Add Category"
2. Go to: `admin/add_embroidery_subcategories.php`
3. This will automatically add all 13 embroidery subcategories:
   - French Wire / Dabka
   - Bullion Wire/Nakshi
   - Gijai / Gimp / Stiff
   - Mukaish Metal Strip
   - Zari Threads
   - Badla Flat Metallic Threads
   - Badla Dori/ Metallic Braided Cord
   - Cotton Threads
   - Crochet Cotton Threads
   - Art Silk Threads
   - Nylon Threads
   - Sewing Threads

## Navigation

The category management is accessible from:
- **Dashboard**: New "Manage Categories" menu item
- **Direct URL**: `admin/manage_categories.php`

## Database Structure

### Categories Table
- `id` - Primary key
- `category_name` - Category name
- `image` - Category image (optional)

### Subcategories Table
- `id` - Primary key
- `category_id` - Foreign key to categories
- `subcategory_name` - Subcategory name
- `subcategory_slug` - URL-friendly identifier (auto-generated)
- `created_at` - Timestamp

## Best Practices

1. **Create Categories First**: Always create categories before adding subcategories
2. **Use Descriptive Names**: Use clear, descriptive names for categories and subcategories
3. **Organize Hierarchically**: Group related subcategories under appropriate categories
4. **Test Before Deleting**: Make sure no products are using a category/subcategory before deleting it

## Troubleshooting

### Subcategories not showing in Add Product form?
1. Make sure subcategories exist in the database
2. Check that the category is selected first
3. Open browser console (F12) to see any JavaScript errors
4. Test the API: `admin/get_subcategories.php?category_id=1` (replace 1 with your category ID)

### Can't delete a category?
- Make sure no products are using that category
- Check if there are subcategories that need to be deleted first

## Files Created/Updated

1. `admin/manage_categories.php` - Main management page
2. `admin/add_category.php` - Add/Edit category form
3. `admin/add_subcategory.php` - Updated with edit functionality
4. `admin/dashboard.php` - Added menu link

All files are ready to use!










