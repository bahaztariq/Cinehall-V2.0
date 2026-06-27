<template>
    <div class="bg-surface min-h-full">
        <!-- Modal -->
        <div v-if="modal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" @click.self="closeModal">
            <div role="dialog" aria-modal="true" aria-labelledby="room-modal-title" class="bg-surface-container border border-outline-variant rounded-3xl p-8 w-full max-w-md shadow-2xl space-y-6">
                <div class="flex justify-between items-center">
                    <h2 id="room-modal-title" class="font-serif text-2xl font-bold tracking-tight text-on-surface">{{ modal.mode === 'create' ? 'Add Room' : 'Edit Room' }}</h2>
                    <button @click="closeModal" aria-label="Close" class="w-11 h-11 flex items-center justify-center rounded-xl text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>
                <form @submit.prevent="saveRoom" class="space-y-4">
                    <div>
                        <label for="room-name" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Room Name</label>
                        <input id="room-name" v-model="form.name" type="text" required placeholder="Hall A"
                            class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-on-surface placeholder:text-on-surface-variant/60 focus:outline-none focus:border-primary transition-all" />
                    </div>
                    <div>
                        <label for="room-type" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Type</label>
                        <select id="room-type" v-model="form.type" class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-on-surface focus:outline-none focus:border-primary transition-all">
                            <option value="Normal">Normal</option>
                            <option value="VIP">VIP</option>
                        </select>
                    </div>
                    <div>
                        <label for="room-capacity" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Capacity (seats)</label>
                        <input id="room-capacity" v-model.number="form.capacity" type="number" min="1" max="500" required placeholder="100"
                            class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-on-surface placeholder:text-on-surface-variant/60 focus:outline-none focus:border-primary transition-all" />
                        <p class="text-xs text-on-surface-variant mt-1.5">This will auto-create the specified number of seats.</p>
                    </div>
                    <div v-if="formError" class="flex items-start gap-2 text-red-400 text-sm bg-red-500/10 border border-red-500/20 rounded-xl p-3">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
                        <span>{{ formError }}</span>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="closeModal" class="flex-1 py-3 rounded-xl border border-outline-variant text-on-surface hover:bg-surface-container-high font-bold uppercase tracking-widest text-sm transition-colors">Cancel</button>
                        <button type="submit" :disabled="saving" class="flex-1 blue-gradient hover:opacity-90 disabled:opacity-50 font-bold py-3 rounded-xl uppercase tracking-widest text-sm transition-opacity">
                            {{ saving ? 'Saving...' : modal.mode === 'create' ? 'Create' : 'Update' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
        </div>

        <div v-else class="space-y-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="space-y-1">
                    <p class="text-[0.7rem] font-bold text-primary uppercase tracking-[0.2em]">Auditoriums</p>
                    <h1 class="font-serif text-4xl font-bold tracking-tight text-on-surface">Room <span class="text-primary">Management</span></h1>
                </div>
                <button @click="openCreate" class="blue-gradient hover:opacity-90 font-bold px-6 py-3 rounded-xl transition-opacity shadow-lg shadow-black/40 uppercase tracking-widest text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                    Add Room
                </button>
            </div>

            <!-- Empty state -->
            <div v-if="!rooms.length" class="flex flex-col items-center justify-center text-center py-20 bg-surface-container border border-dashed border-outline-variant rounded-2xl">
                <div class="w-14 h-14 rounded-2xl bg-surface-container-high flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-on-surface-variant" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10v6m18-6v6M5 16v2m14-2v2M3 13h18M7 13v-3h10v3"/></svg>
                </div>
                <h3 class="font-serif text-xl text-on-surface mb-1">No rooms yet</h3>
                <p class="text-on-surface-variant text-sm">Create your first auditorium to start scheduling screenings.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="room in rooms" :key="room.id" class="bg-surface-container border border-outline-variant rounded-2xl p-6 hover:border-primary/50 transition-all flex flex-col justify-between gap-6">
                    <div>
                        <div class="flex justify-between items-start gap-3 mb-5">
                            <div class="flex items-center gap-3 min-w-0">
                                <div :class="room.type === 'VIP' ? 'bg-primary/15 text-primary' : 'bg-surface-container-high text-on-surface-variant'" class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10v6m18-6v6M5 16v2m14-2v2M3 13h18M7 13v-3h10v3"/></svg>
                                </div>
                                <h3 class="font-serif text-2xl font-bold text-on-surface truncate">{{ room.name }}</h3>
                            </div>
                            <span :class="room.type === 'VIP' ? 'bg-primary/15 text-primary border-primary/40' : 'bg-surface-container-high text-on-surface-variant border-outline-variant'" class="inline-flex items-center gap-1 px-3 py-1 rounded-full border text-[0.65rem] font-bold uppercase tracking-widest shrink-0">
                                <svg v-if="room.type === 'VIP'" class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2 2.9 6.3L22 9.3l-5 4.9 1.2 7L12 17.8 5.8 21.2 7 14.2l-5-4.9 7.1-1z"/></svg>
                                {{ room.type }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-surface-container-low border border-outline-variant p-4 rounded-xl">
                                <div class="flex items-center gap-1.5 text-[0.6rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm14 10v-2a4 4 0 0 0-3-3.87M16 3.13A4 4 0 0 1 16 11"/></svg>
                                    Capacity
                                </div>
                                <p class="font-serif text-2xl font-bold text-primary">{{ room.capacity }}</p>
                            </div>
                            <div class="bg-surface-container-low border border-outline-variant p-4 rounded-xl">
                                <div class="flex items-center gap-1.5 text-[0.6rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 10v8h16v-8M4 10V7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3M4 10h16M8 18v2m8-2v2"/></svg>
                                    Seats
                                </div>
                                <p class="font-serif text-2xl font-bold text-on-surface">{{ room.seats?.length || room.capacity }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="openEdit(room)" class="flex-1 py-3 bg-surface-container-high hover:bg-surface-container-highest text-on-surface rounded-xl font-bold text-xs uppercase tracking-widest border border-outline-variant transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            Edit
                        </button>
                        <button @click="deleteRoom(room.id)" class="flex-1 py-3 bg-red-500/15 hover:bg-red-600 text-red-400 hover:text-white rounded-xl font-bold text-xs uppercase tracking-widest border border-red-500/30 transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14"/></svg>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../store/auth';

const auth = useAuthStore();
const rooms = ref([]);
const loading = ref(true);
const saving = ref(false);
const formError = ref('');
const modal = reactive({ show: false, mode: 'create', room: null });
const form = reactive({ name: '', type: 'Normal', capacity: 100 });

const resetForm = () => { form.name = ''; form.type = 'Normal'; form.capacity = 100; formError.value = ''; };
const openCreate = () => { resetForm(); modal.room = null; modal.mode = 'create'; modal.show = true; };
const openEdit = (room) => {
    resetForm(); modal.room = room; modal.mode = 'edit'; modal.show = true;
    form.name = room.name; form.type = room.type || 'Normal'; form.capacity = room.capacity;
};
const closeModal = () => { modal.show = false; resetForm(); };

const saveRoom = async () => {
    saving.value = true; formError.value = '';
    try {
        if (modal.mode === 'create') {
            const res = await axios.post('/api/v1/rooms', form, { headers: { Authorization: `Bearer ${auth.token}` } });
            rooms.value.unshift(res.data.data || res.data);
        } else {
            const res = await axios.put(`/api/v1/rooms/${modal.room.id}`, form, { headers: { Authorization: `Bearer ${auth.token}` } });
            const idx = rooms.value.findIndex(r => r.id === modal.room.id);
            if (idx !== -1) rooms.value[idx] = res.data.data || res.data;
        }
        closeModal();
    } catch (err) {
        formError.value = err.response?.data?.message || JSON.stringify(err.response?.data?.errors || 'Save failed');
    } finally {
        saving.value = false;
    }
};

const deleteRoom = async (id) => {
    if (!confirm('Delete this room? All its seats will be removed.')) return;
    try {
        await axios.delete(`/api/v1/rooms/${id}`, { headers: { Authorization: `Bearer ${auth.token}` } });
        rooms.value = rooms.value.filter(r => r.id !== id);
    } catch (err) { alert(err.response?.data?.message || 'Delete failed'); }
};

onMounted(async () => {
    try {
        const res = await axios.get('/api/v1/rooms');
        rooms.value = res.data.rooms || res.data.data || [];
    } finally { loading.value = false; }
});
</script>
