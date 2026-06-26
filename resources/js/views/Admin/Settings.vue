<template>
    <div class="space-y-8 pb-16 max-w-3xl">
        <!-- Header -->
        <div class="space-y-1">
            <p class="text-[0.7rem] font-bold text-primary uppercase tracking-[0.2em]">Configuration</p>
            <h1 class="font-serif text-4xl font-bold tracking-tight">Cinema <span class="text-primary">Settings</span></h1>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
        </div>

        <!-- Form card -->
        <form v-else @submit.prevent="saveSettings" class="bg-surface-container border border-outline-variant rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="set-cinema-name" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Cinema Name</label>
                    <input id="set-cinema-name" v-model="settings.cinema_name" type="text" placeholder="Cinehall"
                        class="w-full glass-input rounded-xl px-4 py-3 text-on-surface text-sm" />
                </div>
                <div>
                    <label for="set-support-email" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Support Email</label>
                    <input id="set-support-email" v-model="settings.support_email" type="email" placeholder="support@cinehall.com"
                        class="w-full glass-input rounded-xl px-4 py-3 text-on-surface text-sm" />
                </div>
                <div>
                    <label for="set-currency" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Currency</label>
                    <input id="set-currency" v-model="settings.currency" type="text" placeholder="USD"
                        class="w-full glass-input rounded-xl px-4 py-3 text-on-surface text-sm" />
                </div>
                <div>
                    <label for="set-hold" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Booking Hold (minutes)</label>
                    <input id="set-hold" v-model.number="settings.booking_hold_minutes" type="number" min="0" placeholder="15"
                        class="w-full glass-input rounded-xl px-4 py-3 text-on-surface text-sm" />
                </div>
                <div>
                    <label for="set-default-price" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Default Price</label>
                    <input id="set-default-price" v-model.number="settings.default_price" type="number" min="0" step="0.01" placeholder="12.00"
                        class="w-full glass-input rounded-xl px-4 py-3 text-on-surface text-sm" />
                </div>
                <div>
                    <label for="set-vip-price" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">VIP Price</label>
                    <input id="set-vip-price" v-model.number="settings.vip_price" type="number" min="0" step="0.01" placeholder="20.00"
                        class="w-full glass-input rounded-xl px-4 py-3 text-on-surface text-sm" />
                </div>
            </div>

            <!-- Status messages -->
            <p v-if="saved" role="status" class="flex items-center gap-2 text-green-400 text-sm bg-green-500/10 border border-green-500/20 rounded-xl p-3">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">check_circle</span>
                Settings saved successfully.
            </p>
            <p v-if="error" role="alert" class="flex items-center gap-2 text-red-400 text-sm bg-red-500/10 border border-red-500/20 rounded-xl p-3">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">error</span>
                {{ error }}
            </p>

            <div class="flex justify-end pt-2 border-t border-outline-variant">
                <button type="submit" :disabled="saving" class="mt-6 inline-flex items-center justify-center gap-2 gold-gradient hover:opacity-90 disabled:opacity-50 font-bold px-8 py-3.5 rounded-xl text-xs uppercase tracking-widest transition-opacity cursor-pointer min-h-[44px]">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">save</span>
                    {{ saving ? 'Saving...' : 'Save Changes' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(true);
const saving = ref(false);
const saved = ref(false);
const error = ref('');

const settings = reactive({
    cinema_name: '',
    support_email: '',
    currency: '',
    default_price: 0,
    vip_price: 0,
    booking_hold_minutes: 0,
});

const fetchSettings = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/api/v1/admin/settings');
        const data = res.data.settings || {};
        Object.keys(settings).forEach((key) => {
            if (data[key] !== undefined && data[key] !== null) settings[key] = data[key];
        });
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to load settings';
    } finally {
        loading.value = false;
    }
};

const saveSettings = async () => {
    saving.value = true;
    saved.value = false;
    error.value = '';
    try {
        const res = await axios.put('/api/v1/admin/settings', { settings: { ...settings } });
        const data = res.data.settings || {};
        Object.keys(settings).forEach((key) => {
            if (data[key] !== undefined && data[key] !== null) settings[key] = data[key];
        });
        saved.value = true;
        setTimeout(() => { saved.value = false; }, 3000);
    } catch (err) {
        const errors = err.response?.data?.errors;
        error.value = errors ? Object.values(errors).flat().join(' ') : (err.response?.data?.message || 'Save failed');
    } finally {
        saving.value = false;
    }
};

onMounted(fetchSettings);
</script>
