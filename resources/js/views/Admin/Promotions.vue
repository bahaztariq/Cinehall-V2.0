<template>
    <div class="space-y-8 pb-16">
        <!-- Modal -->
        <div v-if="modal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" @click.self="closeModal">
            <div role="dialog" aria-modal="true" aria-labelledby="promo-modal-title" class="bg-surface-container border border-outline-variant rounded-3xl p-8 w-full max-w-lg shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="flex justify-between items-center">
                    <h2 id="promo-modal-title" class="font-serif text-2xl font-bold tracking-tight">{{ modal.mode === 'create' ? 'Add Promotion' : 'Edit Promotion' }}</h2>
                    <button @click="closeModal" aria-label="Close" class="w-11 h-11 flex items-center justify-center rounded-xl text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high transition-colors cursor-pointer">
                        <span class="material-symbols-outlined" aria-hidden="true">close</span>
                    </button>
                </div>

                <form @submit.prevent="savePromotion" class="space-y-5">
                    <div>
                        <label for="promo-name" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Name</label>
                        <input id="promo-name" v-model="form.name" type="text" required placeholder="Summer Special"
                            class="w-full glass-input rounded-xl px-4 py-3 text-on-surface text-sm" />
                    </div>
                    <div>
                        <label for="promo-discount" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Discount %</label>
                        <input id="promo-discount" v-model.number="form.discount_percentage" type="number" min="0" max="100" required placeholder="25"
                            class="w-full glass-input rounded-xl px-4 py-3 text-on-surface text-sm" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="promo-start" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Start Date</label>
                            <input id="promo-start" v-model="form.start_date" type="datetime-local" required
                                class="w-full glass-input rounded-xl px-4 py-3 text-on-surface text-sm" />
                        </div>
                        <div>
                            <label for="promo-end" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">End Date</label>
                            <input id="promo-end" v-model="form.end_date" type="datetime-local" required
                                class="w-full glass-input rounded-xl px-4 py-3 text-on-surface text-sm" />
                        </div>
                    </div>

                    <div v-if="formError" role="alert" class="flex items-start gap-2 text-red-400 text-sm bg-red-500/10 border border-red-500/20 rounded-xl p-3">
                        <span class="material-symbols-outlined text-[18px] mt-px shrink-0" aria-hidden="true">error</span>
                        <span>{{ formError }}</span>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="closeModal" class="flex-1 py-3.5 rounded-xl border border-outline-variant hover:bg-surface-container-high text-xs font-bold uppercase tracking-widest text-on-surface transition-colors cursor-pointer min-h-[44px]">Cancel</button>
                        <button type="submit" :disabled="saving" class="flex-1 blue-gradient hover:opacity-90 disabled:opacity-50 font-bold py-3.5 rounded-xl text-xs uppercase tracking-widest transition-opacity cursor-pointer min-h-[44px]">
                            {{ saving ? 'Saving...' : modal.mode === 'create' ? 'Create' : 'Update' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="space-y-1">
                <p class="text-[0.7rem] font-bold text-primary uppercase tracking-[0.2em]">Marketing</p>
                <h1 class="font-serif text-4xl font-bold tracking-tight">Promotion <span class="text-primary">Management</span></h1>
            </div>
            <button @click="openCreate" class="flex items-center justify-center gap-2 blue-gradient hover:opacity-90 font-bold px-6 py-3.5 rounded-xl transition-opacity shadow-lg shadow-primary/20 uppercase tracking-widest text-xs cursor-pointer min-h-[44px]">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">add</span>
                Add Promotion
            </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
        </div>

        <!-- Empty -->
        <div v-else-if="!promotions.length" class="flex flex-col items-center justify-center text-center py-20 bg-surface-container border border-dashed border-outline-variant rounded-2xl">
            <div class="w-14 h-14 rounded-2xl bg-surface-container-high flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-[28px] text-on-surface-variant" aria-hidden="true">local_offer</span>
            </div>
            <h3 class="font-serif text-xl mb-1">No promotions yet</h3>
            <p class="text-on-surface-variant text-sm">Create a promotion to offer discounts to your customers.</p>
        </div>

        <!-- Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="promo in promotions" :key="promo.id" class="bg-surface-container border border-outline-variant rounded-2xl p-6 flex flex-col gap-4 hover:border-primary/40 transition-colors">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="font-bold text-on-surface text-lg leading-tight">{{ promo.name }}</h3>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.65rem] font-bold uppercase tracking-wider border shrink-0" :class="statusOf(promo).class">
                        {{ statusOf(promo).label }}
                    </span>
                </div>
                <p class="font-serif text-4xl font-bold text-primary">{{ promo.discount_percentage }}% <span class="text-xl">OFF</span></p>
                <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">date_range</span>
                    <span>{{ formatDate(promo.start_date) }} — {{ formatDate(promo.end_date) }}</span>
                </div>
                <div class="flex gap-3 pt-2 mt-auto">
                    <button @click="openEdit(promo)" class="flex-1 inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl border border-outline-variant hover:bg-surface-container-high text-on-surface text-[0.7rem] font-bold uppercase tracking-wider transition-colors cursor-pointer min-h-[44px]">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">edit</span> Edit
                    </button>
                    <button @click="deletePromotion(promo)" class="flex-1 inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl border border-red-500/30 text-red-400 hover:bg-red-500/12 hover:border-red-500/50 text-[0.7rem] font-bold uppercase tracking-wider transition-colors cursor-pointer min-h-[44px]">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">delete</span> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const promotions = ref([]);
const loading = ref(true);
const saving = ref(false);
const formError = ref('');

const modal = reactive({ show: false, mode: 'create', id: null });
const form = reactive({ name: '', discount_percentage: 0, start_date: '', end_date: '' });

const statusOf = (promo) => {
    const now = new Date();
    const start = new Date(promo.start_date);
    const end = new Date(promo.end_date);
    if (now < start) return { label: 'Upcoming', class: 'bg-blue-500/15 text-blue-400 border-blue-500/30' };
    if (now > end) return { label: 'Expired', class: 'bg-red-500/15 text-red-400 border-red-500/30' };
    return { label: 'Active', class: 'bg-green-500/15 text-green-400 border-green-500/30' };
};

const formatDate = (dt) => {
    if (!dt) return '—';
    return new Date(dt).toLocaleDateString(undefined, { dateStyle: 'medium' });
};

// Convert ISO/server date to value usable by datetime-local input
const toInputValue = (dt) => {
    if (!dt) return '';
    const d = new Date(dt);
    if (isNaN(d.getTime())) return '';
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

const resetForm = () => {
    form.name = '';
    form.discount_percentage = 0;
    form.start_date = '';
    form.end_date = '';
    formError.value = '';
};

const openCreate = () => {
    resetForm();
    modal.mode = 'create';
    modal.id = null;
    modal.show = true;
};

const openEdit = (promo) => {
    resetForm();
    modal.mode = 'edit';
    modal.id = promo.id;
    form.name = promo.name;
    form.discount_percentage = promo.discount_percentage;
    form.start_date = toInputValue(promo.start_date);
    form.end_date = toInputValue(promo.end_date);
    modal.show = true;
};

const closeModal = () => {
    modal.show = false;
    resetForm();
};

const fetchPromotions = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/api/v1/promotions');
        promotions.value = res.data.promotions || [];
    } catch (err) {
        console.error('Failed to fetch promotions:', err);
        promotions.value = [];
    } finally {
        loading.value = false;
    }
};

const savePromotion = async () => {
    saving.value = true;
    formError.value = '';
    try {
        const payload = {
            name: form.name,
            discount_percentage: form.discount_percentage,
            start_date: form.start_date,
            end_date: form.end_date,
        };
        if (modal.mode === 'create') {
            await axios.post('/api/v1/promotions', payload);
        } else {
            await axios.put(`/api/v1/promotions/${modal.id}`, payload);
        }
        await fetchPromotions();
        closeModal();
    } catch (err) {
        const errors = err.response?.data?.errors;
        formError.value = errors ? Object.values(errors).flat().join(' ') : (err.response?.data?.message || 'Save failed');
    } finally {
        saving.value = false;
    }
};

const deletePromotion = async (promo) => {
    if (!confirm(`Delete promotion "${promo.name}"? This cannot be undone.`)) return;
    try {
        await axios.delete(`/api/v1/promotions/${promo.id}`);
        promotions.value = promotions.value.filter(p => p.id !== promo.id);
    } catch (err) {
        alert(err.response?.data?.message || 'Delete failed');
    }
};

onMounted(fetchPromotions);
</script>
