<template>
    <div class="group relative bg-white/5 backdrop-blur-md rounded-2xl overflow-hidden border border-white/10 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:shadow-black/50 hover:border-white/20 text-white w-full max-w-[300px]">
        <!-- Image Wrapper -->
        <div class="relative aspect-2/3 overflow-hidden">
            <img 
                :src="film.image?.path || 'https://via.placeholder.com/300x450?text=No+Image'" 
                :alt="film.title" 
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" 
            />
            <!-- Overlay -->
            <div class="absolute inset-0 bg-linear-to-t from-black/80 via-transparent to-transparent flex flex-col justify-between p-4 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                <div class="self-end bg-yellow-400 text-black px-2 py-0.5 rounded text-xs font-bold shadow-lg shadow-yellow-400/30">
                    {{ film.rate }}
                </div>
                <button class="w-full py-2.5 rounded-lg bg-yellow-600 hover:bg-yellow-700 text-white font-bold uppercase text-[0.85rem] transition-colors">
                    View Details
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-4">
            <h3 class="text-xl font-bold mb-2 truncate bg-linear-to-r from-white to-gray-400 bg-clip-text text-transparent">
                {{ film.title }}
            </h3>
            
            <div class="flex flex-col gap-2 mb-3">
                <span class="flex items-center gap-1 text-sm text-gray-400">
                    <svg viewBox="0 0 24 24" class="w-3.5 h-3.5 fill-current">
                        <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"/><path d="M13 7h-2v6h6v-2h-4z"/>
                    </svg>
                    {{ film.duration }} min
                </span>
                
                <div class="flex flex-wrap gap-1.5">
                    <span v-for="genre in film.genres" :key="genre.id" class="text-[0.7rem] px-2 py-0.5 bg-white/10 rounded-full text-gray-300 border border-white/5">
                        {{ genre.name }}
                    </span>
                </div>
            </div>

            <p class="text-sm leading-relaxed text-gray-400 line-clamp-3">
                {{ film.description }}
            </p>
        </div>
    </div>
</template>

<script setup>
defineProps({
    film: {
        type: Object,
        required: true
    }
});
</script>
