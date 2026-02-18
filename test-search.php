<!DOCTYPE html>
<html>
<head>
    <title>Test Search</title>
</head>
<body>
    <h1>Search Test</h1>
    <input type="text" id="testInput" placeholder="Type 'si' to test">
    <div id="testResults" style="border: 1px solid #ccc; padding: 10px; margin-top: 10px; min-height: 50px;"></div>
    
    <script>
    document.getElementById('testInput').addEventListener('input', function() {
        const q = this.value.trim();
        if (q.length < 2) {
            document.getElementById('testResults').innerHTML = '';
            return;
        }
        
        console.log('Testing search with:', q);
        fetch('ajax-search.php?q=' + encodeURIComponent(q))
            .then(r => {
                console.log('Response status:', r.status);
                return r.text();
            })
            .then(html => {
                console.log('Response HTML:', html);
                document.getElementById('testResults').innerHTML = html;
            })
            .catch(e => {
                console.error('Error:', e);
                document.getElementById('testResults').innerHTML = 'Error: ' + e.message;
            });
    });
    </script>
</body>
</html>
