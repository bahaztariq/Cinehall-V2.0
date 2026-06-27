<template>
    <div class="pb-20 space-y-16 sm:space-y-24 bg-surface text-on-surface overflow-x-clip">
        <!-- Full-Screen Hero Slider -->
        <div
            v-if="sliderFilms.length"
            class="relative w-full h-dvh min-h-[600px] overflow-hidden border-b border-outline-variant/30 bg-surface-container"
            @mouseenter="hovered = true; stopSlider()"
            @mouseleave="hovered = false; startSlider()"
        >
            <!-- Slides Container -->
            <div
                v-for="(film, idx) in sliderFilms"
                :key="film.id"
                v-show="idx === currentSlide"
                class="absolute inset-0 w-full h-full animate-fade-in"
            >
                <!-- Background image (poster / fallback) -->
                <img
                    :src="film.backdrop || getImageUrl(film.image)"
                    class="w-full h-full object-cover relative z-0 scale-100 transition-all duration-1000"
                    alt="Backdrop"
                />

                <!-- Trailer plays on hover (muted, looping); poster shows otherwise -->
                <div
                    v-if="idx === currentSlide && hovered && getEmbedId(film.trailer)"
                    class="absolute inset-0 z-1 overflow-hidden bg-black pointer-events-none"
                >
                    <iframe
                        :src="bgTrailerSrc(film.trailer)"
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[177.78vh] min-w-full h-[56.25vw] min-h-full pointer-events-none"
                        frameborder="0"
                        allow="autoplay; encrypted-media"
                        title="Trailer background"
                    ></iframe>
                </div>

                <!-- Cinematic scrim: dark on text side (left), backdrop visible on right -->
                <div class="absolute inset-0 bg-linear-to-r from-surface via-surface/55 to-transparent z-10"></div>
                <div class="absolute inset-0 bg-linear-to-t from-surface/90 via-transparent to-transparent z-10"></div>

                <!-- Content overlay -->
                <div class="absolute inset-y-0 left-0 right-0 flex flex-col justify-center px-5 sm:px-12 lg:px-24 max-w-full sm:max-w-3xl z-20 space-y-5 sm:space-y-6 pb-24 sm:pb-0">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="bg-primary/10 text-primary text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                            ★ {{ film.rate }} Rating
                        </span>
                        <span class="bg-surface-container-low text-on-surface-variant text-[10px] px-3 py-1 rounded-full uppercase tracking-wider border border-outline-variant/20">
                            {{ film.duration }} Min
                        </span>
                        <span v-for="genre in film.genres.slice(0, 2)" :key="genre.id" class="text-[10px] text-primary font-bold tracking-widest uppercase">
                            // {{ genre.name }}
                        </span>
                    </div>

                    <h1 class="font-serif text-3xl sm:text-6xl font-bold text-on-surface leading-tight tracking-tight">
                        {{ film.title }}
                    </h1>

                    <p class="text-sm sm:text-base text-on-surface-variant leading-relaxed line-clamp-3 max-w-xl">
                        {{ film.description }}
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 sm:gap-4 pt-2">
                        <router-link
                            :to="'/Film/' + film.id"
                            class="px-8 min-h-[48px] inline-flex items-center justify-center blue-gradient text-xs font-bold uppercase tracking-widest rounded-lg hover:brightness-110 shadow-lg transition-all active:scale-95"
                        >
                            Book Tickets
                        </router-link>
                        <button
                            v-if="film.trailer"
                            @click="openTrailer(film.trailer)"
                            class="px-8 min-h-[48px] bg-surface hover:bg-surface-container border border-outline-variant text-on-surface text-xs font-bold uppercase tracking-widest rounded-lg transition-colors cursor-pointer flex items-center justify-center gap-2"
                        >
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">play_circle</span>
                            Watch Trailer
                        </button>
                    </div>
                </div>
            </div>

            <!-- Slide Navigation Buttons (Dots) -->
            <div class="absolute bottom-6 sm:bottom-8 left-5 sm:left-auto sm:right-12 lg:right-24 z-30 flex gap-2">
                <button 
                    v-for="(film, idx) in sliderFilms" 
                    :key="film.id"
                    @click="setSlide(idx)"
                    :aria-label="'Go to slide ' + (idx + 1)"
                    :aria-current="idx === currentSlide ? 'true' : undefined"
                    :class="idx === currentSlide ? 'bg-primary text-on-primary border-primary font-bold px-4' : 'bg-surface/80 text-outline hover:text-primary border-outline-variant/40 px-3'"
                    class="py-1.5 border rounded-full text-xs transition-all duration-300"
                >
                    {{ String(idx + 1).padStart(2, '0') }}
                </button>
            </div>
        </div>

        <!-- Full-bleed slider sections -->
        <div class="space-y-16 sm:space-y-24">
            <!-- SECTION 1: NOW SHOWING -->
            <section class="space-y-6 px-4">
                <!-- Section Header: title + genre dropdown + arrows (top right) -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-outline-variant/30 pb-6">
                    <div>
                        <h2 class="font-serif text-3xl font-bold text-on-surface">Now Showing</h2>
                        <p class="text-sm text-on-surface-variant mt-1">Carefully curated cinematic experiences available today.</p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <!-- Genre dropdown -->
                        <div class="relative">
                            <button
                                @click="genreMenuOpen = !genreMenuOpen"
                                :aria-expanded="genreMenuOpen"
                                aria-label="Filter by genre"
                                class="flex items-center gap-2 min-h-[44px] px-4 rounded-full bg-surface-container border border-outline-variant text-on-surface text-[11px] font-bold uppercase tracking-wider hover:border-primary/60 transition-colors cursor-pointer"
                            >
                                <span class="material-symbols-outlined text-[16px] text-primary" aria-hidden="true">tune</span>
                                {{ selectedGenre ? selectedGenre.name : 'All Genres' }}
                                <span class="material-symbols-outlined text-[18px] text-on-surface-variant" aria-hidden="true">{{ genreMenuOpen ? 'expand_less' : 'expand_more' }}</span>
                            </button>
                            <div v-if="genreMenuOpen" class="fixed inset-0 z-40" @click="genreMenuOpen = false"></div>
                            <div v-if="genreMenuOpen" class="absolute right-0 mt-2 w-56 max-h-80 overflow-y-auto bg-surface-container border border-outline-variant rounded-xl shadow-[0_12px_40px_rgba(0,0,0,0.6)] z-50 py-1">
                                <button
                                    @click="selectedGenre = null; fetchFilms(); genreMenuOpen = false"
                                    :class="selectedGenre === null ? 'text-primary' : 'text-on-surface-variant hover:text-on-surface'"
                                    class="w-full text-left px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider hover:bg-surface-container-high transition-colors flex items-center justify-between cursor-pointer"
                                >
                                    All Genres
                                    <span v-if="selectedGenre === null" class="material-symbols-outlined text-[16px]" aria-hidden="true">check</span>
                                </button>
                                <button
                                    v-for="genre in genres"
                                    :key="genre.id"
                                    @click="filterByGenre(genre); genreMenuOpen = false"
                                    :class="selectedGenre?.id === genre.id ? 'text-primary' : 'text-on-surface-variant hover:text-on-surface'"
                                    class="w-full text-left px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider hover:bg-surface-container-high transition-colors flex items-center justify-between cursor-pointer"
                                >
                                    {{ genre.name }}
                                    <span v-if="selectedGenre?.id === genre.id" class="material-symbols-outlined text-[16px]" aria-hidden="true">check</span>
                                </button>
                            </div>
                        </div>

                        <!-- Scroll arrows (desktop) -->
                        <div class="hidden md:flex items-center gap-2">
                            <button @click="scrollSlider(nowShowingSlider, 'left')" aria-label="Scroll left" class="w-10 h-10 rounded-full bg-surface-container border border-outline-variant flex items-center justify-center text-primary hover:bg-primary hover:text-on-primary transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">chevron_left</span>
                            </button>
                            <button @click="scrollSlider(nowShowingSlider, 'right')" aria-label="Scroll right" class="w-10 h-10 rounded-full bg-surface-container border border-outline-variant flex items-center justify-center text-primary hover:bg-primary hover:text-on-primary transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">chevron_right</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Loading / Error states -->
                <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3">
                    <div class="w-8 h-8 border-2 border-primary border-t-transparent animate-spin rounded-full"></div>
                    <p class="text-outline uppercase tracking-widest text-[10px] font-bold">Synchronizing directory...</p>
                </div>

                <div v-else-if="error" class="border border-red-500/30 bg-red-500/10 p-8 text-center max-w-md mx-auto rounded-xl space-y-4">
                    <p class="text-red-400 font-bold">Failed to synchronize remote cinema directory.</p>
                    <button @click="fetchFilms" class="px-5 py-2.5 bg-primary text-on-primary rounded-lg font-bold hover:bg-primary/90 transition-colors">Retry</button>
                </div>

                <!-- Full-bleed Film Slider -->
                <div
                    v-else-if="films.length"
                    ref="nowShowingSlider"
                    class="flex gap-5 sm:gap-6 overflow-x-auto py-2 scroll-smooth scrollbar-none snap-x snap-mandatory"
                >
                    <div
                        v-for="film in films"
                        :key="film.id"
                        class="min-w-[68vw] sm:min-w-[260px] max-w-[68vw] sm:max-w-[260px] snap-start"
                    >
                        <FilmCard :film="film" />
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="text-center py-16 border border-dashed border-outline-variant rounded-xl text-sm text-outline">
                    No screenings currently allocated in this category.
                </div>
            </section>

            <!-- SECTION 1.5: COMING SOON -->
            <section class="space-y-6 px-4">
                <!-- Section Header: title + arrows (top right) -->
                <div class="flex items-end justify-between gap-4 border-b border-outline-variant/30 pb-6">
                    <div>
                        <h2 class="font-serif text-3xl font-bold text-on-surface">Coming Soon</h2>
                        <p class="text-sm text-on-surface-variant mt-1">Exclusive upcoming masterpieces arriving soon to our auditoriums.</p>
                    </div>
                    <div class="hidden md:flex items-center gap-2 shrink-0">
                        <button @click="scrollSlider(comingSoonSlider, 'left')" aria-label="Scroll left" class="w-10 h-10 rounded-full bg-surface-container border border-outline-variant flex items-center justify-center text-primary hover:bg-primary hover:text-on-primary transition-colors cursor-pointer">
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">chevron_left</span>
                        </button>
                        <button @click="scrollSlider(comingSoonSlider, 'right')" aria-label="Scroll right" class="w-10 h-10 rounded-full bg-surface-container border border-outline-variant flex items-center justify-center text-primary hover:bg-primary hover:text-on-primary transition-colors cursor-pointer">
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">chevron_right</span>
                        </button>
                    </div>
                </div>

                <!-- Loading state -->
                <div v-if="loadingComingSoon" class="flex flex-col items-center justify-center py-10 gap-3">
                    <div class="w-8 h-8 border-2 border-primary border-t-transparent animate-spin rounded-full"></div>
                </div>

                <!-- Full-bleed Coming Soon Slider -->
                <div
                    v-else-if="comingSoonFilms.length"
                    ref="comingSoonSlider"
                    class="flex gap-5 sm:gap-6 overflow-x-auto py-2 scroll-smooth scrollbar-none snap-x snap-mandatory"
                >
                    <div
                        v-for="film in comingSoonFilms"
                        :key="film.id"
                        class="min-w-[68vw] sm:min-w-[260px] max-w-[68vw] sm:max-w-[260px] snap-start"
                    >
                        <FilmCard :film="film" />
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="text-center py-16 border border-dashed border-outline-variant rounded-xl text-sm text-outline">
                    No upcoming films scheduled at the moment.
                </div>
            </section>

        </div>

        <!-- Contained content sections -->
        <div class="max-w-7xl mx-auto px-5 sm:px-12 lg:px-24 space-y-24">
            <!-- SECTION 2: MEMBER EXCLUSIVE PERKS -->
            <section class="space-y-8">
                <div class="border-b border-outline-variant/30 pb-6 text-center md:text-left">
                    <h2 class="font-serif text-3xl font-bold text-on-surface">Member Exclusive Perks</h2>
                    <p class="text-sm text-on-surface-variant mt-1">Indulge in unparalleled service designed around you.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Lounge Access -->
                    <div class="bg-surface-container rounded-xl p-8 border border-outline-variant/30 shadow-[0_8px_30px_rgba(0,0,0,0.45)] hover:border-primary/50 transition-all group">
                        <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary mb-6 group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-[24px]" aria-hidden="true">local_bar</span>
                        </div>
                        <h3 class="font-serif text-xl font-bold text-on-surface mb-3">VIP Lounge Entry</h3>
                        <p class="text-sm text-on-surface-variant leading-relaxed">
                            Access our luxury private lounges prior to screening times. Relax with premium mixology and fine dining selections.
                        </p>
                    </div>

                    <!-- Priority Booking -->
                    <div class="bg-surface-container rounded-xl p-8 border border-outline-variant/30 shadow-[0_8px_30px_rgba(0,0,0,0.45)] hover:border-primary/50 transition-all group">
                        <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary mb-6 group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-[24px]" aria-hidden="true">confirmation_number</span>
                        </div>
                        <h3 class="font-serif text-xl font-bold text-on-surface mb-3">Priority Ticket Booking</h3>
                        <p class="text-sm text-on-surface-variant leading-relaxed">
                            Acquire prime seating options 48 hours before general release. Never miss out on major premiere listings.
                        </p>
                    </div>

                    <!-- In-Seat Service -->
                    <div class="bg-surface-container rounded-xl p-8 border border-outline-variant/30 shadow-[0_8px_30px_rgba(0,0,0,0.45)] hover:border-primary/50 transition-all group">
                        <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary mb-6 group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-[24px]" aria-hidden="true">room_service</span>
                        </div>
                        <h3 class="font-serif text-xl font-bold text-on-surface mb-3">In-Seat Concierge</h3>
                        <p class="text-sm text-on-surface-variant leading-relaxed">
                            Enjoy fine food and beverage selections brought straight to your leather recliner seat with call-button concierge.
                        </p>
                    </div>
                </div>
            </section>

            <!-- SECTION 3: EDITORIAL & CINEMA NEWS -->
            <section class="space-y-8">
                <div class="border-b border-outline-variant/30 pb-6">
                    <h2 class="font-serif text-3xl font-bold text-on-surface">Cinema Chronicles</h2>
                    <p class="text-sm text-on-surface-variant mt-1">Insights and articles from the forefront of filmmaking.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Article 1 -->
                    <div class="flex flex-col sm:flex-row gap-6 bg-surface-container rounded-xl p-6 border border-outline-variant/30 shadow-[0_8px_30px_rgba(0,0,0,0.45)]">
                        <div class="w-full sm:w-44 h-44 rounded-lg overflow-hidden shrink-0 bg-surface-container">
                            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuBBYr0f8IqIOYRFKKPe9JYOvZOALBsklbjaG0doZqbTsxnXW6MoXTIJhEIvZ6az0sz4xbVXTDHzhe4-D_Go0sF7dme7avbHahYEpdy4E4NnnvlMD6zB4SsTN04a356VyeButtldia5FP67w7wJTsTpepoCaxcfeWr59VTYcXERrMZ_ut5RJTZcqN5QUzmD7Rs5AbbVHE_hOl8P9tHH_smgZpG8-VpBzyARN8r2El2eTE4RLLT6U7ATQDzXg4CgBtXPeqUZZ8P3SKiA" class="w-full h-full object-cover" alt="Cinema editorial" />
                        </div>
                        <div class="flex flex-col justify-between py-1">
                            <div>
                                <span class="text-[10px] text-primary font-bold uppercase tracking-widest">Festival Focus</span>
                                <h3 class="font-serif text-lg font-bold text-on-surface mt-2 mb-2">The Magic of 70mm Film Prints</h3>
                                <p class="text-xs text-on-surface-variant leading-relaxed line-clamp-3">
                                    Discover why direct-from-negative analog projection holds the crown for visual purity and high contrast, and explore this weekend's schedule.
                                </p>
                            </div>
                            <span class="text-[10px] text-outline uppercase font-bold tracking-widest mt-4">Read Article ➔</span>
                        </div>
                    </div>

                    <!-- Article 2 -->
                    <div class="flex flex-col sm:flex-row gap-6 bg-surface-container rounded-xl p-6 border border-outline-variant/30 shadow-[0_8px_30px_rgba(0,0,0,0.45)]">
                        <div class="w-full sm:w-44 h-44 rounded-lg overflow-hidden shrink-0 bg-surface-container">
                            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuBBYr0f8IqIOYRFKKPe9JYOvZOALBsklbjaG0doZqbTsxnXW6MoXTIJhEIvZ6az0sz4xbVXTDHzhe4-D_Go0sF7dme7avbHahYEpdy4E4NnnvlMD6zB4SsTN04a356VyeButtldia5FP67w7wJTsTpepoCaxcfeWr59VTYcXERrMZ_ut5RJTZcqN5QUzmD7Rs5AbbVHE_hOl8P9tHH_smgZpG8-VpBzyARN8r2El2eTE4RLLT6U7ATQDzXg4CgBtXPeqUZZ8P3SKiA" class="w-full h-full object-cover" alt="Cinema editorial" />
                        </div>
                        <div class="flex flex-col justify-between py-1">
                            <div>
                                <span class="text-[10px] text-primary font-bold uppercase tracking-widest">Gourmet Corner</span>
                                <h3 class="font-serif text-lg font-bold text-on-surface mt-2 mb-2">Crafting Our New Concession Menu</h3>
                                <p class="text-xs text-on-surface-variant leading-relaxed line-clamp-3">
                                    Partnering with local organic dairies and pastry chefs, we've designed a bespoke tasting menu to complement your viewing experience.
                                </p>
                            </div>
                            <span class="text-[10px] text-outline uppercase font-bold tracking-widest mt-4">Read Article ➔</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION 4: MEMBERSHIP REGISTRATION -->
            <section class="rounded-2xl p-6 sm:p-8 md:p-16 flex flex-col md:flex-row justify-between items-center gap-8 md:gap-10 bg-surface-container border border-outline-variant/30 shadow-[0_8px_30px_rgba(0,0,0,0.45)] relative overflow-hidden">
                <div class="absolute right-0 bottom-0 opacity-5 pointer-events-none select-none -translate-x-12 translate-y-12">
                    <span class="font-serif text-9xl font-bold text-primary">VIP</span>
                </div>

                <div class="space-y-4 max-w-2xl relative z-10">
                    <span class="text-[10px] text-primary font-bold tracking-widest uppercase">The Aurelian Circle</span>
                    <h3 class="font-serif text-3xl font-bold text-on-surface">Become a Cinehall VIP and unlock benefits</h3>
                    <p class="text-sm text-on-surface-variant leading-relaxed">
                        Earn reward points on all bookings, receive invitations to exclusive pre-release screenings, and enjoy a complimentary gourmet popcorn combo on your birthday.
                    </p>
                </div>

                <div class="shrink-0 space-y-3 w-full md:w-auto relative z-10">
                    <router-link to="/register" class="block text-center px-10 py-4 blue-gradient text-xs font-bold uppercase tracking-widest rounded-lg hover:brightness-110 shadow-lg transition-all scale-95 active:scale-90">
                        Join Circle Free
                    </router-link>
                    <p class="text-center text-[10px] text-outline uppercase tracking-widest">No membership fees required.</p>
                </div>
            </section>
        </div>

        <!-- Trailer Video Modal Overlay -->
        <div v-if="activeTrailerUrl" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-on-surface/90 backdrop-blur-sm" @click="closeTrailer">
            <div role="dialog" aria-modal="true" class="relative w-full max-w-4xl aspect-video bg-black border border-primary/30 shadow-2xl rounded-xl overflow-hidden" @click.stop>
                <button @click="closeTrailer" aria-label="Close trailer" class="absolute top-3 right-3 sm:top-4 sm:right-4 z-50 bg-black/60 hover:bg-black/90 text-white hover:text-primary w-11 h-11 rounded-full border border-white/20 transition-all flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">close</span>
                </button>
                <iframe
                    :src="getEmbedUrl(activeTrailerUrl)"
                    class="w-full h-full"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                ></iframe>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import FilmCard from '../components/FilmCard.vue';

const films = ref([]);
const sliderFilms = ref([]);
const genres = ref([]);
const loading = ref(true);
const error = ref(null);
const selectedGenre = ref(null);
const genreMenuOpen = ref(false);
const hovered = ref(false);
const activeTrailerUrl = ref(null);

// Slider States
const currentSlide = ref(0);
let slideInterval = null;

// Slider Refs and Coming Soon States
const nowShowingSlider = ref(null);
const comingSoonSlider = ref(null);
const comingSoonFilms = ref([]);
const loadingComingSoon = ref(true);

const startSlider = () => {
    if (slideInterval) clearInterval(slideInterval);
    slideInterval = setInterval(() => {
        if (sliderFilms.value.length > 0) {
            currentSlide.value = (currentSlide.value + 1) % sliderFilms.value.length;
        }
    }, 6000);
};

const stopSlider = () => {
    if (slideInterval) clearInterval(slideInterval);
};

const setSlide = (idx) => {
    currentSlide.value = idx;
};

const openTrailer = (url) => {
    activeTrailerUrl.value = url;
};

const closeTrailer = () => {
    activeTrailerUrl.value = null;
};

const getEmbedUrl = (url) => {
    if (!url) return '';
    const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
    if (ytMatch) return `https://www.youtube.com/embed/${ytMatch[1]}?autoplay=1`;
    return url;
};

// Hero background trailer: muted, looping autoplay behind the scrim/content
const getEmbedId = (url) => {
    if (!url) return null;
    const m = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
    return m ? m[1] : null;
};
const bgTrailerSrc = (url) => {
    const id = getEmbedId(url);
    if (!id) return '';
    return `https://www.youtube.com/embed/${id}?autoplay=1&mute=1&controls=0&loop=1&playlist=${id}&modestbranding=1&rel=0&showinfo=0&playsinline=1&disablekb=1`;
};

const getImageUrl = (image) => {
    if (!image || !image.path) return 'https://via.placeholder.com/1920x1080?text=No+Image';
    if (image.path.startsWith('http://') || image.path.startsWith('https://')) {
        return image.path;
    }
    return `/storage/${image.path}`;
};

const scrollSlider = (el, direction) => {
    if (!el) return;
    const scrollAmount = 312; // Card width 280 + Gap 32
    if (direction === 'left') {
        el.scrollLeft -= scrollAmount * 2;
    } else {
        el.scrollLeft += scrollAmount * 2;
    }
};

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
        const response = await axios.get('/api/v1/films?all=true&coming_soon=0');
        const data = response.data;
        films.value = data;
        sliderFilms.value = data.slice(0, 4);
    } catch (err) {
        error.value = "Failed to synchronize remote cinema directory.";
    } finally {
        loading.value = false;
    }
};

const fetchComingSoon = async () => {
    loadingComingSoon.value = true;
    try {
        const response = await axios.get('/api/v1/films?all=true&coming_soon=1');
        comingSoonFilms.value = response.data || [];
    } catch (err) {
        console.error("Failed to fetch coming soon films:", err);
    } finally {
        loadingComingSoon.value = false;
    }
};

const filterByGenre = async (genre) => {
    selectedGenre.value = genre;
    loading.value = true;
    error.value = null;
    try {
        const res = await axios.get(`/api/v1/films/filter?genre_id=${genre.id}&coming_soon=0`);
        films.value = res.data.data || [];
    } catch {
        films.value = [];
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    fetchGenres();
    await fetchFilms();
    fetchComingSoon();
    startSlider();
});

onUnmounted(() => {
    stopSlider();
});
</script>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.8s ease-out forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(1.01);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.scrollbar-none::-webkit-scrollbar {
    display: none;
}
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
