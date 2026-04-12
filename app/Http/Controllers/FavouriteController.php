<?php

namespace App\Http\Controllers;

use App\Models\favourite;
use App\Models\Film;
use Illuminate\Http\Request;

class FavouriteController extends Controller
{
    /**
     * List authenticated user's favourite films.
     */
    public function index(Request $request)
    {
        $favourites = favourite::with(['film.genres', 'film.image'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json(['favourites' => $favourites]);
    }

    /**
     * Toggle a film in/out of favourites.
     */
    public function toggle(Request $request)
    {
        $request->validate(['film_id' => 'required|exists:films,id']);

        $existing = favourite::where('user_id', auth()->id())
            ->where('film_id', $request->film_id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['message' => 'Removed from favourites', 'is_favourite' => false]);
        }

        favourite::create([
            'user_id' => auth()->id(),
            'film_id' => $request->film_id,
        ]);

        return response()->json(['message' => 'Added to favourites', 'is_favourite' => true]);
    }

    /**
     * Check if a specific film is favourited by the user.
     */
    public function check(Film $film)
    {
        $isFavourite = favourite::where('user_id', auth()->id())
            ->where('film_id', $film->id)
            ->exists();

        return response()->json(['is_favourite' => $isFavourite]);
    }

    /**
     * Remove a specific favourite.
     */
    public function destroy(favourite $favourite)
    {
        if ($favourite->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $favourite->delete();
        return response()->json(['message' => 'Removed from favourites']);
    }
}
