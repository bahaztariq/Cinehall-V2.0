<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\genre;
use App\Services\TMDBService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class TMDBController extends Controller
{
    protected TMDBService $tmdbService;

    public function __construct(TMDBService $tmdbService)
    {
        $this->tmdbService = $tmdbService;
    }

    /**
     * Search movies on TMDB.
     */
    public function search(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $results = $this->tmdbService->searchMovies($request->query('query'));

        return response()->json($results);
    }

    /**
     * Import a movie from TMDB.
     */
    public function import(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'tmdb_id' => 'required|integer'
        ]);

        $tmdbId = $request->input('tmdb_id');

        // Check if film is already imported (we don't want duplicates)
        // We can check by title (simplest)
        $details = $this->tmdbService->getMovieDetails($tmdbId);

        if (!$details) {
            return response()->json(['message' => 'Movie not found on TMDB.'], 404);
        }

        $existing = Film::where('title', $details['title'])->first();
        if ($existing) {
            return response()->json([
                'message' => 'This film has already been imported.',
                'film' => $existing->load(['genres', 'image'])
            ], 422);
        }

        // Map certification/rating
        $rating = 'G';
        if (isset($details['release_dates']['results'])) {
            foreach ($details['release_dates']['results'] as $res) {
                if ($res['iso_3166_1'] === 'US') {
                    foreach ($res['release_dates'] as $rd) {
                        if (!empty($rd['certification'])) {
                            $cert = strtoupper($rd['certification']);
                            if (in_array($cert, ['G', 'PG', 'PG-13', 'R', 'NC-17'])) {
                                $rating = $cert;
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        // Format YouTube Trailer embed URL
        $trailerUrl = null;
        if (isset($details['videos']['results'])) {
            foreach ($details['videos']['results'] as $video) {
                if ($video['site'] === 'YouTube' && ($video['type'] === 'Trailer' || $video['type'] === 'Teaser')) {
                    $trailerUrl = "https://www.youtube.com/watch?v=" . $video['key'];
                    break;
                }
            }
        }

        // Create the Film record
        $film = Film::create([
            'title' => $details['title'],
            'description' => $details['overview'] ?? '',
            'duration' => $details['runtime'] ?? 120,
            'rate' => $rating,
            'trailer' => $trailerUrl,
            'backdrop' => !empty($details['backdrop_path']) ? "https://image.tmdb.org/t/p/w1280" . $details['backdrop_path'] : null,
            'cast' => isset($details['credits']['cast']) ? collect($details['credits']['cast'])->take(8)->map(function ($c) {
                return [
                    'name' => $c['name'],
                    'character' => $c['character'],
                    'profile_path' => !empty($c['profile_path']) ? "https://image.tmdb.org/t/p/w185" . $c['profile_path'] : null
                ];
            })->toArray() : null
        ]);

        // Map Genres
        if (isset($details['genres'])) {
            $genreIds = [];
            foreach ($details['genres'] as $tmdbGenre) {
                $localGenre = genre::firstOrCreate(
                    ['name' => $tmdbGenre['name']]
                );
                $genreIds[] = $localGenre->id;
            }
            $film->genres()->sync($genreIds);
        }

        // Download and Save Poster Image
        if (!empty($details['poster_path'])) {
            try {
                $posterUrl = "https://image.tmdb.org/t/p/w500" . $details['poster_path'];
                $imageContents = Http::get($posterUrl)->body();
                
                if ($imageContents) {
                    $filename = 'tmdb_' . $tmdbId . '_' . Str::random(8) . '.jpg';
                    Storage::disk('public')->put('films/' . $filename, $imageContents);
                    
                    $film->image()->create([
                        'path' => 'films/' . $filename
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to download poster for TMDB movie {$tmdbId}: " . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Film imported successfully!',
            'film' => $film->load(['genres', 'image'])
        ], 201);
    }
}
