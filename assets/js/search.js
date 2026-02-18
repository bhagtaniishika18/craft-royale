// Simple and direct search functionality
console.log('🔍 Search.js file loaded');

// Wait for page to fully load
window.addEventListener('load', function() {
    console.log('📄 Page loaded, initializing search...');
    
    const searchInput = document.getElementById("searchInput");
    const searchResults = document.getElementById("searchResults");
    
    if (!searchInput) {
        console.error('❌ searchInput element not found!');
        return;
    }
    
    if (!searchResults) {
        console.error('❌ searchResults element not found!');
        return;
    }
    
    console.log('✅ Both elements found, setting up search...');
    
    let searchTimeout;
    
    // Listen to input events
    searchInput.addEventListener('input', function(e) {
        const query = this.value.trim();
        console.log('⌨️ Input detected:', query, 'Length:', query.length);
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Hide if less than 2 characters
        if (query.length < 2) {
            searchResults.style.display = 'none';
            searchResults.innerHTML = '';
            return;
        }
        
        // Show loading
        searchResults.innerHTML = '<div style="padding: 15px; text-align: center; color: #999;"><i class="fa fa-spinner fa-spin"></i> Searching...</div>';
        searchResults.style.display = 'block';
        searchResults.style.visibility = 'visible';
        searchResults.style.opacity = '1';
        
        console.log('⏳ Showing loading, will fetch in 200ms...');
        
        // Fetch after delay
        searchTimeout = setTimeout(function() {
            console.log('🚀 Fetching results for:', query);
            
            fetch('ajax-search.php?q=' + encodeURIComponent(query))
                .then(function(response) {
                    console.log('📡 Response received, status:', response.status);
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    return response.text();
                })
                .then(function(html) {
                    console.log('✅ HTML received, length:', html.length);
                    console.log('📄 First 200 chars:', html.substring(0, 200));
                    
                    searchResults.innerHTML = html;
                    
                    if (html.trim().length > 0) {
                        searchResults.style.display = 'block';
                        searchResults.style.visibility = 'visible';
                        searchResults.style.opacity = '1';
                        console.log('✅ Dropdown should be visible now');
                    } else {
                        searchResults.style.display = 'none';
                        console.log('❌ Empty response, hiding dropdown');
                    }
                })
                .catch(function(error) {
                    console.error('❌ Fetch error:', error);
                    searchResults.innerHTML = '<div style="padding: 15px; color: #dc3545;">Error: ' + error.message + '</div>';
                    searchResults.style.display = 'block';
                });
        }, 200);
    });
    
    // Handle clicks outside to close
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
    
    // Handle form submit
    const form = searchInput.closest('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (searchResults) {
                searchResults.style.display = 'none';
            }
        });
    }
    
    console.log('✅ Search setup complete!');
});
