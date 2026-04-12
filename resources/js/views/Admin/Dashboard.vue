<template>
    <div>
        <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-yellow-600"></div>
        </div>

        <div v-else-if="stats" class="space-y-12 pb-20">
            <h1 class="text-5xl font-black uppercase italic tracking-tighter">Admin <span class="text-yellow-600">Dashboard</span></h1>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white/5 border border-white/10 p-6 rounded-2xl">
                    <p class="text-gray-500 uppercase tracking-widest font-bold text-xs mb-2">Total Users</p>
                    <p class="text-4xl font-black italic">{{ stats.counts.total_users }}</p>
                </div>
                <div class="bg-white/5 border border-white/10 p-6 rounded-2xl">
                    <p class="text-gray-500 uppercase tracking-widest font-bold text-xs mb-2">Total Reservations</p>
                    <p class="text-4xl font-black italic">{{ stats.counts.total_reservations }}</p>
                </div>
                <div class="bg-white/5 border border-white/10 p-6 rounded-2xl">
                    <p class="text-gray-500 uppercase tracking-widest font-bold text-xs mb-2">Pending</p>
                    <p class="text-4xl font-black text-yellow-600 italic">{{ stats.counts.pending_reservations }}</p>
                </div>
                <div class="bg-white/5 border border-white/10 p-6 rounded-2xl">
                    <p class="text-gray-500 uppercase tracking-widest font-bold text-xs mb-2">Canceled</p>
                    <p class="text-4xl font-black text-red-500 italic">{{ stats.counts.canceled_reservations }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Top Films -->
                <section class="space-y-6">
                    <h2 class="text-2xl font-black uppercase italic tracking-tighter">Top <span class="text-yellow-600">Films</span> (This Month)</h2>
                    <div class="space-y-4">
                        <div v-for="ranking in stats.rankings.this_month" :key="ranking.id" class="bg-white/5 border border-white/10 p-4 rounded-xl flex justify-between items-center">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-14 rounded overflow-hidden border border-white/10">
                                    <img :src="ranking.film?.image?.path || 'https://via.placeholder.com/50x70?text=Film'" class="w-full h-full object-cover" />
                                </div>
                                <span class="font-bold">{{ ranking.film?.title }}</span>
                            </div>
                            <span class="text-yellow-600 font-black italic">{{ ranking.reservations_count }} Bookings</span>
                        </div>
                    </div>
                </section>

                <!-- Quick Links -->
                <section class="space-y-6">
                    <h2 class="text-2xl font-black uppercase italic tracking-tighter">Quick <span class="text-yellow-600">Management</span></h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <router-link to="/admin/users" class="p-6 bg-white/5 border border-white/10 rounded-2xl hover:border-yellow-600/50 transition-all flex flex-col items-center gap-2 group">
                            <span class="text-2xl group-hover:scale-110 transition-transform">👥</span>
                            <span class="font-bold uppercase tracking-widest text-xs">Users</span>
                        </router-link>
                        <router-link to="/admin/films" class="p-6 bg-white/5 border border-white/10 rounded-2xl hover:border-yellow-600/50 transition-all flex flex-col items-center gap-2 group">
                            <span class="text-2xl group-hover:scale-110 transition-transform">🎬</span>
                            <span class="font-bold uppercase tracking-widest text-xs">Films</span>
                        </router-link>
                        <router-link to="/admin/sessions" class="p-6 bg-white/5 border border-white/10 rounded-2xl hover:border-yellow-600/50 transition-all flex flex-col items-center gap-2 group">
                            <span class="text-2xl group-hover:scale-110 transition-transform">📅</span>
                            <span class="font-bold uppercase tracking-widest text-xs">Sessions</span>
                        </router-link>
                        <router-link to="/admin/rooms" class="p-6 bg-white/5 border border-white/10 rounded-2xl hover:border-yellow-600/50 transition-all flex flex-col items-center gap-2 group">
                            <span class="text-2xl group-hover:scale-110 transition-transform">🏛️</span>
                            <span class="font-bold uppercase tracking-widest text-xs">Rooms</span>
                        </router-link>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../store/auth';

const auth = useAuthStore();

const stats = ref(null);
const loading = ref(true);

const fetchStats = async () => {
    try {
        const response = await axios.get('/api/v1/statistics', {
            headers: { Authorization: `Bearer ${auth.token}` }
        });
        stats.value = response.data;
    } catch (err) {
        console.error('Failed to fetch statistics:', err);
        // Handle unauthorized or other errors
    } finally {
        loading.value = false;
    }
};

onMounted(fetchStats);
</script>
