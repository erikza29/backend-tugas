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
        $ratings = Rating::with(['pemberi', 'penerima', 'loker'])->get();

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

        $rating = Rating::updateOrCreate(
            [
                'yangreting_id' => Auth::id(),
                'target_id' => $request->target_id,
                'loker_id' => $request->loker_id,
            ],
            [
                'rating' => $request->rating,
            ]
        );

        $rating->load(['pemberi', 'penerima', 'loker']);

        return response()->json([
            'success' => true,
            'message' => 'Rating berhasil disimpan',
            'data' => $rating
        ]);
    }

    public function show($id)
    {
        $rating = Rating::with(['pemberi', 'penerima', 'loker'])->find($id);

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
        $avg = Rating::where('target_id', $id)->avg('rating');
        $count = Rating::where('target_id', $id)->count();

        return response()->json([
            'success' => true,
            'message' => 'Rata-rata rating user',
            'average_rating' => round($avg ?? 0, 1),
            'total' => $count
        ]);
    }

    public function listByUser($id)
    {
        $ratings = Rating::with(['pemberi', 'loker'])
            ->where('target_id', $id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar rating user',
            'data' => $ratings
        ]);
    }
    public function check($target_id)
    {
        try {
            $userId = Auth::id();

            $alreadyRated = Rating::where('yangreting_id', $userId)
                ->where('target_id', $target_id)
                ->exists();

            return response()->json([
                'alreadyRated' => $alreadyRated
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


}
