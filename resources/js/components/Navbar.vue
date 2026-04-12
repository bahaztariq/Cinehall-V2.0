<template>
    <nav class="sticky top-0 z-50 bg-black/60 backdrop-blur-xl border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4">
                <!-- Logo -->
                <router-link to="/" class="flex items-center gap-2 shrink-0">
                    <span class="text-2xl font-black tracking-tighter text-white uppercase italic">
                        Cine<span class="text-yellow-600">hall</span>
                    </span>
                </router-link>

                <!-- Search Bar -->
                <div class="flex-1 max-w-md relative hidden md:block">
                    <input
                        v-model="searchQuery"
                        @keyup.enter="doSearch"
                        @input="onInput"
                        type="text"
                        placeholder="Search films..."
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 pr-10 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-yellow-600/60 transition-all"
                    />
                    <button @click="doSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-yellow-600 transition-colors">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/></svg>
                    </button>
                    <!-- Suggestions -->
                    <div v-if="suggestions.length" class="absolute top-full left-0 right-0 mt-2 bg-[#111] border border-white/10 rounded-xl overflow-hidden shadow-2xl z-50">
                        <router-link
                            v-for="film in suggestions"
                            :key="film.id"
                            :to="`/Film/${film.id}`"
                            @click="suggestions = []; searchQuery = ''"
                            class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors"
                        >
                            <img :src="film.image?.path || 'https://via.placeholder.com/40x60'" class="w-8 h-12 object-cover rounded" />
                            <div>
                                <p class="text-sm font-bold text-white">{{ film.title }}</p>
                                <p class="text-xs text-gray-500">{{ film.duration }} min</p>
                            </div>
                        </router-link>
                    </div>
                </div>

                <!-- Auth Actions -->
                <div class="flex items-center gap-3 shrink-0">
                    <template v-if="auth.isAuthenticated">
                        <router-link v-if="auth.isAdmin" to="/admin" class="text-yellow-600 hover:text-yellow-500 transition-colors font-black uppercase tracking-widest text-xs border border-yellow-600/30 px-3 py-1.5 rounded-lg bg-yellow-600/10 hidden sm:block">
                            Admin
                        </router-link>
                        <router-link to="/favourites" class="text-gray-300 hover:text-red-400 transition-colors" title="My Favourites">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                        </router-link>
                        <router-link to="/profile" class="text-gray-300 hover:text-white transition-colors font-medium text-sm">
                            Profile
                        </router-link>
                        <button @click="handleLogout" class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-lg transition-all border border-white/10 text-sm">
                            Logout
                        </button>
                    </template>
                    <template v-else>
                        <router-link to="/login" class="text-gray-300 hover:text-white transition-colors font-medium text-sm">
                            Login
                        </router-link>
                        <router-link to="/register" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition-all shadow-lg shadow-yellow-600/20 font-bold text-sm">
                            Register
                        </router-link>
                    </template>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../store/auth';
import { useRouter } from 'vue-router';
import axios from 'axios';

const auth = useAuthStore();
const router = useRouter();

const searchQuery = ref('');
const suggestions = ref([]);
let debounceTimer = null;

const doSearch = () => {
    if (!searchQuery.value.trim()) return;
    suggestions.value = [];
    router.push({ path: '/', query: { search: searchQuery.value.trim() } });
    searchQuery.value = '';
};

const onInput = () => {
    clearTimeout(debounceTimer);
    if (!searchQuery.value.trim()) {
        suggestions.value = [];
        return;
    }
    debounceTimer = setTimeout(async () => {
        try {
            const res = await axios.get(`/api/v1/films/search?film=${encodeURIComponent(searchQuery.value)}`);
            suggestions.value = (res.data.data || []).slice(0, 5);
        } catch {
            suggestions.value = [];
        }
    }, 300);
};

const handleLogout = async () => {
    try {
        await axios.post('/api/v1/logout', {}, {
            headers: { Authorization: `Bearer ${auth.token}` }
        });
    } catch { /* ignore */ }
    auth.logout();
    router.push('/login');
};
</script>
