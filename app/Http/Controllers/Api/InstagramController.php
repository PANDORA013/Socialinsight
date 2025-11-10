<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InstagramController extends Controller
{
    /**
     * Placeholder for Instagram API functionality
     */
    public function analyze(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Instagram integration coming soon',
        ], 501);
    }
}
