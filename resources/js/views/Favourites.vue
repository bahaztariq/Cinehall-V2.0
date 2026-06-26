<template>
    <div class="max-w-7xl mx-auto px-6 sm:px-12 lg:px-24 space-y-10 pb-20 bg-surface">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-outline-variant/20 pb-6">
            <div>
                <h1 class="font-serif text-3xl font-bold text-primary">Saved Collection</h1>
                <p class="text-xs text-on-surface-variant uppercase font-bold tracking-wider mt-1">Your private archive of preferred screenings</p>
            </div>
            <div class="bg-primary/10 border border-primary/20 px-4 py-1.5 rounded-full text-xs font-bold text-primary self-start sm:self-auto uppercase tracking-wide">
                {{ favourites.length }} Film{{ favourites.length !== 1 ? 's' : '' }} Saved
            </div>
        </div>

        <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3">
            <div class="w-6 h-6 border-2 border-primary border-t-transparent animate-spin rounded-full"></div>
            <p class="text-outline uppercase tracking-widest text-[10px] font-bold">Accessing Archive Vault...</p>
        </div>

        <div v-else-if="favourites.length === 0" class="py-24 text-center border border-dashed border-outline-variant/50 rounded-2xl space-y-6">
            <p class="text-on-surface-variant text-sm font-medium">Your personal film registry is currently vacant.</p>
            <router-link to="/" class="inline-block px-8 py-3.5 gold-gradient font-bold rounded-lg tracking-widest uppercase text-xs shadow-md transition-all">
                Explore Film database
            </router-link>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 sm:gap-8">
            <div 
                v-for="fav in favourites" 
                :key="fav.id" 
                class="bg-surface-container rounded-xl border border-outline-variant/30 p-4 hover:border-primary/50 hover:shadow-lg transition-all duration-300 flex flex-col justify-between group"
            >
                <div>
                    <!-- Image Wrapper with correct aspect-ratio -->
                    <div class="relative aspect-2/3 overflow-hidden rounded-lg bg-surface-container select-none mb-4 shadow-sm">
                        <img
                            :src="getImageUrl(fav.film?.image)"
                            :alt="fav.film?.title"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                        />
                        <div class="absolute top-3 right-3 bg-surface-container/90 backdrop-blur text-primary text-[10px] font-bold px-2.5 py-0.5 rounded-md border border-outline-variant/30 shadow-sm">
                            {{ fav.film?.rate }}/10
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        <h3 class="font-serif text-base font-bold text-on-surface truncate group-hover:text-primary transition-colors">{{ fav.film?.title }}</h3>
                        
                        <div class="flex justify-between text-[10px] text-outline font-bold uppercase tracking-wider">
                            <span>Duration: {{ fav.film?.duration }} Mins</span>
                        </div>

                        <div class="flex flex-wrap gap-1.5 pt-1">
                            <span v-for="genre in fav.film?.genres" :key="genre.id" class="text-[9px] font-bold px-2 py-0.5 bg-surface-container-low border border-outline-variant/20 text-on-surface-variant rounded uppercase tracking-wider">
                                {{ genre.name }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-outline-variant/20 flex gap-2">
                    <router-link :to="`/Film/${fav.film?.id}`" class="flex-1 py-2.5 gold-gradient rounded-lg text-center text-xs font-bold uppercase tracking-widest shadow-sm hover:brightness-105 active:scale-95 transition-all">
                        View Shows
                    </router-link>
                    <button
                        @click="removeFavourite(fav)"
                        class="px-3 py-2.5 bg-red-500/15 hover:bg-red-500/25 border border-red-500/30 text-red-400 rounded-lg text-xs font-bold transition-colors cursor-pointer"
                        title="Remove from Saved"
                        aria-label="Remove from Saved"
                    >
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">delete</span>
                    </button>
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

const getImageUrl = (image) => {
    if (!image || !image.path) return 'https://via.placeholder.com/300x450?text=No+Image';
    if (image.path.startsWith('http://') || image.path.startsWith('https://')) {
        return image.path;
    }
    return `/storage/${image.path}`;
};

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
