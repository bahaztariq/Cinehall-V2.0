<template>
    <div class="space-y-12">
        <!-- Profile Header -->
        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-8 flex flex-col md:flex-row items-center gap-8">
            <div class="w-24 h-24 rounded-full bg-yellow-600/20 border-2 border-yellow-600/40 flex items-center justify-center text-4xl font-black text-yellow-600 uppercase shrink-0">
                {{ user?.name?.charAt(0) || '?' }}
            </div>
            <div class="text-center md:text-left space-y-1">
                <h1 class="text-4xl font-black italic uppercase tracking-tighter">{{ user?.name }}</h1>
                <p class="text-gray-400 text-sm">{{ user?.email }}</p>
                <div class="flex flex-wrap gap-2 justify-center md:justify-start mt-2">
                    <span v-if="user?.is_admin" class="px-3 py-1 bg-yellow-600/10 border border-yellow-600/30 text-yellow-600 text-xs font-bold rounded-full uppercase tracking-widest">Admin</span>
                    <span :class="user?.status === 'active' ? 'bg-green-600/10 border-green-600/30 text-green-500' : 'bg-red-600/10 border-red-600/30 text-red-500'" class="px-3 py-1 border text-xs font-bold rounded-full uppercase tracking-widest">{{ user?.status }}</span>
                </div>
            </div>
            <div class="md:ml-auto grid grid-cols-2 gap-4 text-center">
                <div class="bg-black/20 rounded-xl p-4">
                    <p class="text-2xl font-black text-yellow-600">{{ reservations.length }}</p>
                    <p class="text-xs text-gray-500 uppercase tracking-widest font-bold mt-1">Reservations</p>
                </div>
                <div class="bg-black/20 rounded-xl p-4">
                    <router-link to="/favourites">
                        <p class="text-2xl font-black text-yellow-600">♥</p>
                        <p class="text-xs text-gray-500 uppercase tracking-widest font-bold mt-1">Favourites</p>
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Reservations -->
        <div class="space-y-6">
            <h2 class="text-2xl font-black uppercase italic tracking-tighter">My <span class="text-yellow-600">Reservations</span></h2>

            <div v-if="loading" class="flex justify-center py-10">
                <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-yellow-600"></div>
            </div>

            <div v-else-if="!reservations.length" class="py-16 text-center border border-white/10 border-dashed rounded-2xl">
                <p class="text-gray-500 text-xl italic">No reservations yet.</p>
                <router-link to="/" class="mt-4 inline-block px-6 py-2 bg-yellow-600 hover:bg-yellow-700 rounded-xl font-bold uppercase tracking-widest transition-all text-sm">
                    Browse Films
                </router-link>
            </div>

            <div v-else class="grid gap-4">
                <div
                    v-for="res in reservations" :key="res.id"
                    class="bg-white/5 border rounded-2xl p-6 flex flex-col sm:flex-row justify-between items-center gap-6 transition-all hover:border-white/20"
                    :class="res.status === 'cancelled' ? 'border-red-600/20 opacity-60' : 'border-white/10'"
                >
                    <div class="flex items-center gap-5">
                        <img
                            :src="res.session?.film?.image?.path || 'https://via.placeholder.com/80x120'"
                            class="w-14 h-20 object-cover rounded-lg border border-white/10 shrink-0"
                        />
                        <div class="space-y-1">
                            <h3 class="text-lg font-bold text-white">{{ res.session?.film?.title }}</h3>
                            <p class="text-sm text-gray-400">{{ formatDateTime(res.session?.start_time) }}</p>
                            <p class="text-sm text-gray-400">Room: {{ res.session?.room?.name }} • Seat: {{ res.seat?.seat_number }}</p>
                            <span
                                class="inline-block px-3 py-0.5 rounded-full text-xs font-bold uppercase tracking-widest"
                                :class="{
                                    'bg-green-600/10 text-green-500 border border-green-600/20': res.status === 'active',
                                    'bg-yellow-600/10 text-yellow-600 border border-yellow-600/20': res.status === 'pending',
                                    'bg-red-600/10 text-red-500 border border-red-600/20': res.status === 'cancelled',
                                }"
                            >{{ res.status }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button
                            v-if="res.status !== 'cancelled'"
                            @click="cancelReservation(res)"
                            :disabled="cancelling === res.id"
                            class="px-4 py-2 rounded-lg border border-red-600/30 hover:bg-red-600 text-red-400 hover:text-white font-bold text-xs uppercase tracking-widest transition-all disabled:opacity-50"
                        >{{ cancelling === res.id ? 'Cancelling...' : 'Cancel' }}</button>
                    </div>
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
const user = ref(null);
const reservations = ref([]);
const loading = ref(true);
const cancelling = ref(null);

const formatDateTime = (d) => d ? new Date(d).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' }) : '';

const fetchProfile = async () => {
    try {
        const [profileRes, resRes] = await Promise.all([
            axios.get('/api/v1/profile', { headers: { Authorization: `Bearer ${auth.token}` } }),
            axios.get('/api/v1/reservation', { headers: { Authorization: `Bearer ${auth.token}` } }),
        ]);
        user.value = profileRes.data.user || profileRes.data;
        reservations.value = resRes.data.reservations || [];
    } catch (err) {
        console.error('Failed to load profile:', err);
    } finally {
        loading.value = false;
    }
};

const cancelReservation = async (res) => {
    if (!confirm(`Cancel reservation for "${res.session?.film?.title}"?`)) return;
    cancelling.value = res.id;
    try {
        await axios.delete(`/api/v1/reservation/${res.id}`, {
            headers: { Authorization: `Bearer ${auth.token}` }
        });
        const idx = reservations.value.findIndex(r => r.id === res.id);
        if (idx !== -1) reservations.value[idx].status = 'cancelled';
    } catch (err) {
        alert(err.response?.data?.message || 'Cancellation failed');
    } finally {
        cancelling.value = null;
    }
};

onMounted(fetchProfile);
</script>
