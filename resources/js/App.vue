<template>
    <div class="min-h-screen bg-[#0c0c0c] text-white font-sans selection:bg-yellow-500/30">
        <Navbar />
        
        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-12 px-5">
            <router-view v-slot="{ Component }">
                <transition 
                    enter-active-class="transform transition duration-500 ease-out"
                    enter-from-class="opacity-0 translate-y-4"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transform transition duration-300 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-4"
                    mode="out-in"
                >
                    <component :is="Component" />
                </transition>
            </router-view>
        </main>

        <Footer />
    </div>
</template>

<script setup>
import Navbar from './components/Navbar.vue';
import Footer from './components/Footer.vue';
</script>