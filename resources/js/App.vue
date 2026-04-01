<template>
    <div class="min-h-screen bg-[#0c0c0c] text-white py-12 px-5 font-sans selection:bg-red-500/30">
        <!-- Header -->
        <header class="text-center mb-16 space-y-3">
            <h1 class="text-5xl md:text-7xl font-extrabold uppercase tracking-[0.2em] bg-linear-to-r from-yellow-600 to-yellow-400 bg-clip-text text-transparent inline-block">
                CineHall
            </h1>
            <p class="text-gray-500 text-lg md:text-xl font-medium tracking-wide">
                Experience cinema like never before
            </p>
        </header>
        
        <!-- Main Content -->
        <main class="max-w-7xl mx-auto">
            <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-4">
                <div class="w-12 h-12 border-4 border-red-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-gray-400 animate-pulse">Loading amazing movies...</p>
            </div>
            
            <div v-else-if="error" class="bg-red-900/20 border border-red-500/50 rounded-2xl p-8 text-center max-w-2xl mx-auto">
                <h3 class="text-2xl font-bold text-red-500 mb-2">Oops! Something went wrong</h3>
                <p class="text-red-300/80 mb-6">{{ error }}</p>
                <button @click="fetchFilms" class="px-6 py-2 bg-red-600 hover:bg-red-700 rounded-lg font-bold transition-colors">
                    Try Again
                </button>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 justify-items-center">
                <FilmCard v-for="film in films" :key="film.id" :film="film" />
            </div>
            
            <div v-if="!loading && films.length === 0" class="text-center py-20">
                <p class="text-gray-500 text-xl">No films found in the database.</p>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import FilmCard from './components/FilmCard.vue';

const films = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchFilms = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get('/api/v1/films');
        films.value = response.data.data || response.data;
    } catch (err) {
        console.error('Error fetching films:', err);
        error.value = 'We couldn\'t load the movie list. Please check your connection or try again later.';
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchFilms();
});
</script>