<template>
    <div class="admin-dashboard min-h-screen bg-surface text-on-surface">
        <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
        </div>

        <div v-else-if="stats" class="max-w-7xl mx-auto px-6 py-10 space-y-12 pb-20">
            <!-- Page header -->
            <header class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-on-surface-variant">Control Center</p>
                <h1 class="font-serif text-4xl md:text-5xl text-on-surface">
                    Admin <span class="text-primary">Dashboard</span>
                </h1>
                <p class="text-sm text-on-surface-variant max-w-xl">
                    A live overview of your cinema — audience, bookings and what's trending on screen this month.
                </p>
            </header>

            <!-- Stat cards -->
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <article class="stat-card bg-surface-container border border-outline-variant rounded-2xl p-6">
                    <div class="flex items-start justify-between mb-5">
                        <span class="stat-icon bg-primary/10 text-primary" aria-hidden="true">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m6-1.13a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm6 0a3 3 0 1 0-2.4-1.2M5 13a3 3 0 1 0 2.4-1.2"/></svg>
                        </span>
                    </div>
                    <p class="text-4xl font-bold tracking-tight text-on-surface tabular-nums">{{ stats.counts.total_users }}</p>
                    <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] text-on-surface-variant">Total Users</p>
                    <p class="mt-1 text-xs text-on-surface-variant/70">Registered audience members</p>
                </article>

                <article class="stat-card bg-surface-container border border-outline-variant rounded-2xl p-6">
                    <div class="flex items-start justify-between mb-5">
                        <span class="stat-icon bg-primary/10 text-primary" aria-hidden="true">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2M9 6V4m6 2V4M8 11h.01M8 15h.01M12 11h.01M12 15h.01M16 11h.01"/></svg>
                        </span>
                    </div>
                    <p class="text-4xl font-bold tracking-tight text-on-surface tabular-nums">{{ stats.counts.total_reservations }}</p>
                    <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] text-on-surface-variant">Total Reservations</p>
                    <p class="mt-1 text-xs text-on-surface-variant/70">All-time tickets booked</p>
                </article>

                <article class="stat-card bg-surface-container border border-outline-variant rounded-2xl p-6">
                    <div class="flex items-start justify-between mb-5">
                        <span class="stat-icon bg-amber-500/10 text-amber-400" aria-hidden="true">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2.5 2.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        </span>
                    </div>
                    <p class="text-4xl font-bold tracking-tight text-primary tabular-nums">{{ stats.counts.pending_reservations }}</p>
                    <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] text-on-surface-variant">Pending</p>
                    <p class="mt-1 text-xs text-on-surface-variant/70">Awaiting confirmation</p>
                </article>

                <article class="stat-card bg-surface-container border border-outline-variant rounded-2xl p-6">
                    <div class="flex items-start justify-between mb-5">
                        <span class="stat-icon bg-red-500/10 text-red-400" aria-hidden="true">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14 14 10M14 14 10 10M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        </span>
                    </div>
                    <p class="text-4xl font-bold tracking-tight text-red-400 tabular-nums">{{ stats.counts.canceled_reservations }}</p>
                    <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] text-on-surface-variant">Canceled</p>
                    <p class="mt-1 text-xs text-on-surface-variant/70">Refunded or released</p>
                </article>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                <!-- Top Films leaderboard -->
                <section class="lg:col-span-3 bg-surface-container border border-outline-variant rounded-2xl overflow-hidden">
                    <header class="bg-surface-container-high px-6 py-4 border-b border-outline-variant flex items-center justify-between">
                        <div>
                            <h2 class="font-serif text-xl text-on-surface">Top <span class="text-primary">Films</span></h2>
                            <p class="text-xs uppercase tracking-[0.18em] text-on-surface-variant mt-0.5">This Month's Leaderboard</p>
                        </div>
                        <span class="stat-icon bg-primary/10 text-primary" aria-hidden="true">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V3H8v8m8 0a4 4 0 0 1-8 0m8 0h2.5a2.5 2.5 0 0 0 0-5H16m-8 5H5.5a2.5 2.5 0 0 1 0-5H8m4 9v3m-4 0h8"/></svg>
                        </span>
                    </header>

                    <ol class="divide-y divide-outline-variant">
                        <li
                            v-for="(ranking, index) in stats.rankings.this_month"
                            :key="ranking.id"
                            class="leader-row flex items-center gap-4 px-6 py-4"
                        >
                            <span
                                class="rank-badge"
                                :class="[
                                    index === 0 ? 'rank-blue' : '',
                                    index === 1 ? 'rank-silver' : '',
                                    index === 2 ? 'rank-bronze' : '',
                                    index > 2 ? 'rank-plain' : ''
                                ]"
                                aria-hidden="true"
                            >{{ index + 1 }}</span>
                            <div class="w-11 h-16 rounded-lg overflow-hidden border border-outline-variant shrink-0 bg-surface-container-high">
                                <img :src="ranking.film?.image?.path || 'https://via.placeholder.com/50x70?text=Film'" class="w-full h-full object-cover" alt="" />
                            </div>
                            <span class="font-semibold text-on-surface truncate flex-1">{{ ranking.film?.title }}</span>
                            <span class="booking-pill">
                                <span class="text-primary font-bold tabular-nums">{{ ranking.reservations_count }}</span>
                                <span class="text-on-surface-variant text-xs uppercase tracking-wider ml-1.5">Bookings</span>
                            </span>
                        </li>
                    </ol>
                </section>

                <!-- Quick Management -->
                <section class="lg:col-span-2 bg-surface-container border border-outline-variant rounded-2xl overflow-hidden">
                    <header class="bg-surface-container-high px-6 py-4 border-b border-outline-variant">
                        <h2 class="font-serif text-xl text-on-surface">Quick <span class="text-primary">Management</span></h2>
                        <p class="text-xs uppercase tracking-[0.18em] text-on-surface-variant mt-0.5">Jump to a section</p>
                    </header>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
                        <router-link to="/admin/users" class="nav-card group">
                            <span class="nav-icon" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m6-1.13a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm6 0a3 3 0 1 0-2.4-1.2M5 13a3 3 0 1 0 2.4-1.2"/></svg>
                            </span>
                            <span class="nav-label">Users</span>
                            <svg class="nav-arrow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                        </router-link>

                        <router-link to="/admin/films" class="nav-card group">
                            <span class="nav-icon" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 4v16M17 4v16M3 8h4M3 16h4M17 8h4M17 16h4M3 4h18a0 0 0 0 1 0 0v16a0 0 0 0 1 0 0H3a0 0 0 0 1 0 0V4a0 0 0 0 1 0 0Z"/></svg>
                            </span>
                            <span class="nav-label">Films</span>
                            <svg class="nav-arrow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                        </router-link>

                        <router-link to="/admin/sessions" class="nav-card group">
                            <span class="nav-icon" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M3 11h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/></svg>
                            </span>
                            <span class="nav-label">Sessions</span>
                            <svg class="nav-arrow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                        </router-link>

                        <router-link to="/admin/rooms" class="nav-card group">
                            <span class="nav-icon" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4 8 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01"/></svg>
                            </span>
                            <span class="nav-label">Rooms</span>
                            <svg class="nav-arrow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
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

<style scoped>
.stat-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 0.875rem;
}

.stat-card {
    transition: border-color 0.2s ease, transform 0.2s ease;
}
.stat-card:hover {
    border-color: rgba(59, 130, 246, 0.35);
    transform: translateY(-2px);
}

/* Leaderboard */
.leader-row {
    transition: background-color 0.18s ease;
}
.leader-row:hover {
    background-color: var(--color-surface-container-high);
}

.rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 9999px;
    font-weight: 700;
    font-size: 0.85rem;
    flex-shrink: 0;
    font-variant-numeric: tabular-nums;
}
.rank-blue {
    background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
    color: var(--color-on-primary);
}
.rank-silver {
    background: linear-gradient(135deg, #e6e8f0 0%, #aab0c4 100%);
    color: #1a1830;
}
.rank-bronze {
    background: linear-gradient(135deg, #d8a273 0%, #a96f3d 100%);
    color: #1a1407;
}
.rank-plain {
    background: var(--color-surface-container-high);
    color: var(--color-on-surface-variant);
    border: 1px solid var(--color-outline-variant);
}

.booking-pill {
    display: inline-flex;
    align-items: baseline;
    padding: 0.35rem 0.85rem;
    border-radius: 9999px;
    background: var(--color-surface-container-high);
    border: 1px solid var(--color-outline-variant);
    white-space: nowrap;
    flex-shrink: 0;
}

/* Quick management nav cards */
.nav-card {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 1rem 1.125rem;
    border-radius: 1rem;
    background: var(--color-surface-container-high);
    border: 1px solid var(--color-outline-variant);
    transition: border-color 0.2s ease, transform 0.2s ease, background-color 0.2s ease;
}
.nav-card:hover {
    border-color: rgba(59, 130, 246, 0.45);
    transform: translateY(-2px);
}

.nav-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    background: rgba(59, 130, 246, 0.1);
    color: var(--color-primary);
    flex-shrink: 0;
}

.nav-label {
    flex: 1;
    font-weight: 700;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.18em;
    color: var(--color-on-surface);
}

.nav-arrow {
    width: 1.1rem;
    height: 1.1rem;
    color: var(--color-on-surface-variant);
    opacity: 0;
    transform: translateX(-4px);
    transition: opacity 0.2s ease, transform 0.2s ease, color 0.2s ease;
}
.nav-card:hover .nav-arrow {
    opacity: 1;
    transform: translateX(0);
    color: var(--color-primary);
}
</style>
