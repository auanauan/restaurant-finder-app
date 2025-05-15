<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function search(Request $request)
    {
        $keyword = $request->query('keyword', 'Bang Sue');
        $cacheKey = 'search_' . md5($keyword);

        // Cache for 1 hour
        $result = Cache::remember($cacheKey, 3600, function () use ($keyword) {
            $apiKey = env('GOOGLE_MAP_API_KEY');
            $response = Http::get('https://maps.googleapis.com/maps/api/place/textsearch/json', [
                'query' => 'restaurant in ' . $keyword,
                'key' => $apiKey
            ]);

            return $response->json();
        });

        return response()->json($result);
    }
}
