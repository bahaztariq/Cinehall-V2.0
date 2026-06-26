<template>
    <div class="space-y-8 pb-16">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="space-y-1">
                <p class="text-[0.7rem] font-bold text-primary uppercase tracking-[0.2em]">Reservations</p>
                <h1 class="font-serif text-4xl font-bold tracking-tight">Booking <span class="text-primary">Management</span></h1>
            </div>
            <!-- Search -->
            <div class="relative w-full md:w-72">
                <label for="booking-search" class="sr-only">Search by user name or email</label>
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-on-surface-variant" aria-hidden="true">search</span>
                <input
                    id="booking-search"
                    v-model="search"
                    type="search"
                    placeholder="Search by user name or email..."
                    class="w-full glass-input rounded-xl pl-11 pr-4 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/60"
                />
            </div>
        </div>

        <!-- Filter chips -->
        <div class="flex flex-wrap gap-2">
            <button
                v-for="chip in filters"
                :key="chip.value"
                @click="setStatus(chip.value)"
                :class="status === chip.value ? 'bg-primary/10 text-primary border-primary/40' : 'bg-surface-container-low border-outline-variant text-on-surface-variant hover:border-primary/30 hover:text-on-surface'"
                class="px-4 py-2 rounded-full border text-[0.7rem] font-bold uppercase tracking-widest transition-all cursor-pointer min-h-[44px]"
            >{{ chip.label }}</button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
        </div>

        <!-- Empty -->
        <div v-else-if="!reservations.length" class="flex flex-col items-center justify-center text-center py-20 bg-surface-container border border-dashed border-outline-variant rounded-2xl">
            <div class="w-14 h-14 rounded-2xl bg-surface-container-high flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-[28px] text-on-surface-variant" aria-hidden="true">event_seat</span>
            </div>
            <h3 class="font-serif text-xl mb-1">No bookings found</h3>
            <p class="text-on-surface-variant text-sm">Try a different filter or search term.</p>
        </div>

        <!-- Table -->
        <div v-else class="bg-surface-container border border-outline-variant rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[860px]">
                    <thead class="bg-surface-container-high">
                        <tr class="text-on-surface-variant uppercase tracking-[0.18em] text-[0.7rem] font-bold">
                            <th scope="col" class="px-6 py-4">Film</th>
                            <th scope="col" class="px-6 py-4">User</th>
                            <th scope="col" class="px-6 py-4">Seat</th>
                            <th scope="col" class="px-6 py-4">Room</th>
                            <th scope="col" class="px-6 py-4">Date / Time</th>
                            <th scope="col" class="px-6 py-4">Price</th>
                            <th scope="col" class="px-6 py-4">Status</th>
                            <th scope="col" class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="r in reservations" :key="r.id" class="border-t border-outline-variant hover:bg-surface-container-high transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img :src="getImageUrl(r.session?.film?.image)" :alt="r.session?.film?.title" class="w-10 h-14 object-cover rounded-lg border border-outline-variant shrink-0" />
                                    <span class="font-semibold text-on-surface truncate max-w-[180px]">{{ r.session?.film?.title || '—' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col min-w-0">
                                    <span class="font-semibold text-on-surface truncate">{{ r.user?.name || '—' }}</span>
                                    <span class="text-xs text-on-surface-variant truncate">{{ r.user?.email }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant tabular-nums">{{ r.seat?.seat_number ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <span class="text-on-surface">{{ r.session?.room?.name || '—' }}</span>
                                <span v-if="r.session?.room?.type" class="block text-xs text-on-surface-variant uppercase tracking-wider">{{ r.session.room.type }}</span>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant whitespace-nowrap">{{ formatDateTime(r.session?.start_time) }}</td>
                            <td class="px-6 py-4 font-bold text-primary tabular-nums whitespace-nowrap">{{ formatPrice(r.session?.price) }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[0.7rem] font-bold uppercase tracking-wider border" :class="statusMap[r.status]?.class">
                                    {{ statusMap[r.status]?.label || r.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    v-if="r.status !== 'canceled'"
                                    @click="cancelBooking(r)"
                                    :disabled="cancelling[r.id]"
                                    :aria-label="`Cancel booking for ${r.user?.name}`"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-red-500/30 text-red-400 hover:bg-red-500/12 hover:border-red-500/50 text-[0.7rem] font-bold uppercase tracking-wider transition-colors cursor-pointer disabled:opacity-50 min-h-[44px]"
                                >
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">cancel</span>
                                    {{ cancelling[r.id] ? '...' : 'Cancel' }}
                                </button>
                                <span v-else class="text-xs text-on-surface-variant">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="!loading && reservations.length" class="flex items-center justify-between gap-4">
            <p class="text-xs text-on-surface-variant uppercase tracking-widest">Page {{ currentPage }} of {{ lastPage }} · {{ total }} total</p>
            <div class="flex gap-2">
                <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1" class="inline-flex items-center gap-1 px-4 py-2.5 rounded-xl border border-outline-variant text-xs font-bold uppercase tracking-widest text-on-surface hover:bg-surface-container-high transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed min-h-[44px]">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">chevron_left</span> Prev
                </button>
                <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= lastPage" class="inline-flex items-center gap-1 px-4 py-2.5 rounded-xl border border-outline-variant text-xs font-bold uppercase tracking-widest text-on-surface hover:bg-surface-container-high transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed min-h-[44px]">
                    Next <span class="material-symbols-outlined text-[18px]" aria-hidden="true">chevron_right</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import axios from 'axios';

const reservations = ref([]);
const loading = ref(true);
const cancelling = reactive({});

const status = ref('');
const search = ref('');
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);

const filters = [
    { label: 'All', value: '' },
    { label: 'Pending', value: 'pending' },
    { label: 'Confirmed', value: 'accepted' },
    { label: 'Cancelled', value: 'canceled' },
];

const statusMap = {
    pending: { label: 'Pending', class: 'bg-amber-500/15 text-amber-400 border-amber-500/30' },
    accepted: { label: 'Confirmed', class: 'bg-green-500/15 text-green-400 border-green-500/30' },
    canceled: { label: 'Cancelled', class: 'bg-red-500/15 text-red-400 border-red-500/30' },
};

const getImageUrl = (image) => {
    if (!image || !image.path) return 'https://via.placeholder.com/80x120?text=No+Image';
    if (image.path.startsWith('http://') || image.path.startsWith('https://')) return image.path;
    return `/storage/${image.path}`;
};

const formatDateTime = (dt) => {
    if (!dt) return '—';
    return new Date(dt).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
};

const formatPrice = (p) => {
    if (p === null || p === undefined) return '—';
    return `$${Number(p).toFixed(2)}`;
};

const fetchReservations = async () => {
    loading.value = true;
    try {
        const params = { page: currentPage.value };
        if (status.value) params.status = status.value;
        if (search.value.trim()) params.search = search.value.trim();
        const res = await axios.get('/api/v1/admin/reservations', { params });
        const paginator = res.data.reservations || {};
        reservations.value = paginator.data || [];
        currentPage.value = paginator.current_page || 1;
        lastPage.value = paginator.last_page || 1;
        total.value = paginator.total || 0;
    } catch (err) {
        console.error('Failed to fetch reservations:', err);
        reservations.value = [];
    } finally {
        loading.value = false;
    }
};

const setStatus = (value) => {
    if (status.value === value) return;
    status.value = value;
    currentPage.value = 1;
    fetchReservations();
};

const goToPage = (page) => {
    if (page < 1 || page > lastPage.value) return;
    currentPage.value = page;
    fetchReservations();
};

const cancelBooking = async (r) => {
    if (!confirm(`Cancel booking for ${r.user?.name || 'this user'}?`)) return;
    cancelling[r.id] = true;
    try {
        const res = await axios.patch(`/api/v1/admin/reservations/${r.id}/cancel`);
        const updated = res.data.reservation;
        const idx = reservations.value.findIndex(x => x.id === r.id);
        if (idx !== -1 && updated) reservations.value[idx] = updated;
        else if (idx !== -1) reservations.value[idx].status = 'canceled';
    } catch (err) {
        alert(err.response?.data?.message || 'Failed to cancel booking');
    } finally {
        cancelling[r.id] = false;
    }
};

// Debounced search
let searchTimer = null;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        currentPage.value = 1;
        fetchReservations();
    }, 400);
});

onMounted(fetchReservations);
</script>
