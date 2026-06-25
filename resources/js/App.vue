<template>
    <!-- Admin routes render their own full-screen layout (sidebar) -->
    <router-view v-if="isAdmin" />

    <!-- Public site shell -->
    <div v-else class="min-h-screen bg-surface text-on-surface font-sans selection:bg-primary selection:text-on-primary">
        <Navbar />

        <!-- Main Content offset for fixed Navbar; home renders a full-screen hero behind the nav -->
        <main :class="[isHome ? '' : 'pt-16 md:pt-[4.5rem]', 'min-h-[calc(100vh-4.5rem)]']">
            <router-view v-slot="{ Component }">
                <transition name="fade" mode="out-in">
                    <component :is="Component" />
                </transition>
            </router-view>
        </main>

        <Footer />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import Navbar from './components/Navbar.vue';
import Footer from './components/Footer.vue';

const route = useRoute();
const isAdmin = computed(() => route.matched.some(r => r.meta?.admin));
const isHome = computed(() => route.path === '/');
</script>
