<template>
    <div>
        <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-yellow-600"></div>
        </div>

        <div v-else-if="film" class="space-y-12">
            <!-- Film Hero -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div class="aspect-2/3 rounded-2xl overflow-hidden border border-white/10 shadow-2xl shadow-black/60">
                    <img :src="film.image?.path || 'https://via.placeholder.com/600x900?text=No+Image'" :alt="film.title" class="w-full h-full object-cover" />
                </div>
                <div class="md:col-span-2 space-y-6">
                    <div>
                        <div class="flex flex-wrap items-center gap-3 mb-3">
                            <span v-for="genre in film.genres" :key="genre.id" class="text-xs uppercase tracking-widest font-bold text-yellow-600 px-3 py-1 bg-yellow-600/10 rounded-full border border-yellow-600/20">
                                {{ genre.name }}
                            </span>
                            <span class="ml-auto flex items-center gap-1 text-yellow-400 font-bold">
                                <span class="text-xl">★</span> {{ film.rate }} / 10
                            </span>
                        </div>
                        <h1 class="text-5xl md:text-7xl font-black uppercase italic tracking-tighter text-white">
                            {{ film.title }}
                        </h1>
                    </div>

                    <div class="flex gap-8 text-sm uppercase tracking-widest font-bold text-gray-500">
                        <div class="flex items-center gap-2">
                            <svg viewBox="0 0 24 24" class="w-5 h-5 fill-current"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"/><path d="M13 7h-2v6h6v-2h-4z"/></svg>
                            {{ film.duration }} Minutes
                        </div>
                    </div>

                    <p class="text-xl text-gray-400 leading-relaxed max-w-3xl">{{ film.description }}</p>

                    <!-- Favourite + actions -->
                    <div class="flex flex-wrap items-center gap-3">
                        <button v-if="auth.isAuthenticated" @click="toggleFavourite" class="flex items-center gap-2 px-5 py-2.5 rounded-xl border transition-all font-bold text-sm uppercase tracking-widest"
                            :class="isFavourited ? 'bg-red-600/10 border-red-600/30 text-red-400' : 'bg-white/5 border-white/10 text-gray-400 hover:border-red-600/30 hover:text-red-400'">
                            <svg class="w-4 h-4" :fill="isFavourited ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            {{ isFavourited ? 'Saved' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Trailer -->
            <section v-if="film.trailer" class="space-y-4">
                <h2 class="text-3xl font-black uppercase italic tracking-tighter">Watch <span class="text-yellow-600">Trailer</span></h2>
                <div class="relative w-full rounded-2xl overflow-hidden border border-white/10 shadow-2xl" style="aspect-ratio: 16/9;">
                    <iframe
                        :src="getEmbedUrl(film.trailer)"
                        class="absolute inset-0 w-full h-full"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                </div>
            </section>

            <!-- Sessions -->
            <section class="space-y-6">
                <h2 class="text-3xl font-black uppercase italic tracking-tighter">Available <span class="text-yellow-600">Sessions</span></h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template v-if="sessions.length">
                        <div v-for="session in sessions" :key="session.id" class="bg-white/5 border border-white/10 p-6 rounded-2xl hover:border-yellow-600/50 transition-all group">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-widest font-bold">Date & Time</p>
                                    <p class="text-lg font-black text-white italic">{{ formatDateTime(session.start_time) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 uppercase tracking-widest font-bold">Room</p>
                                    <p class="text-lg font-bold text-white">{{ session.room?.name || 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-xs text-gray-500 uppercase font-bold">{{ session.language }}</span>
                                <span class="text-yellow-600 font-black">${{ session.price }}</span>
                            </div>
                            <router-link :to="'/reservation/' + session.id" class="block w-full py-3 bg-white/5 group-hover:bg-yellow-600 text-white text-center font-bold uppercase tracking-widest rounded-xl border border-white/10 group-hover:border-yellow-600 transition-all">
                                Book Now
                            </router-link>
                        </div>
                    </template>
                    <div v-else class="col-span-full py-12 text-center text-gray-500 border border-white/10 border-dashed rounded-2xl italic">
                        No sessions currently available for this film.
                    </div>
                </div>
            </section>
        </div>

        <div v-else class="text-center py-20">
            <h2 class="text-3xl font-bold text-red-400">Film not found</h2>
            <router-link to="/" class="mt-4 inline-block text-yellow-600 hover:underline">← Go Home</router-link>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../store/auth';

const route = useRoute();
const auth = useAuthStore();

const film = ref(null);
const sessions = ref([]);
const loading = ref(true);
const isFavourited = ref(false);

const fetchFilm = async () => {
    try {
        const response = await axios.get(`/api/v1/films/${route.params.id}`);
        film.value = response.data.film;

        const sessionsRes = await axios.get('/api/v1/sessions');
        sessions.value = (sessionsRes.data.film_sessions || []).filter(s => s.film_id === parseInt(route.params.id));

        if (auth.isAuthenticated) {
            const favRes = await axios.get(`/api/v1/favourites/check/${route.params.id}`, {
                headers: { Authorization: `Bearer ${auth.token}` }
            });
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
        const res = await axios.post('/api/v1/favourites/toggle', { film_id: film.value.id }, {
            headers: { Authorization: `Bearer ${auth.token}` }
        });
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

onMounted(fetchFilm);
</script>
