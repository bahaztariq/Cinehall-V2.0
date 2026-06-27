<template>
    <div class="booking-result min-h-screen flex items-center justify-center px-5 py-16 bg-surface text-on-surface">
        <div class="w-full max-w-lg">

            <!-- ════════ Cancelled ════════ -->
            <div v-if="cancelled" class="ticket-card relative bg-surface-container rounded-3xl border border-outline-variant shadow-[0_10px_50px_rgba(0,0,0,0.55)] overflow-hidden text-center">
                <div class="p-10 space-y-5">
                    <div class="w-20 h-20 bg-surface-container-highest rounded-full flex items-center justify-center mx-auto">
                        <span class="material-symbols-outlined text-[40px] text-on-surface-variant" aria-hidden="true">do_not_disturb_on</span>
                    </div>
                    <div class="space-y-2">
                        <p class="text-[10px] text-on-surface-variant font-bold uppercase tracking-[0.3em]">Cancelled</p>
                        <h1 ref="heading" tabindex="-1" class="font-serif text-3xl sm:text-4xl font-bold leading-tight outline-none">Payment cancelled</h1>
                        <p class="text-on-surface-variant text-sm leading-relaxed max-w-sm mx-auto">
                            No charge was made. Your seats were not booked — feel free to try again whenever you're ready.
                        </p>
                    </div>
                </div>
                <div class="ticket-divider" aria-hidden="true"></div>
                <div class="px-10 py-7 bg-surface-container-low flex justify-center">
                    <router-link to="/" class="px-8 min-h-[48px] py-4 blue-gradient rounded-xl font-bold uppercase tracking-[0.18em] text-xs shadow-lg shadow-primary/25 transition-all hover:brightness-105 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">home</span>
                        Back to home
                    </router-link>
                </div>
            </div>

            <!-- ════════ Loading / Verifying ════════ -->
            <div v-else-if="state === 'loading'" class="ticket-card relative bg-surface-container rounded-3xl border border-outline-variant shadow-[0_10px_50px_rgba(0,0,0,0.55)] overflow-hidden text-center">
                <div class="p-12 flex flex-col items-center gap-6">
                    <div class="animate-spin rounded-full h-12 w-12 border-2 border-primary border-t-transparent" role="status" aria-label="Verifying your payment"></div>
                    <div class="space-y-2">
                        <h1 ref="heading" tabindex="-1" class="font-serif text-2xl sm:text-3xl font-bold outline-none">Confirming your booking</h1>
                        <p class="text-on-surface-variant text-sm leading-relaxed max-w-xs mx-auto">
                            Please hold on while we verify your payment. Do not close this window.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ════════ Success ════════ -->
            <div v-else-if="state === 'success'" class="ticket-card relative bg-surface-container rounded-3xl border border-outline-variant shadow-[0_10px_50px_rgba(0,0,0,0.55)] overflow-hidden text-center">
                <div class="p-10 space-y-5">
                    <div class="success-check w-20 h-20 blue-gradient rounded-full flex items-center justify-center mx-auto shadow-lg shadow-primary/30">
                        <span class="material-symbols-outlined text-[40px]" aria-hidden="true">check</span>
                    </div>
                    <div class="space-y-2">
                        <p class="text-[10px] text-primary font-bold uppercase tracking-[0.3em]">Confirmed</p>
                        <h1 ref="heading" tabindex="-1" class="font-serif text-3xl sm:text-4xl font-bold leading-tight outline-none">Booking confirmed</h1>
                        <p class="text-on-surface-variant text-sm leading-relaxed max-w-sm mx-auto">
                            Your payment went through and your seats are reserved. Download your passes below or find them anytime in your profile.
                        </p>
                    </div>

                    <!-- Per-ticket download buttons -->
                    <div v-if="ticketIds.length" class="flex flex-col gap-3 pt-2">
                        <button
                            v-for="(id, i) in ticketIds"
                            :key="id"
                            @click="downloadTicket(id)"
                            :disabled="downloading.includes(id)"
                            class="w-full blue-gradient min-h-[48px] py-4 rounded-xl font-bold uppercase tracking-[0.18em] text-xs shadow-lg transition-all hover:brightness-105 active:scale-[0.98] cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            <span v-if="downloading.includes(id)" class="animate-spin rounded-full h-4 w-4 border-2 border-on-primary border-t-transparent" aria-hidden="true"></span>
                            <span v-else class="material-symbols-outlined text-[18px]" aria-hidden="true">download</span>
                            {{ ticketIds.length > 1 ? `Download ticket ${i + 1}` : 'Download ticket' }}
                        </button>
                    </div>

                    <div v-if="downloadError" role="alert" class="flex items-center gap-2.5 bg-red-500/10 border border-red-500/30 text-red-400 p-3.5 rounded-xl text-xs font-bold text-left">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">error</span>
                        {{ downloadError }}
                    </div>
                </div>
                <div class="ticket-divider" aria-hidden="true"></div>
                <div class="px-8 py-6 bg-surface-container-low flex flex-col sm:flex-row gap-3 justify-center">
                    <router-link to="/profile" class="px-7 min-h-[48px] py-4 bg-surface-container border border-outline-variant hover:border-primary hover:text-primary text-on-surface rounded-xl font-bold uppercase tracking-[0.18em] text-xs transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">local_activity</span>
                        My tickets
                    </router-link>
                    <router-link to="/" class="px-7 min-h-[48px] py-4 bg-surface-container border border-outline-variant hover:border-primary hover:text-primary text-on-surface rounded-xl font-bold uppercase tracking-[0.18em] text-xs transition-colors flex items-center justify-center">
                        Return home
                    </router-link>
                </div>
            </div>

            <!-- ════════ Error ════════ -->
            <div v-else class="ticket-card relative bg-surface-container rounded-3xl border border-outline-variant shadow-[0_10px_50px_rgba(0,0,0,0.55)] overflow-hidden text-center">
                <div class="p-10 space-y-5">
                    <div class="w-20 h-20 bg-red-500/15 rounded-full flex items-center justify-center mx-auto">
                        <span class="material-symbols-outlined text-[40px] text-red-400" aria-hidden="true">error</span>
                    </div>
                    <div class="space-y-2">
                        <p class="text-[10px] text-red-400 font-bold uppercase tracking-[0.3em]">Payment problem</p>
                        <h1 ref="heading" tabindex="-1" class="font-serif text-3xl sm:text-4xl font-bold leading-tight outline-none">We couldn't confirm your booking</h1>
                        <div role="alert" class="text-on-surface-variant text-sm leading-relaxed max-w-sm mx-auto">
                            {{ errorMessage }}
                        </div>
                    </div>
                </div>
                <div class="ticket-divider" aria-hidden="true"></div>
                <div class="px-8 py-6 bg-surface-container-low flex flex-col sm:flex-row gap-3 justify-center">
                    <router-link to="/" class="px-7 min-h-[48px] py-4 blue-gradient rounded-xl font-bold uppercase tracking-[0.18em] text-xs shadow-lg shadow-primary/25 transition-all hover:brightness-105 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">home</span>
                        Back to home
                    </router-link>
                    <router-link to="/profile" class="px-7 min-h-[48px] py-4 bg-surface-container border border-outline-variant hover:border-primary hover:text-primary text-on-surface rounded-xl font-bold uppercase tracking-[0.18em] text-xs transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">local_activity</span>
                        My tickets
                    </router-link>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();

const cancelled = ref(route.path === '/booking/cancel');
const state = ref('loading'); // 'loading' | 'success' | 'error'
const errorMessage = ref('Something went wrong while verifying your payment.');
const reservationIds = ref([]);
const ticketIds = ref([]);
const downloading = ref([]);
const downloadError = ref('');
const heading = ref(null);

const focusHeading = () => {
    nextTick(() => heading.value?.focus());
};

const verify = async () => {
    const provider = route.query.provider;
    try {
        let res;
        if (provider === 'stripe') {
            res = await axios.post('/api/v1/transactions/stripe/verify', { cs: route.query.cs });
        } else if (provider === 'paypal') {
            res = await axios.post('/api/v1/transactions/paypal/capture', { token: route.query.token });
        } else {
            state.value = 'error';
            errorMessage.value = 'Unknown payment provider. We could not verify this booking.';
            focusHeading();
            return;
        }

        const data = res.data || {};
        if (data.status === 'success') {
            reservationIds.value = data.reservation_ids || [];
            ticketIds.value = data.ticket_ids || [];
            state.value = 'success';
        } else {
            state.value = 'error';
            errorMessage.value = data.error || 'Your payment could not be confirmed.';
        }
    } catch (err) {
        state.value = 'error';
        errorMessage.value = err.response?.data?.error || err.response?.data?.message || 'Your payment could not be confirmed. If you were charged, please contact support.';
    } finally {
        focusHeading();
    }
};

// Create an object URL from a blob, trigger an <a download>, then clean up.
const downloadTicket = async (ticketId) => {
    downloadError.value = '';
    downloading.value.push(ticketId);
    try {
        const res = await axios.get(`/api/v1/tickets/${ticketId}/download`, { responseType: 'blob' });
        const url = window.URL.createObjectURL(res.data);
        const link = document.createElement('a');
        link.href = url;
        link.download = `ticket-${ticketId}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    } catch (err) {
        downloadError.value = 'Could not download this ticket. Please try again from your profile.';
    } finally {
        downloading.value = downloading.value.filter(id => id !== ticketId);
    }
};

onMounted(() => {
    if (cancelled.value) {
        focusHeading();
        return;
    }
    verify();
});
</script>

<style scoped>
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

.success-check {
    animation: check-in 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes check-in {
    0% { transform: scale(0); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

@media (prefers-reduced-motion: reduce) {
    .success-check {
        animation: none;
    }
}
</style>
