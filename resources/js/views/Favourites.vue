<template>
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <h1 class="text-5xl font-black uppercase italic tracking-tighter">My <span class="text-yellow-600">Favourites</span></h1>
            <div class="bg-white/5 border border-white/10 px-6 py-2 rounded-xl text-sm font-bold text-gray-400">
                {{ favourites.length }} Film{{ favourites.length !== 1 ? 's' : '' }}
            </div>
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-yellow-600"></div>
        </div>

        <div v-else-if="favourites.length === 0" class="py-24 text-center border border-white/10 border-dashed rounded-2xl">
            <p class="text-6xl mb-6">🎬</p>
            <p class="text-gray-500 text-xl italic">You haven't favourited any films yet.</p>
            <router-link to="/" class="mt-6 inline-block px-8 py-3 bg-yellow-600 hover:bg-yellow-700 rounded-xl font-bold uppercase tracking-widest transition-all">
                Browse Films
            </router-link>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="fav in favourites" :key="fav.id" class="group relative bg-white/5 backdrop-blur-md rounded-2xl overflow-hidden border border-white/10 transition-all duration-300 hover:-translate-y-1 hover:border-white/20 hover:shadow-xl">
                <div class="relative aspect-2/3 overflow-hidden">
                    <img
                        :src="fav.film?.image?.path || 'https://via.placeholder.com/300x450?text=No+Image'"
                        :alt="fav.film?.title"
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                    />
                    <div class="absolute inset-0 bg-linear-to-t from-black/80 via-transparent to-transparent flex flex-col justify-between p-4">
                        <div class="self-end bg-yellow-400 text-black px-2 py-0.5 rounded text-xs font-bold">
                            {{ fav.film?.rate }}
                        </div>
                        <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <router-link :to="`/Film/${fav.film?.id}`" class="flex-1 py-2 rounded-lg bg-yellow-600 hover:bg-yellow-700 text-white font-bold text-xs text-center uppercase tracking-widest transition-all">
                                View
                            </router-link>
                            <button @click="removeFavourite(fav)" class="py-2 px-3 rounded-lg bg-red-600/20 hover:bg-red-600 border border-red-600/30 text-red-400 hover:text-white font-bold text-xs uppercase tracking-widest transition-all">
                                ✕
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-lg truncate">{{ fav.film?.title }}</h3>
                    <p class="text-gray-500 text-xs mt-1">{{ fav.film?.duration }} min</p>
                    <div class="flex flex-wrap gap-1 mt-2">
                        <span v-for="genre in fav.film?.genres" :key="genre.id" class="text-[0.65rem] px-2 py-0.5 bg-white/10 rounded-full text-gray-400 border border-white/5">
                            {{ genre.name }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../store/auth';

const auth = useAuthStore();
const favourites = ref([]);
const loading = ref(true);

const fetchFavourites = async () => {
    try {
        const res = await axios.get('/api/v1/favourites', {
            headers: { Authorization: `Bearer ${auth.token}` }
        });
        favourites.value = res.data.favourites || [];
    } catch (err) {
        console.error('Failed to fetch favourites:', err);
    } finally {
        loading.value = false;
    }
};

const removeFavourite = async (fav) => {
    try {
        await axios.post('/api/v1/favourites/toggle', { film_id: fav.film_id }, {
            headers: { Authorization: `Bearer ${auth.token}` }
        });
        favourites.value = favourites.value.filter(f => f.id !== fav.id);
    } catch (err) {
        console.error('Failed to remove favourite:', err);
    }
};

onMounted(fetchFavourites);
</script>
