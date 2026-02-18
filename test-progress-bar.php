<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Bar Test - Craft Royale</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2b2b2b;
            margin-bottom: 20px;
        }
        .instructions {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }
        .instructions h3 {
            margin-top: 0;
            color: #1976d2;
        }
        .instructions ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .instructions li {
            margin: 8px 0;
        }
        .test-button {
            background: #2fc7b4;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin: 10px 0;
            transition: all 0.3s ease;
        }
        .test-button:hover {
            background: #2fa76b;
            transform: translateY(-2px);
        }
        .status {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-weight: 600;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #28a745;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #dc3545;
        }
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 2px solid #17a2b8;
        }
        .preview {
            margin-top: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .preview h3 {
            margin-top: 0;
        }
        /* Simulate the progress bar */
        .demo-progress-bar {
            background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%);
            border: 2px solid #ffc107;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.2);
        }
        .demo-header {
            margin-bottom: 15px;
            font-weight: 700;
            font-size: 14px;
            color: #856404;
        }
        .demo-bar-container {
            position: relative;
            width: 100%;
            height: 10px;
            background: #e0e0e0;
            border-radius: 10px;
            overflow: visible;
            margin-bottom: 20px;
        }
        .demo-bar-fill {
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, #ff6b6b 0%, #ff8c42 25%, #ffa500 50%, #ffb347 75%, #ffc107 100%);
            border-radius: 10px;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
        }
        .demo-truck {
            position: absolute;
            top: 50%;
            left: 60%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
            z-index: 10;
        }
        .demo-footer {
            font-size: 13px;
            color: #666;
            text-align: center;
            line-height: 1.6;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #d63384;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🧪 Progress Bar Test Page</h1>
        
        <div class="instructions">
            <h3>📋 How to Test:</h3>
            <ol>
                <li>Click the button below to load the cart sidebar</li>
                <li>Add a product to your cart first if it's empty</li>
                <li>Look for the yellow progress bar with truck icon</li>
                <li>Click +/- buttons to see instant updates</li>
            </ol>
        </div>

        <button class="test-button" onclick="testCartSidebar()">
            🛒 Open Cart Sidebar
        </button>

        <button class="test-button" onclick="window.location.href='products.php'">
            🏪 Go to Products Page
        </button>

        <button class="test-button" onclick="clearCacheAndReload()">
            🔄 Clear Cache & Reload
        </button>

        <div id="status"></div>

        <div class="preview">
            <h3>✅ What You Should See:</h3>
            <p>The progress bar should look like this:</p>
            
            <div class="demo-progress-bar">
                <div class="demo-header">Almost there!</div>
                <div class="demo-bar-container">
                    <div class="demo-bar-fill"></div>
                    <div class="demo-truck">🚚</div>
                </div>
                <div class="demo-footer">
                    Add <strong style="color: #ff6b35;">₹300.00</strong> more to get <strong style="color: #ff6b35;">FREE SHIPPING!</strong>
                    <br>
                    <small style="font-size: 11px; color: #999; margin-top: 5px; display: block;">This offer is valid for Indian customers only.</small>
                </div>
            </div>

            <h4>🔍 Key Features:</h4>
            <ul>
                <li>✅ Yellow gradient background</li>
                <li>✅ "Almost there!" header (bold, brown color)</li>
                <li>✅ 10px thick progress bar with vibrant gradient</li>
                <li>✅ Single truck emoji (🚚) on the bar</li>
                <li>✅ Centered footer text</li>
            </ul>

            <h4>🎯 Test Real-Time Updates:</h4>
            <ul>
                <li>Click <code>+</code> button → Progress bar moves forward INSTANTLY</li>
                <li>Click <code>-</code> button → Progress bar moves backward INSTANTLY</li>
                <li>Truck icon slides smoothly with the bar</li>
                <li>Remaining amount updates in real-time</li>
            </ul>
        </div>
    </div>

    <script>
        function testCartSidebar() {
            const status = document.getElementById('status');
            status.className = 'status info';
            status.textContent = '🔄 Opening cart sidebar...';
            
            // Try to open cart sidebar
            if (typeof window.openCartSidebar === 'function') {
                window.openCartSidebar();
                status.className = 'status success';
                status.textContent = '✅ Cart sidebar opened! Check the right side of the screen.';
            } else {
                // Redirect to products page where cart sidebar is available
                status.className = 'status info';
                status.textContent = '🔄 Redirecting to products page...';
                setTimeout(() => {
                    window.location.href = 'products.php';
                }, 1000);
            }
        }

        function clearCacheAndReload() {
            const status = document.getElementById('status');
            status.className = 'status info';
            status.textContent = '🔄 Clearing cache and reloading...';
            
            // Clear cache and reload
            if ('caches' in window) {
                caches.keys().then(names => {
                    names.forEach(name => {
                        caches.delete(name);
                    });
                });
            }
            
            // Hard reload
            setTimeout(() => {
                window.location.reload(true);
            }, 500);
        }

        // Check if cart sidebar functions are available
        window.addEventListener('load', () => {
            const status = document.getElementById('status');
            
            if (typeof window.openCartSidebar === 'function') {
                status.className = 'status success';
                status.innerHTML = '✅ Cart sidebar functions detected! Click "Open Cart Sidebar" to test.';
            } else {
                status.className = 'status info';
                status.innerHTML = '📌 Cart sidebar not loaded on this page. Click "Go to Products Page" to test.';
            }
        });
    </script>
</body>
</html>
