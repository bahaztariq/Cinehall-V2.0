<template>
    <div class="min-h-dvh bg-surface text-on-surface flex">
        <!-- Sidebar (desktop) -->
        <aside class="hidden lg:flex flex-col w-64 shrink-0 border-r border-outline-variant bg-surface-container-lowest fixed inset-y-0 left-0 z-30">
            <div class="h-16 flex items-center gap-2.5 px-6 border-b border-outline-variant">
                <img src="/assets/logo_icon.png" class="h-8 w-8 object-contain" alt="" />
                <span class="font-serif text-lg font-bold">Cine<span class="text-primary">hall</span></span>
                <span class="ml-auto text-[0.55rem] font-black uppercase tracking-widest text-primary border border-primary/40 rounded px-1.5 py-0.5">Admin</span>
            </div>
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <router-link
                    v-for="item in nav"
                    :key="item.to"
                    :to="item.to"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-colors"
                    :class="isActive(item) ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                >
                    <span class="material-symbols-outlined text-[20px]" :class="{ 'icon-filled': isActive(item) }" aria-hidden="true">{{ item.icon }}</span>
                    {{ item.label }}
                </router-link>
            </nav>
            <div class="p-3 border-t border-outline-variant space-y-1">
                <router-link to="/" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">public</span> View Site
                </router-link>
                <button @click="handleLogout" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-red-400 hover:bg-red-500/10 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">logout</span> Logout
                </button>
            </div>
        </aside>

        <!-- Mobile drawer -->
        <transition name="fade">
            <div v-if="drawer" class="lg:hidden fixed inset-0 z-40 bg-black/60 backdrop-blur-sm" @click="drawer = false"></div>
        </transition>
        <transition name="drawer">
            <aside v-if="drawer" class="lg:hidden fixed inset-y-0 left-0 z-50 w-72 flex flex-col border-r border-outline-variant bg-surface-container-lowest">
                <div class="h-16 flex items-center gap-2.5 px-6 border-b border-outline-variant">
                    <span class="font-serif text-lg font-bold">Cine<span class="text-primary">hall</span></span>
                    <span class="text-[0.55rem] font-black uppercase tracking-widest text-primary border border-primary/40 rounded px-1.5 py-0.5">Admin</span>
                    <button @click="drawer = false" aria-label="Close menu" class="ml-auto text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined" aria-hidden="true">close</span>
                    </button>
                </div>
                <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                    <router-link
                        v-for="item in nav"
                        :key="item.to"
                        :to="item.to"
                        @click="drawer = false"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-colors"
                        :class="isActive(item) ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container'"
                    >
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">{{ item.icon }}</span>
                        {{ item.label }}
                    </router-link>
                </nav>
                <div class="p-3 border-t border-outline-variant space-y-1">
                    <router-link to="/" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-on-surface-variant hover:bg-surface-container transition-colors">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">public</span> View Site
                    </router-link>
                    <button @click="handleLogout" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-red-400 hover:bg-red-500/10 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">logout</span> Logout
                    </button>
                </div>
            </aside>
        </transition>

        <!-- Main -->
        <div class="flex-1 lg:ml-64 min-w-0 flex flex-col">
            <!-- Topbar -->
            <header class="h-16 shrink-0 sticky top-0 z-20 bg-surface/80 backdrop-blur-xl border-b border-outline-variant flex items-center gap-3 px-5 sm:px-8">
                <button @click="drawer = true" aria-label="Open menu" class="lg:hidden p-2 -ml-2 text-on-surface hover:text-primary">
                    <span class="material-symbols-outlined text-[26px]" aria-hidden="true">menu</span>
                </button>
                <h1 class="font-serif text-lg sm:text-xl font-bold">{{ currentLabel }}</h1>
                <div class="ml-auto flex items-center gap-2">
                    <span class="text-xs text-on-surface-variant hidden sm:inline">{{ auth.user?.name }}</span>
                    <span class="w-8 h-8 rounded-full gold-gradient flex items-center justify-center text-xs font-black uppercase">{{ initial }}</span>
                </div>
            </header>

            <main class="flex-1 p-5 sm:p-8">
                <router-view />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../store/auth';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const drawer = ref(false);

const nav = [
    { to: '/admin', label: 'Dashboard', icon: 'dashboard', exact: true },
    { to: '/admin/films', label: 'Films', icon: 'movie' },
    { to: '/admin/sessions', label: 'Sessions', icon: 'event' },
    { to: '/admin/rooms', label: 'Rooms', icon: 'meeting_room' },
    { to: '/admin/genres', label: 'Genres', icon: 'sell' },
    { to: '/admin/bookings', label: 'Bookings', icon: 'event_seat' },
    { to: '/admin/payments', label: 'Payments', icon: 'payments' },
    { to: '/admin/promotions', label: 'Promotions', icon: 'local_offer' },
    { to: '/admin/users', label: 'Users', icon: 'group' },
    { to: '/admin/settings', label: 'Settings', icon: 'settings' },
];

const initial = computed(() => (auth.user?.name?.charAt(0) || 'A').toUpperCase());
const isActive = (item) => (item.exact ? route.path === item.to : route.path === item.to || route.path.startsWith(item.to + '/'));
const currentLabel = computed(() => nav.find(n => isActive(n))?.label || 'Admin');

const handleLogout = async () => {
    try { await axios.post('/api/v1/logout', {}, { headers: { Authorization: `Bearer ${auth.token}` } }); } catch { /* ignore */ }
    auth.logout();
    router.push('/login');
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.drawer-enter-active, .drawer-leave-active { transition: transform .25s ease; }
.drawer-enter-from, .drawer-leave-to { transform: translateX(-100%); }
.icon-filled { font-variation-settings: 'FILL' 1; }
</style>
