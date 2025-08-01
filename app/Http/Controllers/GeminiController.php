<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeminiController extends Controller
{
    public function generateContent(Request $request)
    {
        $apiKey = env('GEMINI_API_KEY'); // Store your key in .env file
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=AIzaSyCSEwoEi7P-AvizI5vrapu6kY_sjKai4YI";

        $response = Http::post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $request->input('query')],
                    ],
                ],
            ],
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        } else {
            return response()->json(['error' => 'API request failed'], 500);
        }
    }
}
