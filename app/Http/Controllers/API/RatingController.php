<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function index()
    {
        $ratings = rating::with(['pemberi', 'penerima', 'loker'])->get();
        return response()->json([
            'success' => true,
            'message' => 'Daftar semua rating',
            'data' => $ratings
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:users,id|different:yangreting_id',
            'loker_id' => 'required|exists:lokers,id',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $rating = rating::updateOrCreate(
            [
                'yangreting_id' => Auth::id(),
                'target_id' => $request->target_id,
                'loker_id' => $request->loker_id,
            ],
            [
                'rating' => $request->rating,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Rating berhasil disimpan',
            'data' => $rating
        ]);
    }

    public function show($id)
    {
        $rating = rating::with(['pemberi', 'penerima', 'loker'])->find($id);

        if (!$rating) {
            return response()->json([
                'success' => false,
                'message' => 'Rating tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail rating',
            'data' => $rating
        ]);
    }

    public function userRating($id)
    {
        $avg = rating::where('target_id', $id)->avg('rating');
        $count = rating::where('target_id', $id)->count();

        return response()->json([
            'success' => true,
            'message' => 'Rata-rata rating user',
            'average_rating' => round($avg ?? 0, 1),
            'total' => $count
        ]);
    }
}
