<?php
// Set headers first
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Start output buffering to catch any errors
ob_start();

include 'includes/db.php';

if (isset($_GET['q'])) {

    $q = trim(mysqli_real_escape_string($conn, $_GET['q']));
    
    // Threshold method: require at least 2 characters
    if (empty($q) || strlen($q) < 2) {
        ob_end_clean();
        echo '<div class="search-item" style="padding: 15px; text-align: center; color: #999;">Type at least 2 characters to see suggestions</div>';
        exit;
    }

    // Check which columns exist in products table
    $columns_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
    $products_columns = [];
    if ($columns_check) {
        while ($col = mysqli_fetch_assoc($columns_check)) {
            $products_columns[] = $col['Field'];
        }
    } else {
        // Fallback: try common column names
        $products_columns = ['product_name', 'name', 'sku', 'description', 'status'];
    }

    // Build search query - search by product_name and SKU (starts with only)
    $search_conditions = [];
    
    // Check if product_name column exists (could be 'name' or 'product_name')
    // Search for products that START with the query (threshold method - only starts with)
    if (in_array('product_name', $products_columns)) {
        $search_conditions[] = "product_name LIKE '$q%'";
    } elseif (in_array('name', $products_columns)) {
        $search_conditions[] = "name LIKE '$q%'";
    }
    
    // Add SKU search if column exists (starts with)
    if (in_array('sku', $products_columns)) {
        $search_conditions[] = "sku LIKE '$q%'";
    }

    if (empty($search_conditions)) {
        // Fallback if no searchable columns found
        $search_conditions[] = "id LIKE '%$q%'";
    }

    $where_clause = "WHERE (" . implode(" OR ", $search_conditions) . ")";
    
    // Add status filter if column exists - show active and out_of_stock, exclude inactive and NULL
    if (in_array('status', $products_columns)) {
        $where_clause .= " AND ((status = 'active' OR status = 'out_of_stock') AND status IS NOT NULL)";
    }

    // Order by: products starting with query (alphabetically)
    $order_by = "";
    if (in_array('product_name', $products_columns)) {
        $order_by = "ORDER BY product_name ASC";
    } elseif (in_array('name', $products_columns)) {
        $order_by = "ORDER BY name ASC";
    }

    $sql = "SELECT * FROM products $where_clause $order_by LIMIT 10";

    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        echo '<div class="search-item" style="padding: 15px; color: #dc3545;">Database error occurred</div>';
        exit;
    }

    if (mysqli_num_rows($result) > 0) {
        $count = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            if ($count >= 8) break;
            
            $product_name = isset($row['product_name']) ? $row['product_name'] : (isset($row['name']) ? $row['name'] : 'Product');
            $product_id = isset($row['id']) ? $row['id'] : 0;
            $price = isset($row['price']) ? $row['price'] : '0';
            $sku = isset($row['sku']) ? $row['sku'] : '';
            
            // Get product image
            $image_path = 'assets/images/default.png';
            if (isset($row['image']) && !empty($row['image'])) {
                if (file_exists('uploads/products/' . $row['image'])) {
                    $image_path = 'uploads/products/' . $row['image'];
                }
            }
            
            // Highlight search term in product name
            $highlighted_name = str_ireplace($q, '<strong>'.$q.'</strong>', htmlspecialchars($product_name));
            
            // Format price
            $formatted_price = '₹' . number_format($price, 2);
            
            echo '
            <a href="product.php?id='.$product_id.'" class="search-item">
                <div class="search-item-image">
                    <img src="'.$image_path.'" alt="'.htmlspecialchars($product_name).'" onerror="this.src=\'assets/images/default.png\'">
                </div>
                <div class="search-item-content">
                    <div class="search-item-name">'.$highlighted_name.'</div>';
            
            if ($sku) {
                echo '<div class="search-item-sku">'.htmlspecialchars($sku).'</div>';
            }
            
            echo '<div class="search-item-price">'.$formatted_price.'</div>
                </div>
            </a>';
            $count++;
        }

        echo '
        <a href="search.php?query='.urlencode($q).'" class="search-more">
            Search for "'.htmlspecialchars($q).'" →
        </a>';

    } else {
        echo '<div style="padding: 20px; text-align: center; color: #666;">
                <i class="fa fa-search" style="font-size: 32px; margin-bottom: 10px; display: block; color: #ccc;"></i>
                <div style="font-size: 14px;">No products found matching "'.htmlspecialchars($q).'"</div>
              </div>';
    }
} else {
    // No query parameter
    echo '<div class="search-item" style="padding: 15px; text-align: center; color: #999;">Please enter a search term</div>';
}
