# Product Management System Setup Guide

## Overview
This product management system allows you to add products from the admin panel and display them on the client side, similar to the reference website (embroiderymaterial.com).

## Database Setup

### Step 1: Run the SQL Schema
Execute the SQL file to create/update the necessary tables:

```sql
-- Run this file in your MySQL database
create_products_schema.sql
```

This will:
- Create `subcategories` table
- Create/update `products` table with all necessary fields:
  - Basic info: name, slug, SKU, description
  - Pricing: MRP, price, discount_percent
  - Images: main image and additional images (JSON array)
  - Inventory: stock, status
  - Product details: color, size, material, weight, country_of_origin
  - Relationships: category_id, subcategory_id

### Step 2: Create Upload Directory
Make sure the uploads directory exists:
```
uploads/products/
```

## Admin Panel Features

### 1. Add/Edit Products (`admin/add_product.php`)
- Full product form with all fields
- Category and subcategory selection
- Multiple image upload support
- Real-time image preview
- Product status management (Active/Inactive/Out of Stock)

### 2. Manage Products (`admin/manage_products.php`)
- View all products in a table
- Edit and delete products
- Product image preview

### 3. Add Subcategories (`admin/add_subcategory.php`)
- Create subcategories linked to categories
- Automatically generates URL-friendly slugs

## Client-Side Features

### 1. Products Listing Page (`products.php`)
- Displays products filtered by category/subcategory
- URL structure: `products.php?cat=metallic-wires&sub=french-wire-dabka`
- Features:
  - Product grid layout
  - Sorting options (newest, price, name)
  - Pagination
  - Discount badges
  - Stock status indicators
  - Responsive design

### 2. Product Detail Page (`product.php`)
- Full product information display
- Image gallery with thumbnails
- Product specifications
- Quantity selector
- Add to cart button
- Related products section
- Product tabs (Description, Ask a Question)

## URL Structure

The system uses URL slugs to map to categories and subcategories:

### Categories (examples):
- `metallic-wires` → "Metallic Wires"
- `metallic-threads` → "Metallic Threads/Cord"
- `threads-cord` → "Threads/Cord"

### Subcategories (examples):
- `french-wire-dabka` → "French Wire / Dabka"
- `bullion-wire-nakshi` → "Bullion Wire/Nakshi"
- `zari-threads` → "Zari Threads"

## Adding Products

1. **Login to Admin Panel**: `admin/login.php`
2. **Add Subcategories** (if needed): `admin/add_subcategory.php`
3. **Add Product**: `admin/add_product.php`
   - Select category and subcategory
   - Fill in all product details
   - Upload main image and additional images
   - Set pricing (MRP and selling price)
   - Set stock quantity
   - Save product

## Navigation Integration

The header navigation (`includes/header.php`) already has dropdown menus that link to:
```
products.php?cat=metallic-wires&sub=french-wire-dabka
```

These links will automatically display products from the database when clicked.

## Product Fields

### Required Fields:
- Category
- Product Name
- MRP (Original Price)
- Selling Price
- Stock Quantity
- Status
- Main Image

### Optional Fields:
- Subcategory
- SKU
- Discount Percent
- Description
- Short Description
- Additional Images
- Country of Origin
- Color
- Size
- Material
- Weight

## Features Matching Reference Website

✅ Category/Subcategory navigation
✅ Product grid display with images
✅ Discount badges
✅ Stock status (Sold Out/Coming Soon)
✅ Product detail page with image gallery
✅ Pricing display (MRP and discounted price)
✅ Product specifications
✅ Related products section
✅ Breadcrumb navigation
✅ Responsive design

## Next Steps

1. Run the database schema SQL file
2. Create subcategories for your product categories
3. Add products through the admin panel
4. Test the product listing and detail pages
5. Customize styling if needed

## Notes

- Product images are stored in `uploads/products/`
- Additional images are stored as a JSON array in the database
- The system automatically calculates discount percentage if MRP > Price
- Products with status "out_of_stock" or stock = 0 show "COMING SOON" button
- All product queries filter by `status = 'active'` to only show active products










