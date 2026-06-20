<?php

namespace Database\Seeders;

use App\Models\Film;
use App\Models\genre;
use App\Models\Image;
use App\Models\Room;
use App\Models\session;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class RealMoviesSeeder extends Seeder
{
    protected string $apiKey;
    protected string $base = 'https://api.themoviedb.org/3';
    protected string $img = 'https://image.tmdb.org/t/p';

    public function run(): void
    {
        $this->apiKey = config('services.tmdb.key') ?: env('TMDB_API_KEY', '');

        // Clean existing film-related records to avoid duplicates
        Image::where('imageable_type', Film::class)->delete();
        Film::query()->delete();
        Room::query()->delete();

        if (empty($this->apiKey)) {
            $this->command->warn('TMDB_API_KEY not set — skipping live movie seed.');
            return;
        }

        // Cache TMDB genre id -> name map
        $genreMap = $this->fetchGenreMap();

        // Collect movies: now_playing (now showing) + upcoming (coming soon)
        $nowShowing = $this->collect('now_playing', 2, 20);   // ~20 current films
        $comingSoon = $this->collect('upcoming', 1, 10);      // ~10 upcoming

        // Drop any "coming soon" duplicates already in now-showing
        $nowIds = array_column($nowShowing, 'id');
        $comingSoon = array_values(array_filter($comingSoon, fn ($m) => !in_array($m['id'], $nowIds)));

        $localGenreCache = [];
        $nowModels = [];

        foreach ($nowShowing as $m) {
            $film = $this->createFilm($m, false, $genreMap, $localGenreCache);
            if ($film) {
                $nowModels[] = $film;
            }
        }
        foreach ($comingSoon as $m) {
            $this->createFilm($m, true, $genreMap, $localGenreCache);
        }

        $this->command->info('Seeded ' . count($nowModels) . ' now-showing + ' . count($comingSoon) . ' coming-soon films.');

        // Rooms + seats
        $room1 = Room::create(['name' => 'IMAX Auditorium 1', 'type' => 'Normal', 'capacity' => 60]);
        $room2 = Room::create(['name' => 'VIP Lounge 2', 'type' => 'VIP', 'capacity' => 40]);
        for ($i = 1; $i <= 60; $i++) {
            $room1->seats()->create(['seat_number' => 'S-' . $i]);
        }
        for ($i = 1; $i <= 40; $i++) {
            $room2->seats()->create(['seat_number' => 'S-' . $i]);
        }

        // Sessions across the next 4 days for each now-showing film
        $languages = ['english', 'french', 'english'];
        $rooms = [$room1, $room2];
        foreach ($nowModels as $index => $film) {
            for ($d = 0; $d < 4; $d++) {
                $room = $rooms[($index + $d) % 2];
                $hour = 13 + (($index + $d) % 4) * 2; // 13:00, 15:00, 17:00, 19:00
                $start = Carbon::today()->setHour($hour)->setMinute(0)->addDays($d);
                session::create([
                    'film_id' => $film->id,
                    'room_id' => $room->id,
                    'language' => $languages[($index + $d) % 3],
                    'start_time' => $start,
                    'end_time' => $start->copy()->addMinutes($film->duration ?: 120),
                    'price' => $room->type === 'VIP' ? 18.00 : 12.50,
                ]);
            }
        }
    }

    /** Fetch TMDB genre id => name */
    protected function fetchGenreMap(): array
    {
        $res = Http::timeout(15)->get("{$this->base}/genre/movie/list", [
            'api_key' => $this->apiKey, 'language' => 'en-US',
        ]);
        $map = [];
        foreach (($res->json()['genres'] ?? []) as $g) {
            $map[$g['id']] = $g['name'];
        }
        return $map;
    }

    /** Pull a list endpoint across pages, keep entries that have a poster + backdrop */
    protected function collect(string $endpoint, int $pages, int $limit): array
    {
        $out = [];
        for ($p = 1; $p <= $pages; $p++) {
            $res = Http::timeout(15)->get("{$this->base}/movie/{$endpoint}", [
                'api_key' => $this->apiKey, 'language' => 'en-US', 'page' => $p, 'region' => 'US',
            ]);
            foreach (($res->json()['results'] ?? []) as $m) {
                if (!empty($m['poster_path']) && !empty($m['backdrop_path'])) {
                    $out[$m['id']] = $m;
                }
            }
        }
        // Sort by popularity desc, take limit
        usort($out, fn ($a, $b) => ($b['popularity'] ?? 0) <=> ($a['popularity'] ?? 0));
        return array_slice($out, 0, $limit);
    }

    /** Create one Film (+ genres, poster image) from a TMDB list item, enriched with details */
    protected function createFilm(array $m, bool $comingSoon, array $genreMap, array &$cache): ?Film
    {
        $details = Http::timeout(15)->get("{$this->base}/movie/{$m['id']}", [
            'api_key' => $this->apiKey,
            'append_to_response' => 'videos,credits,release_dates',
        ])->json();

        if (empty($details) || empty($details['title'])) {
            return null;
        }

        // Certification (US), fallback PG-13
        $rate = 'PG-13';
        foreach (($details['release_dates']['results'] ?? []) as $r) {
            if (($r['iso_3166_1'] ?? '') === 'US') {
                foreach ($r['release_dates'] as $rd) {
                    $cert = strtoupper($rd['certification'] ?? '');
                    if (in_array($cert, ['G', 'PG', 'PG-13', 'R', 'NC-17'])) {
                        $rate = $cert;
                        break 2;
                    }
                }
            }
        }

        // Trailer (YouTube)
        $trailer = null;
        foreach (($details['videos']['results'] ?? []) as $v) {
            if (($v['site'] ?? '') === 'YouTube' && in_array($v['type'] ?? '', ['Trailer', 'Teaser'])) {
                $trailer = 'https://www.youtube.com/watch?v=' . $v['key'];
                break;
            }
        }

        // Cast (top 8 with photos)
        $cast = collect($details['credits']['cast'] ?? [])
            ->filter(fn ($c) => !empty($c['profile_path']))
            ->take(8)
            ->map(fn ($c) => [
                'name' => $c['name'],
                'character' => $c['character'] ?? '',
                'profile_path' => "{$this->img}/w185" . $c['profile_path'],
            ])->values()->toArray();

        $film = Film::create([
            'title' => $details['title'],
            'description' => $details['overview'] ?: 'No synopsis available.',
            'duration' => $details['runtime'] ?: 120,
            'rate' => $rate,
            'trailer' => $trailer,
            'backdrop' => "{$this->img}/w1280" . $m['backdrop_path'],
            'cast' => $cast ?: null,
            'coming_soon' => $comingSoon,
        ]);

        // Genres
        $genreIds = [];
        foreach (($m['genre_ids'] ?? []) as $gid) {
            $name = $genreMap[$gid] ?? null;
            if (!$name) {
                continue;
            }
            if (!isset($cache[$name])) {
                $cache[$name] = genre::firstOrCreate(['name' => $name]);
            }
            $genreIds[] = $cache[$name]->id;
        }
        $film->genres()->sync($genreIds);

        // Poster (store full TMDB URL — frontend uses it directly)
        $film->image()->create([
            'path' => "{$this->img}/w500" . $m['poster_path'],
        ]);

        return $film;
    }
}
