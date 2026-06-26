<template>
    <div class="profile-page bg-surface min-h-screen pb-24">
        <div class="max-w-7xl mx-auto px-5 sm:px-10 lg:px-20 space-y-10 sm:space-y-12">

            <!-- ============ PROFILE HERO ============ -->
            <header class="hero">
                <div class="hero-glow" aria-hidden="true"></div>
                <div class="hero-inner">
                    <!-- Avatar + identity -->
                    <div class="hero-identity">
                        <div class="avatar-ring shrink-0">
                            <div class="avatar">{{ userInitial }}</div>
                        </div>
                        <div class="min-w-0 text-center sm:text-left">
                            <p class="eyebrow">Member Account</p>
                            <h1 class="font-serif text-3xl sm:text-4xl font-bold text-on-surface leading-tight truncate">
                                {{ user?.name || 'Guest' }}
                            </h1>
                            <p class="text-on-surface-variant text-sm break-all mt-1">{{ user?.email }}</p>
                            <div class="flex flex-wrap gap-2 justify-center sm:justify-start pt-3">
                                <span v-if="user?.is_admin" class="chip chip-gold">
                                    <span class="material-symbols-outlined chip-ico" aria-hidden="true">verified</span>
                                    Admin
                                </span>
                                <span class="chip" :class="user?.status === 'active' ? 'chip-active' : 'chip-muted'">
                                    <span class="dot" aria-hidden="true"></span>
                                    {{ user?.status || 'unknown' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Stat tiles -->
                    <div class="stat-grid">
                        <div class="stat-tile">
                            <span class="material-symbols-outlined stat-ico" aria-hidden="true">confirmation_number</span>
                            <p class="stat-num">{{ reservations.length }}</p>
                            <p class="stat-label">Bookings</p>
                        </div>
                        <div class="stat-tile">
                            <span class="material-symbols-outlined stat-ico" aria-hidden="true">event_upcoming</span>
                            <p class="stat-num">{{ upcomingReservations.length }}</p>
                            <p class="stat-label">Upcoming</p>
                        </div>
                        <div class="stat-tile">
                            <span class="material-symbols-outlined stat-ico" aria-hidden="true">check_circle</span>
                            <p class="stat-num">{{ confirmedCount }}</p>
                            <p class="stat-label">Confirmed</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- ============ LOADING ============ -->
            <div v-if="loading" class="flex justify-center py-24" role="status" aria-label="Loading reservations">
                <div class="animate-spin rounded-full h-9 w-9 border-2 border-primary border-t-transparent"></div>
            </div>

            <!-- ============ ERROR ============ -->
            <div v-else-if="error" class="alert" role="alert">
                <span class="material-symbols-outlined" aria-hidden="true">error</span>
                <span>{{ error }}</span>
            </div>

            <!-- ============ EMPTY ============ -->
            <div v-else-if="!reservations.length" class="empty">
                <div class="empty-ico">
                    <span class="material-symbols-outlined" aria-hidden="true">local_activity</span>
                </div>
                <div class="space-y-1">
                    <h2 class="font-serif text-2xl font-bold text-on-surface">No bookings yet</h2>
                    <p class="text-on-surface-variant text-sm">Your cinema passes will appear here once you reserve a seat.</p>
                </div>
                <router-link to="/" class="cta-gold">
                    <span class="material-symbols-outlined" aria-hidden="true">movie</span>
                    Browse Films
                </router-link>
            </div>

            <!-- ============ BOOKINGS ============ -->
            <section v-else class="space-y-7">
                <!-- Segmented tab control -->
                <div class="seg" role="tablist" aria-label="Booking filter">
                    <button
                        role="tab"
                        :aria-selected="activeTab === 'upcoming'"
                        class="seg-btn"
                        :class="{ 'seg-active': activeTab === 'upcoming' }"
                        @click="activeTab = 'upcoming'"
                    >
                        <span class="material-symbols-outlined seg-ico" aria-hidden="true">event_upcoming</span>
                        Upcoming
                        <span class="seg-count">{{ upcomingReservations.length }}</span>
                    </button>
                    <button
                        role="tab"
                        :aria-selected="activeTab === 'history'"
                        class="seg-btn"
                        :class="{ 'seg-active': activeTab === 'history' }"
                        @click="activeTab = 'history'"
                    >
                        <span class="material-symbols-outlined seg-ico" aria-hidden="true">history</span>
                        History
                        <span class="seg-count">{{ pastReservations.length }}</span>
                    </button>
                </div>

                <!-- Per-tab empty -->
                <div v-if="!displayedReservations.length" class="tab-empty">
                    <span class="material-symbols-outlined" aria-hidden="true">
                        {{ activeTab === 'upcoming' ? 'event_busy' : 'history_toggle_off' }}
                    </span>
                    <p>{{ activeTab === 'upcoming' ? 'No upcoming bookings.' : 'No past bookings yet.' }}</p>
                </div>

                <!-- Ticket grid -->
                <transition-group v-else tag="div" name="ticket-fade" class="grid gap-5 sm:gap-6 lg:grid-cols-2">
                    <article
                        v-for="res in displayedReservations"
                        :key="res.id"
                        class="ticket"
                        :class="{
                            'ticket-cancelled': res.status === 'canceled',
                            'ticket-past': activeTab === 'history' && res.status !== 'canceled',
                        }"
                    >
                        <!-- Poster stub -->
                        <div class="ticket-poster">
                            <img
                                :src="getImageUrl(res.session?.film?.image)"
                                :alt="res.session?.film?.title"
                                class="w-full h-full object-cover"
                            />
                            <div class="poster-shade" aria-hidden="true"></div>
                        </div>

                        <!-- Perforation -->
                        <div class="ticket-perf" aria-hidden="true"></div>

                        <!-- Body -->
                        <div class="ticket-body">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="font-serif text-lg font-bold text-on-surface leading-tight">
                                    {{ res.session?.film?.title }}
                                </h3>
                                <span class="status" :class="statusClass(res.status)">
                                    <span class="material-symbols-outlined status-ico" aria-hidden="true">{{ statusIcon(res.status) }}</span>
                                    {{ statusLabel(res.status) }}
                                </span>
                            </div>

                            <p class="ticket-when">
                                <span class="material-symbols-outlined" aria-hidden="true">schedule</span>
                                {{ formatDateTime(res.session?.start_time) }}
                            </p>

                            <dl class="ticket-meta">
                                <div>
                                    <dt>Room</dt>
                                    <dd>{{ res.session?.room?.name || '—' }}</dd>
                                </div>
                                <div>
                                    <dt>Seat</dt>
                                    <dd><span class="seat-chip">{{ res.seat?.seat_number || '—' }}</span></dd>
                                </div>
                            </dl>

                            <div class="ticket-footer">
                                <button
                                    v-for="ticket in (res.status === 'accepted' && res.tickets?.length ? res.tickets : [])"
                                    :key="ticket.id"
                                    @click="downloadTicket(ticket)"
                                    :disabled="downloading === ticket.id"
                                    class="btn-download"
                                    :aria-label="`Download ticket for ${res.session?.film?.title}`"
                                >
                                    <span class="material-symbols-outlined btn-ico" aria-hidden="true">download</span>
                                    {{ downloading === ticket.id ? 'Preparing…' : 'Download Ticket' }}
                                </button>
                                <button
                                    v-if="res.status !== 'canceled'"
                                    @click="cancelReservation(res)"
                                    :disabled="cancelling === res.id"
                                    class="btn-cancel"
                                    :aria-label="`Cancel reservation for ${res.session?.film?.title}`"
                                >
                                    <span class="material-symbols-outlined btn-ico" aria-hidden="true">close</span>
                                    {{ cancelling === res.id ? 'Cancelling…' : 'Cancel' }}
                                </button>
                            </div>
                        </div>
                    </article>
                </transition-group>
            </section>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../store/auth';

const auth = useAuthStore();
const user = ref(null);
const reservations = ref([]);
const loading = ref(true);
const error = ref('');
const cancelling = ref(null);
const downloading = ref(null);
const activeTab = ref('upcoming');

const statusLabel = (s) => ({
    pending: 'Pending Payment',
    accepted: 'Confirmed',
    canceled: 'Cancelled',
}[s] || s);

const statusIcon = (s) => ({
    pending: 'schedule',
    accepted: 'check_circle',
    canceled: 'cancel',
}[s] || 'help');

const statusClass = (s) => ({
    'status-active': s === 'accepted',
    'status-pending': s === 'pending',
    'status-cancelled': s === 'canceled',
});

// Presentational grouping only — does not alter source data.
const isUpcoming = (res) => {
    if (res.status === 'canceled') return false;
    const t = res.session?.start_time;
    return t ? new Date(t).getTime() >= Date.now() : true;
};
const upcomingReservations = computed(() => reservations.value.filter(isUpcoming));
const pastReservations = computed(() => reservations.value.filter((r) => !isUpcoming(r)));
const displayedReservations = computed(() =>
    activeTab.value === 'upcoming' ? upcomingReservations.value : pastReservations.value
);
const confirmedCount = computed(() => reservations.value.filter((r) => r.status === 'accepted').length);
const userInitial = computed(() => user.value?.name?.charAt(0)?.toUpperCase() || '?');

const formatDateTime = (d) => d ? new Date(d).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' }) : '';

const getImageUrl = (image) => {
    if (!image || !image.path) return 'https://via.placeholder.com/100x150?text=No+Image';
    if (image.path.startsWith('http://') || image.path.startsWith('https://')) {
        return image.path;
    }
    return `/storage/${image.path}`;
};

const fetchProfile = async () => {
    try {
        const [profileRes, resRes] = await Promise.all([
            axios.get('/api/v1/profile'),
            axios.get('/api/v1/reservation'),
        ]);
        user.value = profileRes.data.user || profileRes.data;
        reservations.value = resRes.data.reservations || [];
    } catch (err) {
        console.error('Failed to load profile:', err);
        error.value = err.response?.data?.message || 'Failed to load your account. Please try again.';
    } finally {
        loading.value = false;
    }
};

const cancelReservation = async (res) => {
    if (!confirm(`Cancel reservation for "${res.session?.film?.title}"?`)) return;
    cancelling.value = res.id;
    try {
        await axios.put(`/api/v1/reservation/${res.id}`, { status: 'canceled' });
        const idx = reservations.value.findIndex(r => r.id === res.id);
        if (idx !== -1) reservations.value[idx].status = 'canceled';
    } catch (err) {
        alert(err.response?.data?.message || 'Cancellation failed');
    } finally {
        cancelling.value = null;
    }
};

const downloadTicket = async (ticket) => {
    downloading.value = ticket.id;
    try {
        const res = await axios.get(`/api/v1/tickets/${ticket.id}/download`, { responseType: 'blob' });
        const url = URL.createObjectURL(res.data);
        const a = document.createElement('a');
        a.href = url;
        a.download = `ticket-${ticket.id}.pdf`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    } catch (err) {
        alert(err.response?.data?.message || 'Failed to download ticket');
    } finally {
        downloading.value = null;
    }
};

onMounted(fetchProfile);
</script>

<style scoped>
.profile-page {
    padding-top: 2.5rem;
}

/* ── Material Symbols sizing helper ── */
.material-symbols-outlined {
    font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}

/* ============ HERO ============ */
.hero {
    position: relative;
    overflow: hidden;
    border-radius: 1.5rem;
    background: var(--color-surface-container);
    border: 1px solid var(--color-outline-variant);
    box-shadow: 0 24px 60px -24px rgba(0, 0, 0, 0.65);
}
.hero-glow {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(120% 140% at 12% 0%, rgba(212, 160, 23, 0.16), transparent 55%),
        radial-gradient(80% 120% at 100% 0%, rgba(212, 160, 23, 0.07), transparent 60%);
    pointer-events: none;
}
.hero-inner {
    position: relative;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}
.hero-identity {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
}
@media (min-width: 640px) {
    .hero-inner { padding: 2.5rem; }
    .hero-identity { flex-direction: row; align-items: center; }
}
@media (min-width: 1024px) {
    .hero-inner { flex-direction: row; align-items: center; justify-content: space-between; gap: 2.5rem; }
}

.eyebrow {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.25em;
    color: var(--color-on-surface-variant);
    margin-bottom: 0.4rem;
}

/* Avatar */
.avatar-ring {
    padding: 3px;
    border-radius: 9999px;
    background: linear-gradient(140deg, rgba(212, 160, 23, 0.8), rgba(212, 160, 23, 0.1));
    box-shadow: 0 10px 34px -10px rgba(212, 160, 23, 0.5);
}
.avatar {
    width: 7rem;
    height: 7rem;
    border-radius: 9999px;
    background: var(--color-surface-container-low);
    border: 1px solid var(--color-outline-variant);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-serif);
    font-size: 3rem;
    font-weight: 700;
    color: var(--color-primary);
    user-select: none;
}

/* Chips */
.chip {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.3rem 0.8rem;
    border-radius: 9999px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    border: 1px solid transparent;
}
.chip-ico { font-size: 14px; }
.chip .dot {
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    background: currentColor;
}
.chip-gold {
    color: var(--color-primary);
    background: rgba(212, 160, 23, 0.12);
    border-color: rgba(212, 160, 23, 0.3);
}
.chip-active {
    color: #4ade80;
    background: rgba(74, 222, 128, 0.1);
    border-color: rgba(74, 222, 128, 0.28);
}
.chip-muted {
    color: #f87171;
    background: rgba(248, 113, 113, 0.1);
    border-color: rgba(248, 113, 113, 0.28);
}

/* Stat tiles */
.stat-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    width: 100%;
}
@media (min-width: 1024px) {
    .stat-grid { width: auto; }
}
.stat-tile {
    min-height: 44px;
    padding: 1.1rem 0.75rem;
    text-align: center;
    border-radius: 1rem;
    background: var(--color-surface-container-low);
    border: 1px solid var(--color-outline-variant);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-width: 0;
}
@media (min-width: 1024px) {
    .stat-tile { min-width: 7rem; }
}
.stat-ico {
    font-size: 20px;
    color: var(--color-primary);
    opacity: 0.85;
    margin-bottom: 0.3rem;
}
.stat-num {
    font-family: var(--font-serif);
    font-size: 1.7rem;
    font-weight: 700;
    line-height: 1;
    color: var(--color-on-surface);
}
.stat-label {
    margin-top: 0.4rem;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    color: var(--color-on-surface-variant);
}

/* ============ SEGMENTED CONTROL ============ */
.seg {
    display: inline-flex;
    padding: 0.3rem;
    gap: 0.25rem;
    border-radius: 9999px;
    background: var(--color-surface-container-low);
    border: 1px solid var(--color-outline-variant);
    max-width: 100%;
}
.seg-btn {
    flex: 1;
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    padding: 0 1.1rem;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--color-on-surface-variant);
    cursor: pointer;
    border: 0;
    background: transparent;
    transition: color 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
    white-space: nowrap;
}
.seg-btn:hover { color: var(--color-on-surface); }
.seg-ico { font-size: 16px; }
.seg-active {
    color: var(--color-on-primary);
    background: linear-gradient(135deg, #f2c94c 0%, #d4a017 55%, #a47c10 100%);
    box-shadow: 0 6px 18px -6px rgba(212, 160, 23, 0.55);
}
.seg-active:hover { color: var(--color-on-primary); }
.seg-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.3rem;
    height: 1.3rem;
    padding: 0 0.4rem;
    border-radius: 9999px;
    font-size: 10px;
    background: rgba(0, 0, 0, 0.18);
}
.seg-btn:not(.seg-active) .seg-count {
    background: rgba(255, 255, 255, 0.06);
}

/* ============ TICKET CARD ============ */
.ticket {
    position: relative;
    display: flex;
    align-items: stretch;
    border-radius: 1.1rem;
    overflow: hidden;
    background: var(--color-surface-container);
    border: 1px solid var(--color-outline-variant);
    transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}
.ticket:hover {
    border-color: rgba(212, 160, 23, 0.4);
    transform: translateY(-3px);
    box-shadow: 0 18px 42px -22px rgba(0, 0, 0, 0.75);
}
.ticket-poster {
    position: relative;
    width: 104px;
    flex-shrink: 0;
    background: var(--color-surface-container-low);
}
.ticket-poster img { height: 100%; }
.poster-shade {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent 60%, rgba(26, 24, 48, 0.5) 100%);
}

/* Perforated divider with notches */
.ticket-perf {
    position: relative;
    width: 0;
    border-left: 2px dashed var(--color-outline-variant);
    margin: 0.9rem 0;
}
.ticket-perf::before,
.ticket-perf::after {
    content: '';
    position: absolute;
    left: 50%;
    width: 16px;
    height: 16px;
    border-radius: 9999px;
    background: var(--color-surface);
    transform: translateX(-50%);
}
.ticket-perf::before { top: -0.9rem; transform: translate(-50%, -50%); }
.ticket-perf::after { bottom: -0.9rem; transform: translate(-50%, 50%); }

.ticket-body {
    flex: 1;
    min-width: 0;
    padding: 1.15rem 1.3rem;
    display: flex;
    flex-direction: column;
}
.ticket-when {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.78rem;
    font-weight: 500;
    color: var(--color-on-surface-variant);
    margin-top: 0.5rem;
}
.ticket-when .material-symbols-outlined { font-size: 15px; }
.ticket-meta {
    display: flex;
    gap: 1.75rem;
    margin-top: 0.9rem;
}
.ticket-meta dt {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    color: var(--color-outline);
}
.ticket-meta dd {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--color-on-surface);
    margin-top: 0.25rem;
}
.seat-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.1rem 0.55rem;
    border-radius: 0.5rem;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--color-primary);
    background: rgba(212, 160, 23, 0.1);
    border: 1px solid rgba(212, 160, 23, 0.28);
}
.ticket-footer {
    margin-top: auto;
    padding-top: 1.1rem;
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
}
.ticket-past { opacity: 0.82; }
.ticket-cancelled {
    opacity: 0.55;
    border-color: rgba(248, 113, 113, 0.28);
}
.ticket-cancelled .ticket-poster img { filter: grayscale(0.75); }

/* Status badge */
.status {
    display: inline-flex;
    align-items: center;
    gap: 0.28rem;
    flex-shrink: 0;
    padding: 0.25rem 0.6rem;
    border-radius: 9999px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    border: 1px solid transparent;
}
.status-ico { font-size: 13px; }
.status-active {
    color: #4ade80;
    background: rgba(74, 222, 128, 0.1);
    border-color: rgba(74, 222, 128, 0.28);
}
.status-pending {
    color: var(--color-primary);
    background: rgba(212, 160, 23, 0.1);
    border-color: rgba(212, 160, 23, 0.28);
}
.status-cancelled {
    color: #f87171;
    background: rgba(248, 113, 113, 0.1);
    border-color: rgba(248, 113, 113, 0.28);
}

/* Buttons */
.btn-download,
.btn-cancel {
    min-height: 44px;
    width: 100%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    padding: 0 1.1rem;
    border-radius: 0.65rem;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.13em;
    cursor: pointer;
    transition: background 0.2s ease, border-color 0.2s ease;
}
.btn-ico { font-size: 16px; }
.btn-download {
    border: 1px solid rgba(212, 160, 23, 0.32);
    background: rgba(212, 160, 23, 0.1);
    color: var(--color-primary);
}
.btn-download:hover:not(:disabled) {
    background: rgba(212, 160, 23, 0.18);
    border-color: rgba(212, 160, 23, 0.55);
}
.btn-cancel {
    border: 1px solid rgba(248, 113, 113, 0.28);
    background: rgba(248, 113, 113, 0.08);
    color: #f87171;
}
.btn-cancel:hover:not(:disabled) {
    background: rgba(248, 113, 113, 0.16);
    border-color: rgba(248, 113, 113, 0.5);
}
.btn-download:disabled,
.btn-cancel:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ============ EMPTY / ERROR / TAB-EMPTY ============ */
.empty {
    padding: 5rem 1.5rem;
    text-align: center;
    border: 1px dashed var(--color-outline-variant);
    border-radius: 1.5rem;
    background: rgba(26, 24, 48, 0.4);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.25rem;
}
.empty-ico {
    width: 4rem;
    height: 4rem;
    border-radius: 9999px;
    background: var(--color-surface-container-low);
    border: 1px solid var(--color-outline-variant);
    display: flex;
    align-items: center;
    justify-content: center;
}
.empty-ico .material-symbols-outlined {
    font-size: 30px;
    color: var(--color-primary);
}
.cta-gold {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    min-height: 44px;
    padding: 0 2rem;
    border-radius: 0.65rem;
    background: linear-gradient(135deg, #f2c94c 0%, #d4a017 55%, #a47c10 100%);
    color: var(--color-on-primary);
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    box-shadow: 0 10px 26px -10px rgba(212, 160, 23, 0.5);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.cta-gold:hover { transform: translateY(-2px); }
.cta-gold .material-symbols-outlined { font-size: 18px; }

.alert {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 1rem 1.25rem;
    border-radius: 1rem;
    background: rgba(248, 113, 113, 0.1);
    border: 1px solid rgba(248, 113, 113, 0.3);
    color: #f87171;
    font-size: 0.875rem;
    font-weight: 500;
}

.tab-empty {
    padding: 3.5rem 1.5rem;
    text-align: center;
    border: 1px dashed var(--color-outline-variant);
    border-radius: 1.25rem;
    color: var(--color-on-surface-variant);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.6rem;
}
.tab-empty .material-symbols-outlined {
    font-size: 32px;
    color: var(--color-outline);
}
.tab-empty p { font-size: 0.875rem; }

/* ── Transition for ticket grid ── */
.ticket-fade-enter-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.ticket-fade-enter-from { opacity: 0; transform: translateY(8px); }

/* ============ MOBILE: stack ticket ============ */
@media (max-width: 480px) {
    .ticket { flex-direction: column; }
    .ticket-poster { width: 100%; height: 160px; }
    .poster-shade { display: none; }
    .ticket-perf {
        width: auto;
        height: 0;
        margin: 0 0.9rem;
        border-left: 0;
        border-top: 2px dashed var(--color-outline-variant);
    }
    .ticket-perf::before { top: 50%; left: -0.9rem; transform: translate(-50%, -50%); }
    .ticket-perf::after { top: 50%; bottom: auto; left: auto; right: -0.9rem; transform: translate(50%, -50%); }
    .seg { width: 100%; }
}
</style>
