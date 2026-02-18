/**
 * Dynamic Tab Title Based on Visibility
 * Changes tab title when user switches to another tab
 * Cycles through multiple messages when tab is inactive
 */

(function() {
    'use strict';
    
    // Wait for DOM to be ready
    function initTabTitle() {
        // Store the original title
        let originalTitle = 'Craft Royale';
        
        // Try to get the current title
        if (document.title && document.title.trim() !== '') {
            originalTitle = document.title;
        }
        
        // Array of titles to cycle through when tab is hidden
        const hiddenTitles = [
            'Don\'t forget this...🔥',
            'Come Back💖!',
            'Best Quality Product!🌟'
        ];
        
        let currentTitleIndex = 0;
        let titleInterval = null;
        
        // Function to cycle through hidden titles
        function cycleHiddenTitle() {
            if (document.hidden) {
                document.title = hiddenTitles[currentTitleIndex];
                currentTitleIndex = (currentTitleIndex + 1) % hiddenTitles.length;
            }
        }
        
        // Function to start cycling titles
        function startTitleCycle() {
            if (titleInterval) {
                clearInterval(titleInterval);
            }
            // Change title every 3 seconds
            titleInterval = setInterval(cycleHiddenTitle, 2000);
            // Set initial title immediately
            cycleHiddenTitle();
        }
        
        // Function to stop cycling and restore original title
        function stopTitleCycle() {
            if (titleInterval) {
                clearInterval(titleInterval);
                titleInterval = null;
            }
            document.title = originalTitle;
            currentTitleIndex = 0; // Reset to first title
        }
        
        // Function to handle visibility change
        function handleVisibilityChange() {
            if (document.hidden) {
                // Tab is hidden - start cycling through titles
                startTitleCycle();
            } else {
                // Tab is visible - stop cycling and show original title
                stopTitleCycle();
            }
        }
        
        // Listen for visibility changes (modern browsers)
        if (typeof document.hidden !== 'undefined') {
            document.addEventListener('visibilitychange', handleVisibilityChange);
        }
        
        // Fallback for older browsers
        window.addEventListener('blur', function() {
            startTitleCycle();
        });
        
        window.addEventListener('focus', function() {
            stopTitleCycle();
        });
        
        // Also handle pagehide/pageshow events
        window.addEventListener('pagehide', function() {
            startTitleCycle();
        });
        
        window.addEventListener('pageshow', function() {
            stopTitleCycle();
        });
        
        // Set initial title based on current visibility
        if (typeof document.hidden !== 'undefined') {
            if (document.hidden) {
                startTitleCycle();
            } else {
                document.title = originalTitle;
            }
        } else {
            document.title = originalTitle;
        }
    }
    
    // Initialize immediately - don't wait for DOM
    initTabTitle();
})();

