<template>
    <div>
        <!-- Modal -->
        <div v-if="modal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" @click.self="closeModal">
            <div class="bg-[#111] border border-white/10 rounded-2xl p-8 w-full max-w-md shadow-2xl space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-black uppercase italic tracking-tighter">{{ modal.mode === 'create' ? 'Add Room' : 'Edit Room' }}</h2>
                    <button @click="closeModal" class="text-gray-500 hover:text-white text-xl">✕</button>
                </div>
                <form @submit.prevent="saveRoom" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Room Name</label>
                        <input v-model="form.name" type="text" required placeholder="Hall A"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-600 transition-all" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Type</label>
                        <select v-model="form.type" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-600 transition-all">
                            <option value="Normal">Normal</option>
                            <option value="VIP">VIP</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Capacity (seats)</label>
                        <input v-model.number="form.capacity" type="number" min="1" max="500" required placeholder="100"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-600 transition-all" />
                        <p class="text-xs text-gray-600 mt-1">This will auto-create the specified number of seats.</p>
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
                <h1 class="text-4xl font-black uppercase italic tracking-tighter">Room <span class="text-yellow-600">Management</span></h1>
                <button @click="openCreate" class="bg-yellow-600 hover:bg-yellow-700 text-white font-black px-6 py-3 rounded-xl transition-all shadow-lg shadow-yellow-600/20 uppercase tracking-widest text-sm">
                    + Add Room
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="room in rooms" :key="room.id" class="bg-white/5 border border-white/10 p-6 rounded-2xl hover:border-yellow-600/50 transition-all flex flex-col justify-between gap-6">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-2xl font-black text-white italic uppercase">{{ room.name }}</h3>
                            <span :class="room.type === 'VIP' ? 'bg-yellow-600/10 text-yellow-600 border-yellow-600/30' : 'bg-white/5 text-gray-400 border-white/10'" class="px-3 py-1 rounded-full border text-xs font-bold uppercase tracking-widest">{{ room.type }}</span>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex-1 bg-black/20 p-3 rounded-xl text-center">
                                <p class="text-[0.65rem] font-bold text-gray-500 uppercase tracking-widest mb-1">Capacity</p>
                                <p class="text-2xl font-black text-yellow-600 italic">{{ room.capacity }}</p>
                            </div>
                            <div class="flex-1 bg-black/20 p-3 rounded-xl text-center">
                                <p class="text-[0.65rem] font-bold text-gray-500 uppercase tracking-widest mb-1">Seats</p>
                                <p class="text-2xl font-black text-white italic">{{ room.seats?.length || room.capacity }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="openEdit(room)" class="flex-1 py-2 bg-white/5 hover:bg-white/10 rounded-lg font-bold text-xs uppercase tracking-widest border border-white/10 transition-all">Edit</button>
                        <button @click="deleteRoom(room.id)" class="flex-1 py-2 bg-red-600/10 hover:bg-red-600 text-white rounded-lg font-bold text-xs uppercase tracking-widest border border-red-600/20 transition-all">Delete</button>
                    </div>
                </div>
                <div v-if="!rooms.length" class="col-span-full text-center py-16 border border-white/10 border-dashed rounded-2xl text-gray-500 italic">
                    No rooms found. Add one above.
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
