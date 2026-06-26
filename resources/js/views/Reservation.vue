<template>
    <div class="reservation min-h-screen pb-24 bg-surface text-on-surface">

        <!-- Loading -->
        <div v-if="loading" class="flex flex-col justify-center items-center h-[60vh] gap-4">
            <div class="animate-spin rounded-full h-9 w-9 border-2 border-primary border-t-transparent"></div>
            <span class="text-[10px] font-bold uppercase tracking-[0.25em] text-on-surface-variant">Preparing your auditorium</span>
        </div>

        <template v-else>
            <!-- ── Step Indicator ── -->
            <nav aria-label="Booking progress" class="max-w-3xl mx-auto px-6 pt-10 sm:pt-14">
                <ol class="flex items-center justify-center gap-2 sm:gap-4">
                    <li
                        v-for="(label, i) in stepLabels"
                        :key="label"
                        class="flex items-center gap-2 sm:gap-4"
                    >
                        <div class="flex items-center gap-2.5">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full text-[11px] font-bold transition-all duration-300 border"
                                :class="i < stepIndex
                                    ? 'gold-gradient border-transparent'
                                    : i === stepIndex
                                        ? 'bg-primary/15 border-primary text-primary'
                                        : 'bg-surface-container border-outline-variant/40 text-outline'"
                                :aria-current="i === stepIndex ? 'step' : undefined"
                            >
                                <span v-if="i < stepIndex" class="material-symbols-outlined text-[16px]" aria-hidden="true">check</span>
                                <span v-else>{{ i + 1 }}</span>
                            </span>
                            <span
                                class="hidden sm:inline text-[10px] font-bold uppercase tracking-[0.2em] transition-colors"
                                :class="i <= stepIndex ? 'text-on-surface' : 'text-outline'"
                            >
                                {{ label }}
                            </span>
                        </div>
                        <span
                            v-if="i < stepLabels.length - 1"
                            class="w-6 sm:w-12 h-px transition-colors duration-300"
                            :class="i < stepIndex ? 'bg-primary' : 'bg-outline-variant/40'"
                            aria-hidden="true"
                        ></span>
                    </li>
                </ol>
            </nav>

            <!-- ════════ Step 1: Seat Selection ════════ -->
            <div v-if="session && step === 'seats'" class="max-w-7xl mx-auto px-5 sm:px-10 lg:px-16 mt-10 sm:mt-14">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 xl:gap-14 items-start">

                    <!-- Left: Theater -->
                    <section class="lg:col-span-8 flex flex-col items-center">
                        <header class="text-center space-y-2.5 mb-12">
                            <p class="text-[10px] text-primary font-bold uppercase tracking-[0.3em]">Choose your seats</p>
                            <h1 class="font-serif text-3xl sm:text-4xl font-bold leading-tight">
                                {{ session.film?.title }}
                            </h1>
                            <p class="text-[11px] text-on-surface-variant font-bold uppercase tracking-[0.18em]">
                                {{ formatDateTime(session.start_time) }} <span class="text-outline mx-1">•</span> {{ session.room?.name }}
                            </p>
                        </header>

                        <!-- Curved screen -->
                        <div class="w-full max-w-2xl mb-14 flex flex-col items-center">
                            <div class="screen-stage w-full">
                                <div class="screen-bar"></div>
                                <div class="screen-glow"></div>
                            </div>
                            <span class="mt-5 text-[10px] uppercase text-outline font-bold tracking-[0.4em]">Screen</span>
                        </div>

                        <!-- Legend -->
                        <div class="flex flex-wrap justify-center gap-x-7 gap-y-3 mb-12 bg-surface-container px-7 py-3.5 rounded-full border border-outline-variant">
                            <div class="flex items-center gap-2.5">
                                <span class="w-5 h-5 bg-surface-container-low border-2 border-outline rounded-md" aria-hidden="true"></span>
                                <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-[0.15em]">Available</span>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="w-5 h-5 gold-gradient rounded-md" aria-hidden="true"></span>
                                <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-[0.15em]">Selected</span>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="w-5 h-5 bg-surface-container-highest rounded-md" aria-hidden="true"></span>
                                <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-[0.15em]">Sold out</span>
                            </div>
                        </div>

                        <!-- Seat grid -->
                        <div class="w-full max-w-full overflow-x-auto pb-6 flex justify-start lg:justify-center">
                            <div
                                class="grid gap-2.5 sm:gap-3 p-6 sm:p-8 bg-surface-container border border-outline-variant rounded-2xl shadow-[0_8px_40px_rgba(0,0,0,0.4)]"
                                :style="{ gridTemplateColumns: `repeat(${session.room?.columns || 10}, minmax(0, 1fr))` }"
                                role="group"
                                aria-label="Seat map"
                            >
                                <button
                                    v-for="seat in seats"
                                    :key="seat.id"
                                    @click="toggleSeat(seat)"
                                    :disabled="isSeatOccupied(seat.id)"
                                    :aria-pressed="isSelected(seat.id)"
                                    :title="seat.seat_number"
                                    :aria-label="seat.seat_number + (isSeatOccupied(seat.id) ? ' sold out' : isSelected(seat.id) ? ' selected' : ' available')"
                                    class="seat w-11 h-11 md:w-10 md:h-10 rounded-lg border-2 transition-all duration-200 flex flex-col items-center justify-center text-[9px] font-bold shrink-0"
                                    :class="[
                                        isSeatOccupied(seat.id) ? 'bg-surface-container-highest border-surface-container-highest text-outline/40 cursor-not-allowed' :
                                        isSelected(seat.id) ? 'seat-selected gold-gradient border-transparent shadow-lg shadow-primary/30 scale-110' :
                                        'bg-surface-container-low border-outline text-on-surface hover:border-primary hover:-translate-y-0.5 cursor-pointer'
                                    ]"
                                >
                                    <span class="material-symbols-outlined text-[15px] -mb-0.5" aria-hidden="true">event_seat</span>
                                    <span class="leading-none">{{ seat.seat_number.replace('S-', '') }}</span>
                                </button>
                            </div>
                        </div>
                    </section>

                    <!-- Right: Sticky booking summary -->
                    <aside class="lg:col-span-4 w-full lg:sticky lg:top-8">
                        <div class="bg-surface-container rounded-2xl p-7 border border-outline-variant shadow-[0_10px_40px_rgba(0,0,0,0.5)] space-y-6">
                            <div class="flex gap-4">
                                <div class="w-20 h-28 flex-shrink-0 rounded-xl overflow-hidden bg-surface-container-high border border-outline-variant">
                                    <img class="w-full h-full object-cover" :src="getImageUrl(session.film?.image)" :alt="session.film?.title" />
                                </div>
                                <div class="min-w-0">
                                    <h2 class="font-serif text-lg font-bold leading-snug truncate">{{ session.film?.title }}</h2>
                                    <p class="text-on-surface-variant text-[11px] flex items-center gap-1 mt-1.5 uppercase font-bold tracking-[0.12em]">
                                        <span class="material-symbols-outlined text-[15px] text-primary" aria-hidden="true">location_on</span>
                                        {{ session.room?.name }}
                                    </p>
                                    <p class="text-on-surface-variant text-[11px] flex items-center gap-1 mt-1 uppercase font-bold tracking-[0.12em]">
                                        <span class="material-symbols-outlined text-[15px] text-primary" aria-hidden="true">payments</span>
                                        ${{ Number(session.price || 0).toFixed(2) }} / seat
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 border-y border-outline-variant py-5">
                                <div>
                                    <span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-outline mb-1">Date</span>
                                    <span class="font-bold text-sm text-on-surface">{{ formatDate(session.start_time) }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-outline mb-1">Time</span>
                                    <span class="font-bold text-sm text-on-surface">{{ formatTime(session.start_time) }}</span>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2.5">
                                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-outline">Your seats</span>
                                    <span class="text-[10px] font-bold text-primary">{{ selectedSeats.length }} selected</span>
                                </div>
                                <div class="flex flex-wrap gap-2 min-h-[28px]">
                                    <span v-if="!selectedSeats.length" class="text-xs text-on-surface-variant italic">Tap a seat to begin.</span>
                                    <span
                                        v-for="seat in selectedSeats"
                                        :key="seat.id"
                                        class="bg-primary/10 text-primary px-3 py-1 rounded-full text-[10px] font-bold border border-primary/25"
                                    >
                                        {{ seat.seat_number }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex justify-between items-end pt-1">
                                <div>
                                    <span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-outline mb-0.5">Subtotal</span>
                                    <span class="font-serif text-3xl font-bold text-primary">${{ totalPrice }}</span>
                                </div>
                                <span class="text-on-surface-variant text-[10px] font-bold uppercase mb-1.5 tracking-[0.12em]">+ Taxes &amp; fees</span>
                            </div>

                            <button
                                @click="proceedToPayment"
                                :disabled="!selectedSeats.length"
                                class="w-full gold-gradient min-h-[48px] py-4 rounded-xl font-bold text-xs uppercase tracking-[0.2em] hover:brightness-105 transition-all shadow-lg active:scale-[0.98] cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed disabled:active:scale-100"
                            >
                                Proceed to payment
                            </button>
                            <p class="text-center text-[10px] text-outline font-bold uppercase tracking-[0.15em]">
                                <span class="material-symbols-outlined text-[13px] align-middle" aria-hidden="true">lock</span>
                                Secure checkout by Cinehall Pay
                            </p>
                        </div>
                    </aside>
                </div>
            </div>

            <!-- ════════ Step 2: Payment ════════ -->
            <div v-else-if="session && step === 'payment'" class="max-w-2xl mx-auto px-5 sm:px-6 mt-10 sm:mt-14">
                <header class="text-center space-y-2 mb-10">
                    <p class="text-[10px] text-primary font-bold uppercase tracking-[0.3em]">Secure checkout</p>
                    <h1 class="font-serif text-3xl sm:text-4xl font-bold">Confirm &amp; pay</h1>
                </header>

                <!-- Ticket summary card -->
                <div class="ticket-card relative bg-surface-container rounded-2xl border border-outline-variant shadow-[0_10px_40px_rgba(0,0,0,0.5)] overflow-hidden">
                    <div class="p-7 sm:p-8 space-y-5">
                        <div class="flex items-center gap-3 pb-4 border-b border-outline-variant">
                            <span class="material-symbols-outlined text-primary text-[22px]" aria-hidden="true">confirmation_number</span>
                            <h3 class="font-sans text-[11px] font-bold uppercase tracking-[0.25em] text-on-surface-variant">Your booking</h3>
                        </div>
                        <div class="space-y-3.5 text-sm">
                            <div class="flex justify-between items-center gap-4">
                                <span class="text-on-surface-variant">Film</span>
                                <span class="font-bold text-on-surface text-right">{{ session.film?.title }}</span>
                            </div>
                            <div class="flex justify-between items-center gap-4">
                                <span class="text-on-surface-variant">Showing</span>
                                <span class="font-bold text-on-surface text-right">{{ formatDateTime(session.start_time) }}</span>
                            </div>
                            <div class="flex justify-between items-center gap-4">
                                <span class="text-on-surface-variant">Auditorium</span>
                                <span class="font-bold text-on-surface text-right">{{ session.room?.name }}</span>
                            </div>
                            <div class="flex justify-between items-start gap-4">
                                <span class="text-on-surface-variant pt-1">Seats</span>
                                <div class="flex flex-wrap gap-1.5 justify-end">
                                    <span
                                        v-for="seat in selectedSeats"
                                        :key="seat.id"
                                        class="bg-primary/10 text-primary px-2.5 py-1 rounded-full text-[10px] font-bold border border-primary/25"
                                    >
                                        {{ seat.seat_number }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- perforation -->
                    <div class="ticket-divider" aria-hidden="true"></div>
                    <div class="px-7 sm:px-8 py-5 flex justify-between items-center bg-surface-container-low">
                        <span class="font-bold uppercase tracking-[0.2em] text-[11px] text-outline">Grand total</span>
                        <span class="font-serif text-3xl font-bold text-primary">${{ totalPrice }}</span>
                    </div>
                </div>

                <!-- Payment methods -->
                <fieldset class="mt-9">
                    <legend class="font-sans text-[11px] font-bold uppercase tracking-[0.25em] text-outline mb-4">Payment method</legend>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <button
                            @click="paymentMethod = 'stripe'"
                            :aria-pressed="paymentMethod === 'stripe'"
                            :class="paymentMethod === 'stripe' ? 'border-primary bg-primary/8 text-primary' : 'border-outline-variant bg-surface-container text-on-surface hover:border-primary/50'"
                            class="relative p-6 min-h-[44px] rounded-xl border-2 transition-all flex flex-col items-center gap-2.5 cursor-pointer"
                        >
                            <span v-if="paymentMethod === 'stripe'" class="material-symbols-outlined absolute top-3 right-3 text-primary text-[18px]" aria-hidden="true">check_circle</span>
                            <span class="material-symbols-outlined text-[30px]" aria-hidden="true">credit_card</span>
                            <span class="font-bold uppercase tracking-[0.18em] text-xs">Credit card</span>
                            <span class="text-[9px] text-outline font-bold uppercase tracking-[0.12em]">Stripe checkout</span>
                        </button>
                        <button
                            @click="paymentMethod = 'paypal'"
                            :aria-pressed="paymentMethod === 'paypal'"
                            :class="paymentMethod === 'paypal' ? 'border-primary bg-primary/8 text-primary' : 'border-outline-variant bg-surface-container text-on-surface hover:border-primary/50'"
                            class="relative p-6 min-h-[44px] rounded-xl border-2 transition-all flex flex-col items-center gap-2.5 cursor-pointer"
                        >
                            <span v-if="paymentMethod === 'paypal'" class="material-symbols-outlined absolute top-3 right-3 text-primary text-[18px]" aria-hidden="true">check_circle</span>
                            <span class="material-symbols-outlined text-[30px]" aria-hidden="true">account_balance_wallet</span>
                            <span class="font-bold uppercase tracking-[0.18em] text-xs">PayPal</span>
                            <span class="text-[9px] text-outline font-bold uppercase tracking-[0.12em]">Fast secure redirect</span>
                        </button>
                    </div>
                </fieldset>

                <div v-if="bookingError" role="alert" class="flex items-center gap-2.5 bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-xl text-xs font-bold mt-6">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">error</span>
                    {{ bookingError }}
                </div>

                <div class="flex gap-4 mt-8">
                    <button
                        @click="step = 'seats'"
                        class="px-7 min-h-[48px] py-4 rounded-xl border border-outline-variant text-on-surface-variant hover:border-primary hover:text-primary font-bold uppercase tracking-[0.18em] text-xs transition-colors cursor-pointer bg-surface-container flex items-center gap-2"
                    >
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
                        Back
                    </button>
                    <button
                        @click="confirmBooking"
                        :disabled="!paymentMethod || booking"
                        class="flex-1 gold-gradient min-h-[48px] py-4 rounded-xl font-bold uppercase tracking-[0.18em] text-xs transition-all shadow-lg active:scale-[0.98] cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed disabled:active:scale-100 flex items-center justify-center gap-2"
                    >
                        <span v-if="booking" class="animate-spin rounded-full h-4 w-4 border-2 border-on-primary border-t-transparent" aria-hidden="true"></span>
                        {{ booking ? 'Authorizing…' : 'Authorize payment' }}
                    </button>
                </div>
            </div>

            <!-- ════════ Step 3: Success ════════ -->
            <div v-else-if="step === 'success'" class="max-w-lg mx-auto px-5 mt-12 sm:mt-20">
                <div class="ticket-card relative bg-surface-container rounded-3xl border border-outline-variant shadow-[0_10px_50px_rgba(0,0,0,0.55)] overflow-hidden text-center">
                    <div class="p-10 space-y-5">
                        <div class="success-check w-20 h-20 gold-gradient rounded-full flex items-center justify-center mx-auto shadow-lg shadow-primary/30">
                            <span class="material-symbols-outlined text-[40px]" aria-hidden="true">check</span>
                        </div>
                        <div class="space-y-2">
                            <p class="text-[10px] text-primary font-bold uppercase tracking-[0.3em]">Confirmed</p>
                            <h1 class="font-serif text-3xl sm:text-4xl font-bold leading-tight">Enjoy the show</h1>
                            <p class="text-on-surface-variant text-sm leading-relaxed max-w-sm mx-auto">
                                Your seats are reserved. Your printable passes are waiting in your profile.
                            </p>
                        </div>
                    </div>
                    <div class="ticket-divider" aria-hidden="true"></div>
                    <div class="px-10 py-6 bg-surface-container-low flex items-center justify-center gap-2 text-[10px] font-bold uppercase tracking-[0.2em] text-outline">
                        <span class="material-symbols-outlined text-[15px] text-primary" aria-hidden="true">confirmation_number</span>
                        Cinehall e-ticket
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 justify-center mt-8">
                    <router-link to="/profile" class="px-8 min-h-[48px] py-4 gold-gradient rounded-xl font-bold uppercase tracking-[0.18em] text-xs shadow-lg shadow-primary/25 transition-all hover:brightness-105 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">local_activity</span>
                        Go to tickets
                    </router-link>
                    <router-link to="/" class="px-8 min-h-[48px] py-4 bg-surface-container border border-outline-variant hover:border-primary hover:text-primary text-on-surface rounded-xl font-bold uppercase tracking-[0.18em] text-xs transition-colors flex items-center justify-center">
                        Return home
                    </router-link>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../store/auth';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

const session = ref(null);
const seats = ref([]);
const loading = ref(true);
const booking = ref(false);
const selectedSeats = ref([]);
const occupiedSeatIds = ref([]);
const step = ref('seats'); // 'seats' | 'payment' | 'success'
const paymentMethod = ref(null);
const bookingError = ref('');

// Presentational helpers for the step indicator (no business logic).
const stepLabels = ['Seats', 'Payment', 'Done'];
const stepIndex = computed(() => (step.value === 'seats' ? 0 : step.value === 'payment' ? 1 : 2));

const totalPrice = computed(() => {
    return (selectedSeats.value.length * (session.value?.price || 0)).toFixed(2);
});

const isSeatOccupied = (seatId) => occupiedSeatIds.value.includes(seatId);
const isSelected = (seatId) => selectedSeats.value.some(s => s.id === seatId);

const toggleSeat = (seat) => {
    if (isSeatOccupied(seat.id)) return;
    const idx = selectedSeats.value.findIndex(s => s.id === seat.id);
    if (idx > -1) {
        selectedSeats.value.splice(idx, 1);
    } else {
        selectedSeats.value.push(seat);
    }
};

const proceedToPayment = () => {
    if (selectedSeats.value.length) step.value = 'payment';
};

const fetchSession = async () => {
    try {
        const res = await axios.get(`/api/v1/sessions/${route.params.sessionId}`);
        session.value = res.data.film_session;

        // Fetch all seats for this room
        const roomId = session.value.room_id;
        const seatsRes = await axios.get(`/api/v1/rooms/${roomId}`);
        seats.value = seatsRes.data.room?.seats || [];

        // Set occupied seat IDs from active reservations only (pending/accepted).
        // Canceled reservations free the seat back up, so they must not appear sold-out.
        occupiedSeatIds.value = (session.value.reservations || [])
            .filter(r => ['pending', 'accepted'].includes(r.status))
            .map(r => r.seat_id);
    } catch (err) {
        console.error('Failed to fetch session info:', err);
    } finally {
        loading.value = false;
    }
};

const formatDateTime = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' });
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric' });
};

const formatTime = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const getImageUrl = (image) => {
    if (!image || !image.path) return 'https://via.placeholder.com/600x900?text=No+Image';
    if (image.path.startsWith('http://') || image.path.startsWith('https://')) {
        return image.path;
    }
    return `/storage/${image.path}`;
};

const confirmBooking = async () => {
    booking.value = true;
    bookingError.value = '';
    try {
        // 1. Create one reservation per selected seat, collecting their ids.
        // axios attaches the JWT globally (bootstrap.js interceptor) — no manual headers.
        const reservationIds = [];
        for (const seat of selectedSeats.value) {
            const res = await axios.post('/api/v1/reservation', {
                session_id: session.value.id,
                seat_id: seat.id,
            });
            reservationIds.push(res.data.reservation_id);
        }

        // 2. Open the payment provider's hosted checkout with the FULL id array,
        //    then hand the browser over via a redirect. Success is confirmed later
        //    by BookingResult after the provider redirects back to the SPA.
        if (paymentMethod.value === 'stripe') {
            const payRes = await axios.post('/api/v1/transactions/stripe', {
                reservation_ids: reservationIds,
            });
            if (payRes.data?.url) {
                window.location.href = payRes.data.url;
                return;
            }
        } else if (paymentMethod.value === 'paypal') {
            const payRes = await axios.post('/api/v1/transactions/paypal', {
                reservation_ids: reservationIds,
            });
            if (payRes.data?.approval_url) {
                window.location.href = payRes.data.approval_url;
                return;
            }
        }

        // No redirect URL came back — payment could not be started.
        bookingError.value = 'Could not start the payment. Please try again.';
    } catch (err) {
        bookingError.value = err.response?.data?.message || err.response?.data?.error || 'Transaction authorization failed. Please try again.';
    } finally {
        booking.value = false;
    }
};

onMounted(() => {
    if (!auth.isAuthenticated) {
        router.push('/login');
        return;
    }
    fetchSession();
});
</script>

<style scoped>
/* ── Curved screen with gold glow ── */
.screen-stage {
    position: relative;
    height: 56px;
    display: flex;
    justify-content: center;
}
.screen-bar {
    width: 100%;
    height: 6px;
    border-radius: 999px;
    background: linear-gradient(90deg, transparent, rgba(212, 160, 23, 0.85), transparent);
    box-shadow: 0 0 18px rgba(212, 160, 23, 0.55);
    transform: perspective(420px) rotateX(34deg);
}
.screen-glow {
    position: absolute;
    top: 8px;
    left: 8%;
    right: 8%;
    height: 90px;
    background: linear-gradient(to bottom, rgba(212, 160, 23, 0.22) 0%, transparent 75%);
    clip-path: ellipse(60% 50% at 50% 0%);
    pointer-events: none;
}

/* ── Seat selection pop ── */
.seat-selected {
    animation: seat-pop 0.22s ease;
}
@keyframes seat-pop {
    0% { transform: scale(0.9); }
    60% { transform: scale(1.18); }
    100% { transform: scale(1.1); }
}

/* ── Ticket perforation divider ── */
.ticket-card {
    position: relative;
}
.ticket-divider {
    position: relative;
    height: 0;
    border-top: 2px dashed var(--color-outline-variant);
    margin: 0 1.25rem;
}
.ticket-divider::before,
.ticket-divider::after {
    content: '';
    position: absolute;
    top: -11px;
    width: 22px;
    height: 22px;
    border-radius: 999px;
    background: var(--color-surface);
}
.ticket-divider::before { left: -1.85rem; }
.ticket-divider::after { right: -1.85rem; }

/* ── Success check entrance ── */
.success-check {
    animation: check-in 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes check-in {
    0% { transform: scale(0); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

@media (prefers-reduced-motion: reduce) {
    .seat-selected,
    .success-check {
        animation: none;
    }
}
</style>
