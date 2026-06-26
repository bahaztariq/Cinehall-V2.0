<template>
    <div class="bg-surface min-h-full">
        <!-- Modal -->
        <div v-if="modal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" @click.self="closeModal">
            <div role="dialog" aria-modal="true" aria-labelledby="session-modal-title" class="bg-surface-container border border-outline-variant rounded-3xl p-8 w-full max-w-lg shadow-2xl space-y-6">
                <div class="flex justify-between items-center">
                    <h2 id="session-modal-title" class="font-serif text-2xl font-bold tracking-tight text-on-surface">{{ modal.mode === 'create' ? 'Add Session' : 'Edit Session' }}</h2>
                    <button @click="closeModal" aria-label="Close" class="w-11 h-11 flex items-center justify-center rounded-xl text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>
                <form @submit.prevent="saveSession" class="space-y-4">
                    <div>
                        <label for="session-film" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Film</label>
                        <select id="session-film" v-model.number="form.film_id" required class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-on-surface focus:outline-none focus:border-primary transition-all">
                            <option value="" disabled>Select a film</option>
                            <option v-for="film in films" :key="film.id" :value="film.id">{{ film.title }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="session-room" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Room</label>
                        <select id="session-room" v-model.number="form.room_id" required class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-on-surface focus:outline-none focus:border-primary transition-all">
                            <option value="" disabled>Select a room</option>
                            <option v-for="room in rooms" :key="room.id" :value="room.id">{{ room.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="session-language" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Language</label>
                        <select id="session-language" v-model="form.language" required class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-on-surface focus:outline-none focus:border-primary transition-all">
                            <option value="english">English</option>
                            <option value="french">French</option>
                            <option value="arabic">Arabic</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="session-start" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Start Time</label>
                            <input id="session-start" v-model="form.start_time" type="datetime-local" required class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-on-surface focus:outline-none focus:border-primary transition-all" />
                        </div>
                        <div>
                            <label for="session-end" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">End Time</label>
                            <input id="session-end" v-model="form.end_time" type="datetime-local" required class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-on-surface focus:outline-none focus:border-primary transition-all" />
                        </div>
                    </div>
                    <div>
                        <label for="session-price" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Price ($)</label>
                        <input id="session-price" v-model.number="form.price" type="number" step="0.01" min="0" required placeholder="12.50" class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-on-surface placeholder:text-on-surface-variant/60 focus:outline-none focus:border-primary transition-all" />
                    </div>
                    <div v-if="formError" class="flex items-start gap-2 text-red-400 text-sm bg-red-500/10 border border-red-500/20 rounded-xl p-3">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
                        <span>{{ formError }}</span>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="closeModal" class="flex-1 py-3 rounded-xl border border-outline-variant text-on-surface hover:bg-surface-container-high font-bold uppercase tracking-widest text-sm transition-colors">Cancel</button>
                        <button type="submit" :disabled="saving" class="flex-1 gold-gradient hover:opacity-90 disabled:opacity-50 font-bold py-3 rounded-xl uppercase tracking-widest text-sm transition-opacity">
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
                    <p class="text-[0.7rem] font-bold text-primary uppercase tracking-[0.2em]">Programming</p>
                    <h1 class="font-serif text-4xl font-bold tracking-tight text-on-surface">Session <span class="text-primary">Management</span></h1>
                </div>
                <button @click="openCreate" class="gold-gradient hover:opacity-90 font-bold px-6 py-3 rounded-xl transition-opacity shadow-lg shadow-black/40 uppercase tracking-widest text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                    Add Session
                </button>
            </div>

            <!-- Empty state -->
            <div v-if="!sessions.length" class="flex flex-col items-center justify-center text-center py-20 bg-surface-container border border-dashed border-outline-variant rounded-2xl">
                <div class="w-14 h-14 rounded-2xl bg-surface-container-high flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-on-surface-variant" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
                </div>
                <h3 class="font-serif text-xl text-on-surface mb-1">No sessions scheduled</h3>
                <p class="text-on-surface-variant text-sm">Add your first screening to populate the schedule.</p>
            </div>

            <!-- Sessions table -->
            <div v-else class="bg-surface-container border border-outline-variant rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left border-collapse">
                        <thead class="bg-surface-container-high">
                            <tr class="text-[0.65rem] font-bold text-on-surface-variant uppercase tracking-widest">
                                <th scope="col" class="px-6 py-4">Film</th>
                                <th scope="col" class="px-6 py-4">Date &amp; Time</th>
                                <th scope="col" class="px-6 py-4">Room</th>
                                <th scope="col" class="px-6 py-4">Language</th>
                                <th scope="col" class="px-6 py-4 text-right">Price</th>
                                <th scope="col" class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="session in sessions" :key="session.id" class="border-t border-outline-variant hover:bg-surface-container-high/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-11 h-16 rounded-lg overflow-hidden border border-outline-variant shrink-0 bg-surface-container-low">
                                            <img :src="session.film?.image?.path || 'https://via.placeholder.com/100x150'" :alt="session.film?.title" class="w-full h-full object-cover" />
                                        </div>
                                        <span class="font-semibold text-on-surface">{{ session.film?.title }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 font-semibold text-on-surface">
                                        <svg class="w-4 h-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>
                                        {{ formatDateTime(session.start_time) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-on-surface-variant">{{ session.room?.name }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-secondary-container/40 border border-primary/20 text-xs font-bold uppercase tracking-wider text-primary">{{ session.language }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-serif text-lg font-bold text-primary">${{ session.price }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEdit(session)" aria-label="Edit session" class="w-11 h-11 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high transition-colors">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        </button>
                                        <button @click="deleteSession(session.id)" aria-label="Delete session" class="w-11 h-11 flex items-center justify-center rounded-lg bg-red-500/15 border border-red-500/30 text-red-400 hover:bg-red-600 hover:text-white transition-colors">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
const sessions = ref([]);
const films = ref([]);
const rooms = ref([]);
const loading = ref(true);
const saving = ref(false);
const formError = ref('');

const modal = reactive({ show: false, mode: 'create', session: null });
const form = reactive({ film_id: '', room_id: '', language: 'english', start_time: '', end_time: '', price: '' });

const resetForm = () => {
    form.film_id = ''; form.room_id = ''; form.language = 'english';
    form.start_time = ''; form.end_time = ''; form.price = '';
    formError.value = '';
};

const toLocalInput = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
};

const openCreate = () => { resetForm(); modal.session = null; modal.mode = 'create'; modal.show = true; };
const openEdit = (session) => {
    resetForm(); modal.session = session; modal.mode = 'edit'; modal.show = true;
    form.film_id = session.film_id; form.room_id = session.room_id;
    form.language = session.language; form.price = session.price;
    form.start_time = toLocalInput(session.start_time);
    form.end_time = toLocalInput(session.end_time);
};
const closeModal = () => { modal.show = false; resetForm(); };

const formatDateTime = (d) => d ? new Date(d).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' }) : '';

const saveSession = async () => {
    saving.value = true; formError.value = '';
    try {
        if (modal.mode === 'create') {
            const res = await axios.post('/api/v1/sessions', form, { headers: { Authorization: `Bearer ${auth.token}` } });
            sessions.value.unshift(res.data.data);
        } else {
            const res = await axios.put(`/api/v1/sessions/${modal.session.id}`, form, { headers: { Authorization: `Bearer ${auth.token}` } });
            const idx = sessions.value.findIndex(s => s.id === modal.session.id);
            if (idx !== -1) sessions.value[idx] = res.data.data;
        }
        closeModal();
    } catch (err) {
        formError.value = err.response?.data?.message || JSON.stringify(err.response?.data?.errors || 'Save failed');
    } finally {
        saving.value = false;
    }
};

const deleteSession = async (id) => {
    if (!confirm('Delete this session?')) return;
    try {
        await axios.delete(`/api/v1/sessions/${id}`, { headers: { Authorization: `Bearer ${auth.token}` } });
        sessions.value = sessions.value.filter(s => s.id !== id);
    } catch (err) { alert(err.response?.data?.message || 'Delete failed'); }
};

onMounted(async () => {
    try {
        const [sRes, fRes, rRes] = await Promise.all([
            axios.get('/api/v1/sessions'),
            axios.get('/api/v1/films'),
            axios.get('/api/v1/rooms'),
        ]);
        sessions.value = sRes.data.film_sessions || [];
        films.value = fRes.data.data || fRes.data;
        rooms.value = rRes.data.rooms || rRes.data.data || [];
    } finally { loading.value = false; }
});
</script>
