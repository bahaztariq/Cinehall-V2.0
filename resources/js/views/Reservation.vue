<template>
    <div>
        <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-yellow-600"></div>
        </div>

        <!-- Step 1: Seat Selection -->
        <div v-else-if="session && step === 'seats'" class="max-w-5xl mx-auto space-y-10">
            <div class="text-center space-y-2">
                <p class="text-gray-500 text-xs font-bold uppercase tracking-widest">Step 1 of 2 — Select Your Seats</p>
                <h1 class="text-4xl font-black uppercase italic tracking-tighter">{{ session.film?.title }}</h1>
                <p class="text-gray-500 font-bold uppercase tracking-widest text-sm">
                    {{ formatDateTime(session.start_time) }} • {{ session.room?.name }} • ${{ session.price }} / seat
                </p>
            </div>

            <!-- Legend -->
            <div class="flex justify-center gap-8 text-xs font-bold uppercase tracking-widest">
                <div class="flex items-center gap-2"><span class="w-5 h-5 rounded bg-white/10 border border-white/10 inline-block"></span> Available</div>
                <div class="flex items-center gap-2"><span class="w-5 h-5 rounded bg-yellow-600 border border-yellow-600 inline-block"></span> Selected</div>
                <div class="flex items-center gap-2"><span class="w-5 h-5 rounded bg-white/5 border border-white/5 inline-block"></span> Taken</div>
            </div>

            <!-- Screen -->
            <div class="space-y-2">
                <div class="h-1.5 w-3/4 mx-auto bg-linear-to-r from-transparent via-yellow-600/40 to-transparent rounded-full blur-sm"></div>
                <p class="text-center text-xs font-bold text-gray-600 uppercase tracking-[0.5em]">Screen</p>
            </div>

            <!-- Seats Grid -->
            <div class="flex justify-center overflow-x-auto pb-4">
                <div class="grid gap-2" :style="{ gridTemplateColumns: `repeat(${session.room?.columns || 10}, minmax(0, 1fr))` }">
                    <button
                        v-for="seat in seats"
                        :key="seat.id"
                        @click="toggleSeat(seat)"
                        :disabled="isSeatOccupied(seat.id)"
                        class="w-9 h-9 rounded-lg border transition-all flex items-center justify-center text-[0.6rem] font-bold"
                        :class="[
                            isSeatOccupied(seat.id) ? 'bg-white/5 border-white/5 text-gray-700 cursor-not-allowed' :
                            isSelected(seat.id) ? 'bg-yellow-600 border-yellow-600 text-white shadow-lg shadow-yellow-600/30' :
                            'bg-white/10 border-white/10 text-gray-400 hover:border-yellow-600/50 hover:bg-white/20'
                        ]"
                        :title="seat.seat_number"
                    >
                        {{ seat.seat_number }}
                    </button>
                </div>
            </div>

            <!-- Selection Summary & Proceed -->
            <div class="bg-white/5 border border-white/10 rounded-2xl p-8 flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="space-y-1 text-center md:text-left">
                    <p class="text-gray-500 uppercase tracking-widest font-bold text-xs">Selected Seats</p>
                    <p class="text-xl font-black text-white italic">
                        {{ selectedSeats.length ? selectedSeats.map(s => s.seat_number).join(', ') : 'None' }}
                    </p>
                </div>
                <div class="space-y-1 text-center md:text-left">
                    <p class="text-gray-500 uppercase tracking-widest font-bold text-xs">Total Price</p>
                    <p class="text-3xl font-black text-yellow-600 italic">${{ totalPrice }}</p>
                </div>
                <button
                    @click="proceedToPayment"
                    :disabled="!selectedSeats.length"
                    class="bg-yellow-600 hover:bg-yellow-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black px-12 py-4 rounded-xl transition-all shadow-lg shadow-yellow-600/20 uppercase tracking-widest"
                >
                    Continue →
                </button>
            </div>
        </div>

        <!-- Step 2: Payment -->
        <div v-else-if="session && step === 'payment'" class="max-w-2xl mx-auto space-y-10">
            <div class="text-center space-y-2">
                <p class="text-gray-500 text-xs font-bold uppercase tracking-widest">Step 2 of 2 — Choose Payment</p>
                <h1 class="text-4xl font-black uppercase italic tracking-tighter">{{ session.film?.title }}</h1>
            </div>

            <!-- Order Summary -->
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-4">
                <h3 class="font-black uppercase tracking-widest text-sm text-gray-400">Order Summary</h3>
                <div class="flex justify-between items-center">
                    <span class="text-gray-300">{{ session.room?.name }} — {{ formatDateTime(session.start_time) }}</span>
                </div>
                <div class="flex justify-between items-center" v-for="seat in selectedSeats" :key="seat.id">
                    <span class="text-gray-400 text-sm">Seat {{ seat.seat_number }}</span>
                    <span class="text-white font-bold">${{ session.price }}</span>
                </div>
                <div class="border-t border-white/10 pt-4 flex justify-between items-center">
                    <span class="font-bold uppercase tracking-widest">Total</span>
                    <span class="text-2xl font-black text-yellow-600">${{ totalPrice }}</span>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="space-y-4">
                <h3 class="font-black uppercase tracking-widest text-sm text-gray-400">Payment Method</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button
                        @click="paymentMethod = 'stripe'"
                        :class="paymentMethod === 'stripe' ? 'border-yellow-600 bg-yellow-600/10' : 'border-white/10 bg-white/5'"
                        class="p-6 rounded-2xl border-2 transition-all flex flex-col items-center gap-3 hover:border-yellow-600/60"
                    >
                        <span class="text-3xl">💳</span>
                        <span class="font-black uppercase tracking-widest text-sm">Credit Card</span>
                        <span class="text-xs text-gray-500">Stripe — Secure Payment</span>
                    </button>
                    <button
                        @click="paymentMethod = 'paypal'"
                        :class="paymentMethod === 'paypal' ? 'border-blue-500 bg-blue-500/10' : 'border-white/10 bg-white/5'"
                        class="p-6 rounded-2xl border-2 transition-all flex flex-col items-center gap-3 hover:border-blue-500/60"
                    >
                        <span class="text-3xl">🅿️</span>
                        <span class="font-black uppercase tracking-widest text-sm">PayPal</span>
                        <span class="text-xs text-gray-500">Fast & Secure</span>
                    </button>
                </div>
            </div>

            <!-- Error -->
            <div v-if="bookingError" class="bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-xl text-sm">
                {{ bookingError }}
            </div>

            <!-- Actions -->
            <div class="flex gap-4">
                <button
                    @click="step = 'seats'"
                    class="px-6 py-4 rounded-xl border border-white/10 hover:bg-white/10 font-bold uppercase tracking-widest transition-all"
                >
                    ← Back
                </button>
                <button
                    @click="confirmBooking"
                    :disabled="!paymentMethod || booking"
                    class="flex-1 bg-yellow-600 hover:bg-yellow-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black py-4 rounded-xl transition-all shadow-lg shadow-yellow-600/20 uppercase tracking-widest"
                >
                    {{ booking ? 'Processing...' : 'Confirm & Pay' }}
                </button>
            </div>
        </div>

        <!-- Success -->
        <div v-else-if="step === 'success'" class="max-w-lg mx-auto text-center space-y-8 py-16">
            <div class="text-7xl">🎬</div>
            <h1 class="text-5xl font-black uppercase italic tracking-tighter text-yellow-600">Booked!</h1>
            <p class="text-gray-400 text-lg">Your reservation is confirmed. Check your profile for ticket details.</p>
            <div class="flex gap-4 justify-center">
                <router-link to="/profile" class="px-8 py-4 bg-yellow-600 hover:bg-yellow-700 rounded-xl font-black uppercase tracking-widest transition-all">
                    View My Tickets
                </router-link>
                <router-link to="/" class="px-8 py-4 bg-white/5 hover:bg-white/10 rounded-xl font-black uppercase tracking-widest border border-white/10 transition-all">
                    Home
                </router-link>
            </div>
        </div>

        <div v-else-if="!loading" class="text-center py-20">
            <h2 class="text-3xl font-bold text-red-400">Session not found</h2>
            <router-link to="/" class="mt-4 inline-block text-yellow-600 hover:underline">Go Home</router-link>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../store/auth';

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();

const session = ref(null);
const seats = ref([]);
const loading = ref(true);
const booking = ref(false);
const selectedSeats = ref([]);
const occupiedSeatIds = ref([]);
const step = ref('seats'); // 'seats' | 'payment' | 'success'
const paymentMethod = ref(null);
const bookingError = ref('');

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

        // Fetch occupied seat IDs from existing reservations
        const resRes = await axios.get('/api/v1/reservation', {
            headers: { Authorization: `Bearer ${auth.token}` }
        });
        // But we need ALL reservations for this session, not just user's
        // We'll use a session-specific endpoint - fetch from sessions show which includes reservations
        // For now, mark user's own reserved seats + use info from session
        const existingForSession = (resRes.data.reservations || [])
            .filter(r => r.session_id === session.value.id)
            .map(r => r.seat_id);
        occupiedSeatIds.value = existingForSession;
    } catch (err) {
        console.error('Failed to fetch session:', err);
    } finally {
        loading.value = false;
    }
};

const formatDateTime = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' });
};

const confirmBooking = async () => {
    booking.value = true;
    bookingError.value = '';
    try {
        const createdReservationIds = [];
        for (const seat of selectedSeats.value) {
            const res = await axios.post('/api/v1/reservation', {
                session_id: session.value.id,
                seat_id: seat.id,
            }, {
                headers: { Authorization: `Bearer ${auth.token}` }
            });
            createdReservationIds.push(res.data.reservation_id);
        }

        // Initiate payment with the selected method
        if (paymentMethod.value === 'stripe') {
            const payRes = await axios.post('/api/v1/transactions/stripe', {
                reservation_id: createdReservationIds[0], // primary reservation
                amount: totalPrice.value,
            }, {
                headers: { Authorization: `Bearer ${auth.token}` }
            });
            if (payRes.data?.url) {
                window.location.href = payRes.data.url;
                return;
            }
        } else if (paymentMethod.value === 'paypal') {
            const payRes = await axios.post('/api/v1/transactions/paypal', {
                reservation_id: createdReservationIds[0],
                amount: totalPrice.value,
            }, {
                headers: { Authorization: `Bearer ${auth.token}` }
            });
            if (payRes.data?.approval_url) {
                window.location.href = payRes.data.approval_url;
                return;
            }
        }

        step.value = 'success';
    } catch (err) {
        bookingError.value = err.response?.data?.message || 'Booking failed. Please try again.';
    } finally {
        booking.value = false;
    }
};

onMounted(fetchSession);
</script>
