<template>
    <div class="pb-20 bg-surface text-on-surface">
        <div v-if="loading" class="flex justify-center items-center h-[500px]">
            <div class="animate-spin rounded-full h-8 w-8 border-2 border-primary border-t-transparent"></div>
        </div>

        <div v-else-if="film" class="space-y-12">
            <!-- Hero Section with Backdrop -->
            <section class="relative w-full h-[440px] sm:h-[600px] min-h-0 sm:min-h-[400px] bg-surface-container overflow-hidden">
                <!-- Large Backdrop Image -->
                <div 
                    class="absolute inset-0 w-full h-full bg-cover bg-center"
                    :style="{ backgroundImage: `url(${film.backdrop || getImageUrl(film.image)})` }"
                ></div>
                <!-- Premium backdrop scrim -->
                <div class="absolute inset-0 bg-linear-to-t from-surface via-surface/60 to-transparent"></div>
                <div class="absolute inset-0 bg-linear-to-r from-surface/80 via-transparent to-transparent"></div>

                <!-- Breadcrumbs -->
                <div class="absolute top-6 left-6 sm:left-12 lg:left-24 z-20">
                    <router-link to="/" class="flex items-center gap-2 text-primary font-bold text-xs uppercase tracking-widest hover:opacity-80 transition-opacity">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_back</span>
                        Back to Collection
                    </router-link>
                </div>

                <!-- Floating Info Area -->
                <div class="absolute bottom-0 left-0 right-0 py-8 sm:py-12 z-20">
                    <div class="max-w-7xl mx-auto px-6 sm:px-12 lg:px-24 flex flex-col md:flex-row items-end gap-8">
                        <!-- Movie Poster (floating white border) -->
                        <div class="hidden md:block w-56 aspect-2/3 rounded-xl overflow-hidden shadow-2xl border-4 border-surface-container-high transform translate-y-6 shrink-0 bg-surface-container">
                            <img :src="getImageUrl(film.image)" :alt="film.title" class="w-full h-full object-cover" />
                        </div>
                        
                        <!-- Movie title & details -->
                        <div class="flex-1 space-y-4">
                            <div class="flex flex-wrap gap-2">
                                <span v-for="genre in film.genres" :key="genre.id" class="px-3.5 py-1 bg-surface-container/80 backdrop-blur text-primary text-[10px] font-bold rounded-full border border-outline-variant/30 uppercase tracking-wider">
                                    {{ genre.name }}
                                </span>
                            </div>
                            <h1 class="font-serif text-3xl sm:text-5xl font-bold text-on-surface leading-tight">
                                {{ film.title }}
                            </h1>
                            <div class="flex flex-wrap items-center gap-6 text-xs text-on-surface-variant font-bold uppercase tracking-wider">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-primary text-[18px]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">star</span>
                                    <span>{{ film.rate }}/10 Rating</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-primary text-[18px]" aria-hidden="true">schedule</span>
                                    <span>{{ film.duration }} Mins</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-primary text-[18px]" aria-hidden="true">verified</span>
                                    <span>Dolby Atmos</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main Columns: Content, Cast & Showtimes Selector -->
            <main class="max-w-7xl mx-auto px-6 sm:px-12 lg:px-24 grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16">
                <!-- Left: Synopsis & Cast -->
                <div class="lg:col-span-7 space-y-10 sm:space-y-16">
                    <section class="space-y-4">
                        <h2 class="font-serif text-2xl font-bold text-primary">Synopsis</h2>
                        <p class="text-sm sm:text-base text-on-surface-variant leading-relaxed">
                            {{ film.description }}
                        </p>
                    </section>

                    <!-- Trailer Section -->
                    <section v-if="film.trailer" class="space-y-4">
                        <h2 class="font-serif text-2xl font-bold text-primary">Official Trailer</h2>
                        <div class="aspect-video w-full rounded-2xl overflow-hidden shadow-[0_8px_30px_rgba(0,0,0,0.45)] border border-outline-variant/30 bg-black">
                            <iframe 
                                :src="getEmbedUrl(film.trailer)"
                                class="w-full h-full"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                        </div>
                    </section>


                    <!-- Cast Section -->
                    <section v-if="film.cast && film.cast.length" class="space-y-6">
                        <h2 class="font-serif text-2xl font-bold text-primary">Featured Ensemble</h2>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                            <div 
                                v-for="actor in film.cast" 
                                :key="actor.name"
                                class="group text-center"
                            >
                                <div class="w-full aspect-square rounded-full overflow-hidden mb-3 ring-2 ring-outline-variant p-1 group-hover:ring-primary transition-all duration-300">
                                    <img 
                                        class="w-full h-full object-cover rounded-full" 
                                        :src="actor.profile_path || 'https://via.placeholder.com/185x185?text=' + encodeURIComponent(actor.name)" 
                                        :alt="actor.name"
                                    />
                                </div>
                                <p class="font-bold text-xs text-primary group-hover:text-primary-hover transition-colors">{{ actor.name }}</p>
                                <p class="text-[10px] text-on-surface-variant uppercase mt-0.5">{{ actor.character }}</p>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Favorite Action -->
                    <button 
                        v-if="auth.isAuthenticated" 
                        @click="toggleFavourite" 
                        class="w-full flex items-center justify-center gap-2 py-4 rounded-xl border transition-all text-xs font-bold uppercase tracking-widest cursor-pointer"
                        :class="isFavourited ? 'bg-red-500/10 border-red-500/30 text-red-400 shadow-sm' : 'bg-surface-container border-outline-variant text-outline hover:text-red-400 hover:border-red-500/30'"
                    >
                        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">favorite</span>
                        {{ isFavourited ? 'Remove From Collection' : 'Save To Collection' }}
                    </button>
                </div>

                <!-- Right: Showtime Selector Card -->
                <aside class="lg:col-span-5">
                    <div class="bg-surface-container rounded-2xl p-6 sm:p-8 lg:sticky lg:top-28 border border-outline-variant/30 shadow-[0_8px_30px_rgba(0,0,0,0.45)] space-y-6">
                        <div class="flex items-center gap-2 border-b border-outline-variant/20 pb-4">
                            <span class="material-symbols-outlined text-primary text-[24px]" aria-hidden="true">event_seat</span>
                            <h3 class="font-sans text-lg font-bold text-primary uppercase tracking-wide">Available Screenings</h3>
                        </div>

                        <!-- Date Selector -->
                        <div v-if="availableDates.length" class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
                            <button
                                v-for="d in availableDates"
                                :key="d.key"
                                type="button"
                                @click="selectedDate = d.key"
                                :aria-pressed="selectedDate === d.key"
                                :aria-label="`Show screenings for ${d.weekday} ${d.day}`"
                                class="flex flex-col items-center justify-center min-w-[65px] min-h-[64px] rounded-xl shrink-0 transition-colors"
                                :class="selectedDate === d.key
                                    ? 'gold-gradient shadow-sm'
                                    : 'bg-surface-container-low text-on-surface-variant border border-outline-variant/40 hover:border-primary'"
                            >
                                <span class="text-[9px] font-bold">{{ d.weekday }}</span>
                                <span class="font-bold text-base leading-tight">{{ d.day }}</span>
                            </button>
                        </div>

                        <!-- dynamic session list -->
                        <div class="space-y-4">
                            <template v-if="sessionsForSelectedDate.length">
                                <div
                                    v-for="session in sessionsForSelectedDate"
                                    :key="session.id"
                                    class="p-4 rounded-xl border border-outline-variant/30 hover:border-primary/50 transition-colors bg-surface-container-lowest flex flex-col gap-3 justify-between"
                                >
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="text-[10px] text-outline uppercase font-bold tracking-widest">Time &amp; Language</span>
                                            <p class="font-bold text-sm text-on-surface mt-0.5">{{ formatDateTime(session.start_time) }}</p>
                                            <p class="text-[10px] text-on-surface-variant font-bold uppercase mt-1 tracking-wider">// Language: {{ session.language }}</p>
                                        </div>
                                        <span class="text-[9px] font-bold bg-primary/10 text-primary border border-primary/20 px-2 py-0.5 rounded-md uppercase">
                                            {{ session.room?.type || 'Standard' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center border-t border-outline-variant/20 pt-3 mt-1">
                                        <span class="font-serif text-lg font-bold text-primary">${{ session.price }}</span>
                                        <router-link 
                                            :to="'/reservation/' + session.id" 
                                            class="px-5 py-2.5 gold-gradient rounded-lg font-bold text-xs uppercase tracking-widest hover:brightness-105 active:scale-[0.98] transition-all shadow-sm shadow-primary/10"
                                        >
                                            Select Seats
                                        </router-link>
                                    </div>
                                </div>
                            </template>
                            <div v-else class="py-10 text-center text-xs text-outline border border-dashed border-outline-variant/50 rounded-xl">
                                No showtimes scheduled for this film today.
                            </div>
                        </div>
                    </div>
                </aside>
            </main>
        </div>

        <div v-else class="text-center py-20 border border-dashed border-outline-variant max-w-md mx-auto rounded-2xl space-y-4">
            <h2 class="font-serif text-2xl font-bold text-primary">Film Not Found</h2>
            <p class="text-sm text-on-surface-variant">The film registry does not contain this title.</p>
            <router-link to="/" class="inline-block px-6 py-2.5 bg-primary text-on-primary rounded-lg text-xs font-bold uppercase tracking-widest hover:bg-primary-hover transition-colors">
                Return to Collection
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../store/auth';

const route = useRoute();
const auth = useAuthStore();

const film = ref(null);
const sessions = ref([]);
const loading = ref(true);
const isFavourited = ref(false);
const selectedDate = ref(null);

// Calendar-day key (local) used to group/compare sessions by date.
const dayKey = (d) => {
    const dt = new Date(d);
    return `${dt.getFullYear()}-${dt.getMonth()}-${dt.getDate()}`;
};

// Distinct available dates derived from this film's sessions, sorted ascending.
const availableDates = computed(() => {
    const map = new Map();
    for (const s of sessions.value) {
        if (!s.start_time) continue;
        const key = dayKey(s.start_time);
        if (!map.has(key)) map.set(key, new Date(s.start_time));
    }
    return [...map.values()]
        .sort((a, b) => a - b)
        .map((dt) => ({
            key: dayKey(dt),
            weekday: dt.toLocaleDateString([], { weekday: 'short' }).toUpperCase(),
            day: dt.getDate(),
        }));
});

// Sessions falling on the selected date, sorted by start time.
const sessionsForSelectedDate = computed(() => {
    if (!selectedDate.value) return [];
    return sessions.value
        .filter((s) => s.start_time && dayKey(s.start_time) === selectedDate.value)
        .sort((a, b) => new Date(a.start_time) - new Date(b.start_time));
});

// Default to the nearest available date once sessions load.
watch(availableDates, (dates) => {
    if (dates.length && !dates.some((d) => d.key === selectedDate.value)) {
        selectedDate.value = dates[0].key;
    }
}, { immediate: true });

const fetchFilm = async () => {
    try {
        const response = await axios.get(`/api/v1/films/${route.params.id}`);
        film.value = response.data.film;

        const sessionsRes = await axios.get('/api/v1/sessions');
        sessions.value = (sessionsRes.data.film_sessions || []).filter(s => s.film_id === parseInt(route.params.id));

        if (auth.isAuthenticated) {
            const favRes = await axios.get(`/api/v1/favourites/check/${route.params.id}`);
            isFavourited.value = favRes.data.is_favourite;
        }
    } catch (err) {
        console.error('Failed to fetch film:', err);
    } finally {
        loading.value = false;
    }
};

const toggleFavourite = async () => {
    try {
        const res = await axios.post('/api/v1/favourites/toggle', { film_id: film.value.id });
        isFavourited.value = res.data.is_favourite;
    } catch { /* silent */ }
};

const formatDateTime = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' });
};

const getEmbedUrl = (url) => {
    if (!url) return '';
    const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
    if (ytMatch) return `https://www.youtube.com/embed/${ytMatch[1]}`;
    return url;
};

const getImageUrl = (image) => {
    if (!image || !image.path) return 'https://via.placeholder.com/600x900?text=No+Image';
    if (image.path.startsWith('http://') || image.path.startsWith('https://')) {
        return image.path;
    }
    return `/storage/${image.path}`;
};

onMounted(fetchFilm);
</script>
