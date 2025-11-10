<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TikTok URI Checker</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">🔧 TikTok Redirect URI Checker</h1>
            <p class="text-gray-600">Visual tool to compare your Portal URI with .env URI</p>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-500 p-6 mb-6 rounded-lg">
            <h2 class="text-xl font-bold text-blue-800 mb-3">📋 Current Configuration (.env)</h2>
            <div class="bg-white p-4 rounded border border-blue-200 font-mono text-sm" id="currentConfig">
                Loading...
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">🔍 Compare with Portal URI</h2>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    1. Copy URI from TikTok Developer Portal:
                </label>
                <input 
                    type="text" 
                    id="portalUri" 
                    placeholder="Paste your URI from https://developers.tiktok.com/"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                >
            </div>

            <button 
                onclick="compareUris()" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg">
                Compare URIs
            </button>
        </div>

        <div id="resultBox" class="hidden">
            <div id="matchResult" class="rounded-lg shadow-lg p-6 mb-6">
                <div id="resultContent"></div>
            </div>
        </div>

        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-lg">
            <h3 class="text-lg font-bold text-yellow-800 mb-3">💡 Quick Steps</h3>
            <ol class="list-decimal list-inside space-y-2 text-gray-700">
                <li>Go to TikTok Developer Portal → Your app</li>
                <li>Copy the <strong>Redirect URI</strong></li>
                <li>Paste it above and click <strong>Compare</strong></li>
                <li>Fix if mismatch detected</li>
            </ol>
        </div>

    </div>

    <script>
        // Load current URI from .env via API
        fetch('/api/tiktok/check-config')
            .then(r => r.json())
            .then(data => {
                const config = document.getElementById('currentConfig');
                if (data.uri) {
                    config.innerHTML = `<span class="text-green-600 font-bold">TIKTOK_REDIRECT_URI=</span><span class="text-gray-800">${escapeHtml(data.uri)}</span>`;
                    window.currentUri = data.uri;
                } else {
                    config.innerHTML = '<span class="text-red-600">❌ Not configured in .env</span>';
                }
            })
            .catch(() => {
                document.getElementById('currentConfig').innerHTML = '<span class="text-orange-600">⚠️ Cannot read .env (use manual comparison)</span>';
            });

        function compareUris() {
            const portalUri = document.getElementById('portalUri').value.trim();
            
            if (!portalUri) {
                alert('Please paste URI first!');
                return;
            }

            const currentUri = window.currentUri || '';
            const isMatch = currentUri === portalUri;
            
            const resultBox = document.getElementById('resultBox');
            const matchResult = document.getElementById('matchResult');
            const resultContent = document.getElementById('resultContent');
            
            resultBox.classList.remove('hidden');

            if (isMatch) {
                matchResult.className = 'bg-green-50 border-l-4 border-green-500 rounded-lg shadow-lg p-6 mb-6';
                resultContent.innerHTML = `
                    <div class="text-center">
                        <div class="text-6xl mb-4">✅</div>
                        <h3 class="text-2xl font-bold text-green-700 mb-2">Perfect Match!</h3>
                        <p class="text-green-600">Both URIs are identical. Configuration is correct!</p>
                    </div>
                `;
            } else {
                matchResult.className = 'bg-red-50 border-l-4 border-red-500 rounded-lg shadow-lg p-6 mb-6';
                resultContent.innerHTML = `
                    <div class="text-center mb-6">
                        <div class="text-6xl mb-4">❌</div>
                        <h3 class="text-2xl font-bold text-red-700 mb-2">Mismatch Detected!</h3>
                        <p class="text-red-600">URIs don't match. This causes "Something went wrong" error.</p>
                    </div>
                    
                    <div class="space-y-4 text-left">
                        <div>
                            <div class="text-sm font-medium text-gray-700 mb-1">Current (.env):</div>
                            <div class="bg-white p-3 rounded border border-red-200 font-mono text-sm break-all">${escapeHtml(currentUri)}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-700 mb-1">Portal URI:</div>
                            <div class="bg-white p-3 rounded border border-green-200 font-mono text-sm break-all">${escapeHtml(portalUri)}</div>
                        </div>
                    </div>

                    <div class="mt-6 bg-yellow-100 p-4 rounded">
                        <strong class="text-yellow-800">🔧 Fix:</strong>
                        <ol class="list-decimal list-inside mt-2 text-sm">
                            <li>Edit <code>.env</code> file</li>
                            <li>Change TIKTOK_REDIRECT_URI to: <code class="bg-yellow-200 px-1">${escapeHtml(portalUri)}</code></li>
                            <li>Run: <code class="bg-yellow-200 px-1">php artisan config:clear</code></li>
                            <li>Restart server</li>
                        </ol>
                    </div>
                `;
            }

            resultBox.scrollIntoView({ behavior: 'smooth' });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
