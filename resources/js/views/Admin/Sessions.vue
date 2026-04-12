<template>
    <div>
        <!-- Modal -->
        <div v-if="modal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" @click.self="closeModal">
            <div class="bg-[#111] border border-white/10 rounded-2xl p-8 w-full max-w-lg shadow-2xl space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-black uppercase italic tracking-tighter">{{ modal.mode === 'create' ? 'Add Session' : 'Edit Session' }}</h2>
                    <button @click="closeModal" class="text-gray-500 hover:text-white text-xl">✕</button>
                </div>
                <form @submit.prevent="saveSession" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Film</label>
                        <select v-model.number="form.film_id" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-600 transition-all">
                            <option value="" disabled>Select a film</option>
                            <option v-for="film in films" :key="film.id" :value="film.id">{{ film.title }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Room</label>
                        <select v-model.number="form.room_id" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-600 transition-all">
                            <option value="" disabled>Select a room</option>
                            <option v-for="room in rooms" :key="room.id" :value="room.id">{{ room.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Language</label>
                        <select v-model="form.language" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-600 transition-all">
                            <option value="english">English</option>
                            <option value="french">French</option>
                            <option value="arabic">Arabic</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Start Time</label>
                            <input v-model="form.start_time" type="datetime-local" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-600 transition-all" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">End Time</label>
                            <input v-model="form.end_time" type="datetime-local" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-600 transition-all" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Price ($)</label>
                        <input v-model.number="form.price" type="number" step="0.01" min="0" required placeholder="12.50" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-600 transition-all" />
                    </div>
                    <div v-if="formError" class="text-red-400 text-sm bg-red-500/10 border border-red-500/20 rounded-xl p-3">{{ formError }}</div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="closeModal" class="flex-1 py-3 rounded-xl border border-white/10 hover:bg-white/5 font-bold uppercase tracking-widest text-sm">Cancel</button>
                        <button type="submit" :disabled="saving" class="flex-1 bg-yellow-600 hover:bg-yellow-700 disabled:opacity-50 text-white font-black py-3 rounded-xl uppercase tracking-widest text-sm">
                            {{ saving ? 'Saving...' : modal.mode === 'create' ? 'Create' : 'Update' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-yellow-600"></div>
        </div>

        <div v-else class="space-y-8">
            <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                <h1 class="text-4xl font-black uppercase italic tracking-tighter">Session <span class="text-yellow-600">Management</span></h1>
                <button @click="openCreate" class="bg-yellow-600 hover:bg-yellow-700 text-white font-black px-6 py-3 rounded-xl transition-all shadow-lg shadow-yellow-600/20 uppercase tracking-widest text-sm">
                    + Add Session
                </button>
            </div>

            <div class="grid gap-4">
                <div v-for="session in sessions" :key="session.id" class="bg-white/5 border border-white/10 p-6 rounded-2xl flex flex-col md:flex-row justify-between items-center gap-6 hover:border-white/20 transition-all">
                    <div class="flex items-center gap-6">
                        <div class="w-12 h-16 rounded overflow-hidden border border-white/10 shrink-0">
                            <img :src="session.film?.image?.path || 'https://via.placeholder.com/100x150'" class="w-full h-full object-cover" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">{{ session.film?.title }}</h3>
                            <p class="text-gray-500 text-sm">{{ formatDateTime(session.start_time) }} • {{ session.room?.name }} • ${{ session.price }}</p>
                            <span class="text-xs font-bold uppercase text-yellow-600/80">{{ session.language }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="openEdit(session)" class="px-4 py-2 rounded-lg border border-white/10 hover:bg-white/10 transition-all font-bold text-xs uppercase tracking-widest">Edit</button>
                        <button @click="deleteSession(session.id)" class="px-4 py-2 rounded-lg border border-red-600/30 hover:bg-red-600/20 text-red-500 transition-all font-bold text-xs uppercase tracking-widest">Delete</button>
                    </div>
                </div>
                <div v-if="!sessions.length" class="text-center py-16 border border-white/10 border-dashed rounded-2xl text-gray-500 italic">
                    No sessions found. Add one above.
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
