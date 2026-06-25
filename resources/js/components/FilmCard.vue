<template>
    <router-link :to="'/Film/' + film.id" class="group cursor-pointer flex flex-col justify-between w-full max-w-sm">
        <div>
            <!-- Image Wrapper with rounded corners and glowing border on hover -->
            <div class="relative aspect-2/3 rounded-xl overflow-hidden mb-5 shadow-[0_8px_30px_rgba(0,0,0,0.45)] border-2 border-transparent transition-all duration-300 group-hover:border-primary/50 group-hover:scale-[1.01] bg-surface-container">
                <img
                    :src="getImageUrl(film.image)"
                    :alt="film.title"
                    class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
                />
                
                <!-- Floating rating & favourite actions -->
                <div class="absolute top-4 left-4 right-4 flex justify-between items-center z-10">
                    <!-- Favorite Button -->
                    <button
                        v-if="auth.isAuthenticated"
                        @click.prevent.stop="toggleFavourite"
                        :class="isFavourited ? 'text-red-400 bg-red-500/10 border-red-500/30' : 'text-outline bg-surface/90 border-outline-variant/30 hover:text-red-400'"
                        class="p-2 border rounded-full backdrop-blur transition-all duration-200 cursor-pointer shadow-sm hover:scale-105 flex items-center justify-center"
                        :title="isFavourited ? 'Remove from Favourites' : 'Add to Favourites'"
                        :aria-label="isFavourited ? 'Remove from favourites' : 'Add to favourites'"
                    >
                        <svg class="w-3.5 h-3.5" :fill="isFavourited ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </button>
                    <div v-else></div>

                    <!-- Rating badge -->
                    <div class="bg-surface/90 backdrop-blur px-2.5 py-1 rounded-md text-primary font-bold text-xs flex items-center gap-1 shadow-sm border border-outline-variant/20">
                        <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">star</span>
                        {{ film.rate }}
                    </div>
                </div>
            </div>

            <!-- Meta Tags & Title -->
            <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                    <span class="bg-primary/10 text-primary px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">
                        {{ film.duration }} MIN
                    </span>
                    <span v-for="genre in film.genres.slice(0, 2)" :key="genre.id" class="bg-surface-container-low text-on-surface-variant px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">
                        {{ genre.name }}
                    </span>
                </div>
                <h3 class="font-sans text-lg font-bold text-on-surface group-hover:text-primary transition-colors line-clamp-1">
                    {{ film.title }}
                </h3>
            </div>
        </div>

        <!-- Book Button -->
        <div class="mt-4">
            <button class="w-full py-3 border border-primary text-primary font-sans text-xs font-bold uppercase tracking-widest rounded-lg group-hover:bg-primary group-hover:text-on-primary transition-all duration-300">
                Book Now
            </button>
        </div>
    </router-link>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../store/auth';

const props = defineProps({
    film: { type: Object, required: true }
});

const auth = useAuthStore();
const isFavourited = ref(false);

const getImageUrl = (image) => {
    if (!image || !image.path) return 'https://via.placeholder.com/300x450?text=No+Image';
    if (image.path.startsWith('http://') || image.path.startsWith('https://')) {
        return image.path;
    }
    return `/storage/${image.path}`;
};

const checkFavourite = async () => {
    if (!auth.isAuthenticated) return;
    try {
        const res = await axios.get(`/api/v1/favourites/check/${props.film.id}`);
        isFavourited.value = res.data.is_favourite;
    } catch { /* silent */ }
};

const toggleFavourite = async () => {
    try {
        const res = await axios.post('/api/v1/favourites/toggle', { film_id: props.film.id });
        isFavourited.value = res.data.is_favourite;
    } catch { /* silent */ }
};

onMounted(checkFavourite);
</script>
