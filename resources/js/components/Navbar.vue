<template>
    <nav
        class="fixed top-0 w-full z-50 transition-colors duration-300"
        :class="scrolled || mobileMenu ? 'bg-surface/80 backdrop-blur-xl border-b border-outline-variant/60' : 'bg-transparent border-b border-transparent'"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-8 h-16 md:h-[4.5rem] flex items-center justify-between gap-3 md:gap-6">
            <!-- Brand: icon mark + single wordmark -->
            <router-link to="/" class="flex items-center gap-2 shrink-0 select-none group" aria-label="Cinehall home">
                <img src="/assets/logo_icon.png" class="h-8 w-8 md:h-9 md:w-9 object-contain" alt="" />
                <span class="font-serif text-xl md:text-2xl font-bold tracking-tight text-on-surface group-hover:text-primary transition-colors">
                    Cine<span class="text-primary">hall</span>
                </span>
            </router-link>

            <!-- Desktop Search -->
            <div class="flex-1 max-w-md relative hidden md:block">
                <div class="relative flex items-center bg-surface-container-low px-4 py-2.5 rounded-full border border-outline-variant focus-within:border-primary/60 transition-colors">
                    <span class="material-symbols-outlined text-on-surface-variant text-[18px] mr-2" aria-hidden="true">search</span>
                    <input
                        v-model="searchQuery"
                        @keyup.enter="doSearch"
                        @input="onInput"
                        type="text"
                        aria-label="Search films"
                        placeholder="Search films, genres..."
                        class="bg-transparent border-none outline-none focus:ring-0 text-sm text-on-surface w-full placeholder:text-on-surface-variant/60"
                    />
                    <button v-if="searchQuery" @click="clearSearch" aria-label="Clear search" class="text-on-surface-variant hover:text-primary transition-colors cursor-pointer ml-2">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">close</span>
                    </button>
                </div>
                <div v-if="suggestions.length" class="absolute top-full left-0 right-0 mt-2 bg-surface-container rounded-xl border border-outline-variant overflow-hidden shadow-[0_12px_40px_rgba(0,0,0,0.6)] z-50">
                    <router-link
                        v-for="film in suggestions"
                        :key="film.id"
                        :to="`/Film/${film.id}`"
                        @click="onPick"
                        class="flex items-center gap-3 px-4 py-3 border-b border-outline-variant/60 last:border-0 hover:bg-surface-container-high transition-colors"
                    >
                        <img :src="getImageUrl(film.image)" :alt="film.title" class="w-8 aspect-2/3 object-cover rounded border border-outline-variant" />
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-on-surface truncate">{{ film.title }}</p>
                            <p class="text-[0.6rem] text-primary font-bold tracking-widest uppercase mt-0.5">{{ film.duration }} MIN</p>
                        </div>
                    </router-link>
                </div>
            </div>

            <!-- Desktop Nav / Actions -->
            <div class="hidden md:flex items-center gap-1 shrink-0">
                <template v-if="auth.isAuthenticated">
                    <router-link to="/favourites" aria-label="Favourites" class="p-2 rounded-lg transition-colors" :class="isActive('/favourites') ? 'text-primary' : 'text-on-surface-variant hover:text-primary'">
                        <span class="material-symbols-outlined text-[22px]" aria-hidden="true">favorite</span>
                    </router-link>
                    <router-link v-if="auth.isAdmin" to="/admin" class="ml-1 border border-primary/40 text-primary px-3.5 py-1.5 rounded-full font-bold uppercase tracking-widest text-[0.6rem] bg-primary/5 hover:bg-primary/15 transition-colors">
                        Admin
                    </router-link>
                    <div class="relative ml-1" ref="menuRef">
                        <button @click="menuOpen = !menuOpen" aria-label="Account menu" :aria-expanded="menuOpen" class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-full hover:bg-surface-container transition-colors cursor-pointer">
                            <span class="w-8 h-8 rounded-full blue-gradient flex items-center justify-center text-xs font-black uppercase">{{ initial }}</span>
                            <span class="material-symbols-outlined text-[18px] text-on-surface-variant" aria-hidden="true">{{ menuOpen ? 'expand_less' : 'expand_more' }}</span>
                        </button>
                        <transition name="menu">
                            <div v-if="menuOpen" class="absolute right-0 mt-2 w-56 bg-surface-container border border-outline-variant rounded-xl shadow-[0_12px_40px_rgba(0,0,0,0.6)] overflow-hidden z-50">
                                <div class="px-4 py-3 border-b border-outline-variant">
                                    <p class="text-sm font-bold text-on-surface truncate">{{ auth.user?.name }}</p>
                                    <p class="text-xs text-on-surface-variant truncate">{{ auth.user?.email }}</p>
                                </div>
                                <router-link to="/profile" @click="menuOpen = false" class="flex items-center gap-3 px-4 py-3 text-sm text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">confirmation_number</span> My Tickets
                                </router-link>
                                <router-link v-if="auth.isAdmin" to="/admin" @click="menuOpen = false" class="flex items-center gap-3 px-4 py-3 text-sm text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">dashboard</span> Admin Panel
                                </router-link>
                                <button @click="handleLogout" class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-400 hover:bg-red-500/10 transition-colors cursor-pointer border-t border-outline-variant">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">logout</span> Logout
                                </button>
                            </div>
                        </transition>
                    </div>
                </template>
                <template v-else>
                    <router-link to="/login" class="ml-1 px-4 py-2 rounded-full text-on-surface-variant hover:text-on-surface font-bold uppercase tracking-widest text-[0.65rem] transition-colors">
                        Sign In
                    </router-link>
                    <router-link to="/register" class="blue-gradient px-5 py-2 rounded-full font-bold uppercase tracking-widest text-[0.65rem] hover:brightness-110 active:scale-95 transition-all">
                        Join
                    </router-link>
                </template>
            </div>

            <!-- Mobile controls -->
            <div class="flex md:hidden items-center gap-0.5">
                <button @click="openMobileSearch" aria-label="Search" class="p-2 text-on-surface-variant hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[24px]" aria-hidden="true">search</span>
                </button>
                <router-link v-if="auth.isAuthenticated" to="/favourites" aria-label="Favourites" class="p-2 text-on-surface-variant hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">favorite</span>
                </router-link>
                <button @click="mobileMenu = !mobileMenu" :aria-label="mobileMenu ? 'Close menu' : 'Open menu'" :aria-expanded="mobileMenu" class="p-2 text-on-surface hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[26px]" aria-hidden="true">{{ mobileMenu ? 'close' : 'menu' }}</span>
                </button>
            </div>
        </div>

        <!-- Mobile menu drawer -->
        <transition name="slide">
            <div v-if="mobileMenu" class="md:hidden border-t border-outline-variant bg-surface-container/95 backdrop-blur-xl px-4 py-4 space-y-1">
                <template v-if="auth.isAuthenticated">
                    <router-link to="/favourites" @click="mobileMenu = false" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold uppercase tracking-widest text-on-surface-variant hover:bg-surface-container-high transition-colors">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">favorite</span> Favourites
                    </router-link>
                    <router-link to="/profile" @click="mobileMenu = false" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold uppercase tracking-widest text-on-surface-variant hover:bg-surface-container-high transition-colors">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">confirmation_number</span> My Tickets
                    </router-link>
                    <router-link v-if="auth.isAdmin" to="/admin" @click="mobileMenu = false" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold uppercase tracking-widest text-primary bg-primary/5">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">dashboard</span> Admin Panel
                    </router-link>
                    <button @click="handleLogout" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold uppercase tracking-widest text-red-400 hover:bg-red-500/10 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">logout</span> Logout
                    </button>
                </template>
                <template v-else>
                    <div class="flex gap-3 pt-2">
                        <router-link to="/login" @click="mobileMenu = false" class="flex-1 text-center px-4 py-3 rounded-full border border-outline-variant text-on-surface font-bold uppercase tracking-widest text-xs">Sign In</router-link>
                        <router-link to="/register" @click="mobileMenu = false" class="flex-1 text-center blue-gradient px-4 py-3 rounded-full font-bold uppercase tracking-widest text-xs">Join</router-link>
                    </div>
                </template>
            </div>
        </transition>

        <!-- Mobile full search overlay -->
        <transition name="fade">
            <div v-if="mobileSearch" class="md:hidden fixed inset-0 z-[60] bg-surface/98 backdrop-blur-xl flex flex-col">
                <div class="flex items-center gap-2 px-4 h-16 border-b border-outline-variant">
                    <div class="relative flex items-center flex-1 bg-surface-container-low px-4 py-3 rounded-xl border border-outline-variant focus-within:border-primary/60">
                        <span class="material-symbols-outlined text-on-surface-variant text-[20px] mr-2" aria-hidden="true">search</span>
                        <input
                            ref="mobileInput"
                            v-model="searchQuery"
                            @keyup.enter="doSearch"
                            @input="onInput"
                            type="text"
                            aria-label="Search films"
                            placeholder="Search films, genres..."
                            class="bg-transparent outline-none text-base text-on-surface w-full placeholder:text-on-surface-variant/60"
                        />
                        <button v-if="searchQuery" @click="clearSearch" aria-label="Clear search" class="text-on-surface-variant ml-2">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">close</span>
                        </button>
                    </div>
                    <button @click="mobileSearch = false" aria-label="Close search" class="px-2 py-2 text-sm font-bold uppercase tracking-widest text-on-surface-variant hover:text-primary">Cancel</button>
                </div>
                <div class="flex-1 overflow-y-auto px-4 py-2">
                    <router-link
                        v-for="film in suggestions"
                        :key="film.id"
                        :to="`/Film/${film.id}`"
                        @click="onPick"
                        class="flex items-center gap-3 px-2 py-3 border-b border-outline-variant/60 hover:bg-surface-container-high rounded-lg"
                    >
                        <img :src="getImageUrl(film.image)" :alt="film.title" class="w-10 aspect-2/3 object-cover rounded border border-outline-variant" />
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-on-surface truncate">{{ film.title }}</p>
                            <p class="text-[0.6rem] text-primary font-bold tracking-widest uppercase mt-0.5">{{ film.duration }} MIN</p>
                        </div>
                    </router-link>
                    <p v-if="searchQuery && !suggestions.length" class="text-center text-sm text-on-surface-variant py-10">No films match "{{ searchQuery }}".</p>
                </div>
            </div>
        </transition>
    </nav>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '../store/auth';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const searchQuery = ref('');
const suggestions = ref([]);
const menuOpen = ref(false);
const mobileMenu = ref(false);
const mobileSearch = ref(false);
const menuRef = ref(null);
const mobileInput = ref(null);
const scrolled = ref(false);
let debounceTimer = null;

const initial = computed(() => (auth.user?.name?.charAt(0) || 'U').toUpperCase());
const isActive = (path) => (path === '/' ? route.path === '/' : route.path.startsWith(path));

const getImageUrl = (image) => {
    if (!image || !image.path) return 'https://via.placeholder.com/300x450?text=No+Image';
    if (image.path.startsWith('http')) return image.path;
    return `/storage/${image.path}`;
};

const clearSearch = () => { searchQuery.value = ''; suggestions.value = []; };

const openMobileSearch = () => {
    mobileMenu.value = false;
    mobileSearch.value = true;
    nextTick(() => mobileInput.value?.focus());
};

const doSearch = () => {
    if (!searchQuery.value.trim()) return;
    suggestions.value = [];
    mobileSearch.value = false;
    router.push({ path: '/', query: { search: searchQuery.value.trim() } });
    searchQuery.value = '';
};

const onPick = () => { suggestions.value = []; searchQuery.value = ''; mobileSearch.value = false; };

const onInput = () => {
    clearTimeout(debounceTimer);
    if (!searchQuery.value.trim()) { suggestions.value = []; return; }
    debounceTimer = setTimeout(async () => {
        try {
            const res = await axios.get(`/api/v1/films/search?film=${encodeURIComponent(searchQuery.value)}`);
            suggestions.value = (res.data.data || []).slice(0, 6);
        } catch { suggestions.value = []; }
    }, 300);
};

const handleLogout = async () => {
    try {
        await axios.post('/api/v1/logout', {}, { headers: { Authorization: `Bearer ${auth.token}` } });
    } catch { /* ignore */ }
    auth.logout();
    menuOpen.value = false;
    mobileMenu.value = false;
    router.push('/login');
};

watch(() => route.path, () => { menuOpen.value = false; mobileMenu.value = false; mobileSearch.value = false; });

const onClickOutside = (e) => {
    if (menuRef.value && !menuRef.value.contains(e.target)) menuOpen.value = false;
};
const onScroll = () => {
    scrolled.value = (window.scrollY || document.documentElement.scrollTop || document.body.scrollTop || 0) > 10;
};
onMounted(() => {
    document.addEventListener('click', onClickOutside);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
});
onUnmounted(() => {
    document.removeEventListener('click', onClickOutside);
    window.removeEventListener('scroll', onScroll);
});
</script>

<style scoped>
.menu-enter-active, .menu-leave-active { transition: opacity .15s ease, transform .15s ease; }
.menu-enter-from, .menu-leave-to { opacity: 0; transform: translateY(-6px); }
.slide-enter-active, .slide-leave-active { transition: opacity .2s ease, transform .2s ease; }
.slide-enter-from, .slide-leave-to { opacity: 0; transform: translateY(-8px); }
.fade-enter-active, .fade-leave-active { transition: opacity .2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
