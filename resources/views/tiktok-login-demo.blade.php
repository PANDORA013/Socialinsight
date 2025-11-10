<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TikTok Login Demo - SocialInsight</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-purple-50 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-2xl w-full">
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 shadow-lg mb-4">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">
                🔥 TikTok Login Demo
            </h1>
            <p class="text-lg text-gray-600">
                OAuth 2.0 Integration Test for Review
            </p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-3xl shadow-2xl p-8 mb-6">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Connect Your TikTok Account</h2>
                <p class="text-gray-600 text-sm">
                    This will redirect you to TikTok's official login page. We never see your password.
                </p>
            </div>

            <!-- Explanation Box -->
            <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-6 mb-6">
                <h3 class="font-semibold text-blue-900 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    How OAuth 2.0 Works:
                </h3>
                <ol class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2">
                        <span class="font-semibold text-blue-600 min-w-[20px]">1.</span>
                        <span>Click "Login with TikTok" below</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-semibold text-blue-600 min-w-[20px]">2.</span>
                        <span>You'll be redirected to <strong>TikTok's official page</strong></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-semibold text-blue-600 min-w-[20px]">3.</span>
                        <span>Login with your TikTok credentials (we never see them)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-semibold text-blue-600 min-w-[20px]">4.</span>
                        <span>Authorize SocialInsight to access your basic info</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-semibold text-blue-600 min-w-[20px]">5.</span>
                        <span>TikTok redirects you back here with an authorization code</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-semibold text-blue-600 min-w-[20px]">6.</span>
                        <span>We exchange the code for an access token (securely, server-to-server)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-semibold text-blue-600 min-w-[20px]">7.</span>
                        <span><strong>Done!</strong> You're now connected and can access real-time trending data</span>
                    </li>
                </ol>
            </div>

            <!-- Permissions Info -->
            <div class="bg-purple-50 border-2 border-purple-200 rounded-xl p-6 mb-6">
                <h3 class="font-semibold text-purple-900 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Requested Permissions:
                </h3>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <code class="bg-purple-100 px-2 py-0.5 rounded text-purple-800">user.info.basic</code>
                        <span class="text-gray-600">- Your profile info (username, avatar)</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <code class="bg-purple-100 px-2 py-0.5 rounded text-purple-800">video.list</code>
                        <span class="text-gray-600">- Access to trending videos data</span>
                    </li>
                </ul>
            </div>

            <!-- Login Button -->
            <a href="{{ route('tiktok.login') }}" 
               class="block w-full bg-black text-white text-center py-4 rounded-full font-bold text-lg hover:bg-gray-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center justify-center gap-3">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/>
                </svg>
                Login with TikTok
            </a>

            <p class="text-xs text-center text-gray-500 mt-4">
                By clicking "Login with TikTok", you'll be redirected to TikTok's secure OAuth page.
            </p>
        </div>

        <!-- Security Note -->
        <div class="bg-green-50 border-2 border-green-200 rounded-xl p-4 text-center">
            <div class="flex items-center justify-center gap-2 text-green-800 font-semibold mb-1">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                </svg>
                100% Secure & Private
            </div>
            <p class="text-xs text-green-700">
                We never see or store your TikTok password. All authentication is handled by TikTok's secure servers.
            </p>
        </div>

        <!-- Back to Home -->
        <div class="text-center mt-6">
            <a href="/" class="text-gray-600 hover:text-gray-900 text-sm underline">
                ← Back to SocialInsight Homepage
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg animate-bounce">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif
</body>
</html>
