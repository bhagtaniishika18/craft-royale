/**
 * Prevent Back Button Navigation After Logout
 * This script prevents users from going back to authenticated pages after logout
 */

(function() {
    'use strict';
    
    // Function to check if user is logged in
    function checkLoginStatus() {
        return fetch('check-login.php')
            .then(res => res.json())
            .then(data => {
                return data.status === 'logged_in';
            })
            .catch(() => false);
    }
    
    // Function to redirect to home
    function redirectToHome() {
        window.location.replace('index.php');
    }
    
    // Check if this is a logout redirect
    const urlParams = new URLSearchParams(window.location.search);
    const isLogout = urlParams.has('logout') || urlParams.has('logged_out');
    
    if (isLogout) {
        // Clear browser history
        history.pushState(null, null, window.location.href);
        
        // Prevent back button
        window.onpopstate = function(event) {
            history.pushState(null, null, window.location.href);
            redirectToHome();
        };
        
        // Remove logout parameter from URL
        if (window.history.replaceState) {
            const cleanUrl = window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
        }
    }
    
    // For authenticated pages, verify session on back button
    const isAuthenticatedPage = window.location.pathname.indexOf('account.php') !== -1 || 
                                window.location.pathname.indexOf('admin/') !== -1;
    
    if (isAuthenticatedPage) {
        // Add to history to prevent back
        history.pushState(null, null, window.location.href);
        
        window.addEventListener('popstate', function(event) {
            // Check if user is still logged in
            checkLoginStatus().then(function(isLoggedIn) {
                if (!isLoggedIn) {
                    redirectToHome();
                } else {
                    // User is logged in, allow navigation but push state again
                    history.pushState(null, null, window.location.href);
                }
            });
        });
    }
    
    // Clear cache on page load for authenticated pages
    if (isAuthenticatedPage) {
        // Force reload from server, not cache
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                // Page was loaded from cache, check login status
                checkLoginStatus().then(function(isLoggedIn) {
                    if (!isLoggedIn) {
                        redirectToHome();
                    }
                });
            }
        });
    }
})();


