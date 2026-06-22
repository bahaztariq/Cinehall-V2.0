<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TMDBService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.themoviedb.org/3';

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.key') ?: env('TMDB_API_KEY', '');
    }

    /**
     * Search movies on TMDB.
     */
    public function searchMovies(string $query): array
    {
        if (empty($this->apiKey)) {
            return $this->getMockSearch($query);
        }

        try {
            $response = Http::get("{$this->baseUrl}/search/movie", [
                'api_key' => $this->apiKey,
                'query' => $query,
                'language' => 'en-US',
            ]);

            if ($response->successful()) {
                return $response->json()['results'] ?? [];
            }
        } catch (\Exception $e) {
            Log::error("TMDB Search Error: " . $e->getMessage());
        }

        return $this->getMockSearch($query);
    }

    /**
     * Get detailed movie info including videos (for trailers).
     */
    public function getMovieDetails(int $tmdbId): ?array
    {
        if (empty($this->apiKey)) {
            return $this->getMockDetails($tmdbId);
        }

        try {
            $response = Http::get("{$this->baseUrl}/movie/{$tmdbId}", [
                'api_key' => $this->apiKey,
                'append_to_response' => 'videos,release_dates,credits',
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error("TMDB Details Error: " . $e->getMessage());
        }

        return $this->getMockDetails($tmdbId);
    }

    /**
     * Stubs/Mocks for testing when no TMDB key is provided.
     */
    protected function getMockSearch(string $query): array
    {
        $allMocks = [
            [
                'id' => 27205,
                'title' => 'Inception',
                'overview' => 'Cobb, a skilled thief who steals valuable secrets from deep within the subconscious during the dream state...',
                'release_date' => '2010-07-15',
                'poster_path' => '/oYuLEt3zVCKq57qu2F8dT7NIa6f.jpg',
            ],
            [
                'id' => 157336,
                'title' => 'Interstellar',
                'overview' => 'The adventures of a group of explorers who make use of a newly discovered wormhole to surpass the limitations on human space travel...',
                'release_date' => '2014-11-05',
                'poster_path' => '/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg',
            ],
            [
                'id' => 155,
                'title' => 'The Dark Knight',
                'overview' => 'Batman raises the stakes in his war on crime. With the help of Lt. Jim Gordon and District Attorney Harvey Dent, Batman sets out to dismantle the remaining criminal organizations that plague the streets...',
                'release_date' => '2008-07-16',
                'poster_path' => '/qJ2tW6WMUDux911r6m7haRef0WH.jpg',
            ],
            [
                'id' => 299534,
                'title' => 'Avengers: Endgame',
                'overview' => 'After the devastating events of Avengers: Infinity War, the universe is in ruins due to the efforts of the Mad Titan, Thanos...',
                'release_date' => '2019-04-24',
                'poster_path' => '/or06FN3Dka5tukK1e9sl16pB3iy.jpg',
            ],
            [
                'id' => 120,
                'title' => 'The Lord of the Rings: The Fellowship of the Ring',
                'overview' => 'Young hobbit Frodo Baggins, after inheriting a mysterious ring from his uncle Bilbo, must leave his home in order to keep it from falling into the hands of its evil creator...',
                'release_date' => '2001-12-18',
                'poster_path' => '/6oom5QYQ2yQTMJIbnvbkBL9cHo6.jpg',
            ],
        ];

        return array_values(array_filter($allMocks, function($item) use ($query) {
            return stripos($item['title'], $query) !== false;
        }));
    }

    protected function getMockDetails(int $tmdbId): ?array
    {
        $mocks = [
            27205 => [
                'id' => 27205,
                'title' => 'Inception',
                'overview' => 'Cobb, a skilled thief who steals valuable secrets from deep within the subconscious during the dream state...',
                'runtime' => 148,
                'genres' => [['id' => 28, 'name' => 'Action'], ['id' => 878, 'name' => 'Science Fiction'], ['id' => 12, 'name' => 'Adventure']],
                'release_date' => '2010-07-15',
                'poster_path' => '/oYuLEt3zVCKq57qu2F8dT7NIa6f.jpg',
                'videos' => [
                    'results' => [
                        ['key' => 'YoHD9XEInc0', 'site' => 'YouTube', 'type' => 'Trailer']
                    ]
                ],
                'release_dates' => [
                    'results' => [
                        ['iso_3166_1' => 'US', 'release_dates' => [['certification' => 'PG-13']]]
                    ]
                ]
            ],
            157336 => [
                'id' => 157336,
                'title' => 'Interstellar',
                'overview' => 'The adventures of a group of explorers who make use of a newly discovered wormhole to surpass the limitations on human space travel...',
                'runtime' => 169,
                'genres' => [['id' => 12, 'name' => 'Adventure'], ['id' => 18, 'name' => 'Drama'], ['id' => 878, 'name' => 'Science Fiction']],
                'release_date' => '2014-11-05',
                'poster_path' => '/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg',
                'videos' => [
                    'results' => [
                        ['key' => 'zSWdZAIBOEE', 'site' => 'YouTube', 'type' => 'Trailer']
                    ]
                ],
                'release_dates' => [
                    'results' => [
                        ['iso_3166_1' => 'US', 'release_dates' => [['certification' => 'PG-13']]]
                    ]
                ]
            ],
            155 => [
                'id' => 155,
                'title' => 'The Dark Knight',
                'overview' => 'Batman raises the stakes in his war on crime. With the help of Lt. Jim Gordon and District Attorney Harvey Dent, Batman sets out to dismantle the remaining criminal organizations that plague the streets...',
                'runtime' => 152,
                'genres' => [['id' => 28, 'name' => 'Action'], ['id' => 18, 'name' => 'Drama'], ['id' => 53, 'name' => 'Thriller']],
                'release_date' => '2008-07-16',
                'poster_path' => '/qJ2tW6WMUDux911r6m7haRef0WH.jpg',
                'videos' => [
                    'results' => [
                        ['key' => 'EXeTwQWrcwY', 'site' => 'YouTube', 'type' => 'Trailer']
                    ]
                ],
                'release_dates' => [
                    'results' => [
                        ['iso_3166_1' => 'US', 'release_dates' => [['certification' => 'PG-13']]]
                    ]
                ]
            ],
            299534 => [
                'id' => 299534,
                'title' => 'Avengers: Endgame',
                'overview' => 'After the devastating events of Avengers: Infinity War, the universe is in ruins due to the efforts of the Mad Titan, Thanos...',
                'runtime' => 181,
                'genres' => [['id' => 28, 'name' => 'Action'], ['id' => 12, 'name' => 'Adventure'], ['id' => 878, 'name' => 'Science Fiction']],
                'release_date' => '2019-04-24',
                'poster_path' => '/or06FN3Dka5tukK1e9sl16pB3iy.jpg',
                'videos' => [
                    'results' => [
                        ['key' => 'TcMBFSGVi1c', 'site' => 'YouTube', 'type' => 'Trailer']
                    ]
                ],
                'release_dates' => [
                    'results' => [
                        ['iso_3166_1' => 'US', 'release_dates' => [['certification' => 'PG-13']]]
                    ]
                ]
            ],
            120 => [
                'id' => 120,
                'title' => 'The Lord of the Rings: The Fellowship of the Ring',
                'overview' => 'Young hobbit Frodo Baggins, after inheriting a mysterious ring from his uncle Bilbo, must leave his home in order to keep it from falling into the hands of its evil creator...',
                'runtime' => 178,
                'genres' => [['id' => 12, 'name' => 'Adventure'], ['id' => 14, 'name' => 'Fantasy'], ['id' => 28, 'name' => 'Action']],
                'release_date' => '2001-12-18',
                'poster_path' => '/6oom5QYQ2yQTMJIbnvbkBL9cHo6.jpg',
                'videos' => [
                    'results' => [
                        ['key' => 'V75dMMIW2B4', 'site' => 'YouTube', 'type' => 'Trailer']
                    ]
                ],
                'release_dates' => [
                    'results' => [
                        ['iso_3166_1' => 'US', 'release_dates' => [['certification' => 'PG']]]
                    ]
                ]
            ]
        ];

        return $mocks[$tmdbId] ?? null;
    }
}
