<template>
    <div class="space-y-10">
        <!-- Hero Search Banner (mobile) -->
        <div class="md:hidden">
            <div class="relative">
                <input
                    v-model="searchQuery"
                    @keyup.enter="applySearch"
                    type="text"
                    placeholder="Search films..."
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 pr-10 text-white placeholder-gray-500 focus:outline-none focus:border-yellow-600/60 transition-all"
                />
                <button @click="applySearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-yellow-600">
                    <svg class="w-4 h-4 fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                </button>
            </div>
        </div>

        <!-- Genre Filter Tabs -->
        <div class="flex flex-wrap gap-2">
            <button
                @click="selectedGenre = null; fetchFilms()"
                :class="selectedGenre === null ? 'bg-yellow-600 border-yellow-600 text-white' : 'bg-white/5 border-white/10 text-gray-400 hover:border-yellow-600/40'"
                class="px-4 py-2 rounded-full border text-xs font-bold uppercase tracking-widest transition-all"
            >
                All
            </button>
            <button
                v-for="genre in genres"
                :key="genre.id"
                @click="filterByGenre(genre)"
                :class="selectedGenre?.id === genre.id ? 'bg-yellow-600 border-yellow-600 text-white' : 'bg-white/5 border-white/10 text-gray-400 hover:border-yellow-600/40'"
                class="px-4 py-2 rounded-full border text-xs font-bold uppercase tracking-widest transition-all"
            >
                {{ genre.name }}
            </button>
        </div>

        <!-- Active search/filter indicator -->
        <div v-if="activeSearch || selectedGenre" class="flex items-center gap-3 text-sm text-gray-400">
            <span>Showing results for: </span>
            <span v-if="activeSearch" class="px-3 py-1 bg-yellow-600/10 border border-yellow-600/20 text-yellow-600 rounded-full font-bold">
                "{{ activeSearch }}"
            </span>
            <span v-if="selectedGenre" class="px-3 py-1 bg-yellow-600/10 border border-yellow-600/20 text-yellow-600 rounded-full font-bold">
                {{ selectedGenre.name }}
            </span>
            <button @click="clearFilters" class="text-gray-600 hover:text-white transition-colors underline text-xs">Clear</button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-4">
            <div class="w-12 h-12 border-4 border-yellow-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-gray-400 animate-pulse">Loading amazing movies...</p>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="bg-red-900/20 border border-red-500/50 rounded-2xl p-8 text-center max-w-2xl mx-auto">
            <h3 class="text-2xl font-bold text-red-500 mb-2">Oops! Something went wrong</h3>
            <p class="text-red-300/80 mb-6">{{ error }}</p>
            <button @click="fetchFilms" class="px-6 py-2 bg-red-600 hover:bg-red-700 rounded-lg font-bold transition-colors">
                Try Again
            </button>
        </div>

        <!-- Films Grid -->
        <div v-else-if="films.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 justify-items-center">
            <FilmCard v-for="film in films" :key="film.id" :film="film" />
        </div>

        <!-- Empty -->
        <div v-else class="text-center py-20 border border-white/10 border-dashed rounded-2xl">
            <p class="text-gray-500 text-xl italic">No films found.</p>
            <button @click="clearFilters" class="mt-4 text-yellow-600 hover:underline text-sm font-bold">Browse all films →</button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import FilmCard from '../components/FilmCard.vue';

const route = useRoute();
const router = useRouter();

const films = ref([]);
const genres = ref([]);
const loading = ref(true);
const error = ref(null);
const selectedGenre = ref(null);
const searchQuery = ref('');
const activeSearch = ref('');

const fetchGenres = async () => {
    try {
        const res = await axios.get('/api/v1/genres');
        genres.value = res.data.genres || res.data || [];
    } catch { /* silent */ }
};

const fetchFilms = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get('/api/v1/films');
        films.value = response.data.data || response.data;
    } catch (err) {
        error.value = "We couldn't load the movie list. Please check your connection.";
    } finally {
        loading.value = false;
    }
};

const filterByGenre = async (genre) => {
    selectedGenre.value = genre;
    activeSearch.value = '';
    loading.value = true;
    error.value = null;
    try {
        const res = await axios.get(`/api/v1/films/filter?genre_id=${genre.id}`);
        films.value = res.data.data || [];
    } catch {
        films.value = [];
    } finally {
        loading.value = false;
    }
};

const applySearch = async () => {
    const q = searchQuery.value.trim();
    if (!q) return;
    selectedGenre.value = null;
    activeSearch.value = q;
    loading.value = true;
    error.value = null;
    try {
        const res = await axios.get(`/api/v1/films/search?film=${encodeURIComponent(q)}`);
        films.value = res.data.data || [];
    } catch {
        films.value = [];
    } finally {
        loading.value = false;
    }
};

const clearFilters = () => {
    selectedGenre.value = null;
    activeSearch.value = '';
    searchQuery.value = '';
    router.replace({ query: {} });
    fetchFilms();
};

// Respond to URL search param (from Navbar search)
watch(() => route.query.search, (val) => {
    if (val) {
        searchQuery.value = val;
        applySearch();
    }
});

onMounted(() => {
    fetchGenres();
    if (route.query.search) {
        searchQuery.value = route.query.search;
        applySearch();
    } else {
        fetchFilms();
    }
});
</script>
