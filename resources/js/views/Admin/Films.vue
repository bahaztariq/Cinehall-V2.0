<template>
    <div>
        <!-- Modal Overlay -->
        <div v-if="modal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" @click.self="closeModal">
            <div class="bg-[#111] border border-white/10 rounded-2xl p-8 w-full max-w-lg shadow-2xl space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-black uppercase italic tracking-tighter">{{ modal.mode === 'create' ? 'Add New Film' : 'Edit Film' }}</h2>
                    <button @click="closeModal" class="text-gray-500 hover:text-white transition-colors text-xl">✕</button>
                </div>

                <form @submit.prevent="saveFilm" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Title</label>
                        <input v-model="form.title" type="text" required placeholder="Film title"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-600 transition-all" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Description</label>
                        <textarea v-model="form.description" rows="3" placeholder="Film description"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-600 transition-all resize-none"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Duration (min)</label>
                            <input v-model.number="form.duration" type="number" min="1" required placeholder="120"
                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-600 transition-all" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Rate</label>
                            <input v-model="form.rate" type="text" placeholder="8.5"
                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-600 transition-all" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Trailer URL</label>
                        <input v-model="form.trailer" type="url" placeholder="https://youtube.com/..."
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-600 transition-all" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Genres</label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="genre in genres" :key="genre.id"
                                type="button"
                                @click="toggleGenre(genre.id)"
                                :class="form.genres.includes(genre.id) ? 'bg-yellow-600 border-yellow-600 text-white' : 'bg-white/5 border-white/10 text-gray-400'"
                                class="px-3 py-1 rounded-full border text-xs font-bold uppercase tracking-widest transition-all"
                            >{{ genre.name }}</button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Poster Image</label>
                        <input type="file" @change="onFile" accept="image/*" ref="fileInput"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-600 transition-all file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-yellow-600 file:text-white" />
                    </div>

                    <div v-if="formError" class="text-red-400 text-sm bg-red-500/10 border border-red-500/20 rounded-xl p-3">{{ formError }}</div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="closeModal" class="flex-1 py-3 rounded-xl border border-white/10 hover:bg-white/5 font-bold uppercase tracking-widest transition-all text-sm">Cancel</button>
                        <button type="submit" :disabled="saving" class="flex-1 bg-yellow-600 hover:bg-yellow-700 disabled:opacity-50 text-white font-black py-3 rounded-xl uppercase tracking-widest transition-all text-sm">
                            {{ saving ? 'Saving...' : modal.mode === 'create' ? 'Create Film' : 'Update Film' }}
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
                <h1 class="text-4xl font-black uppercase italic tracking-tighter">Film <span class="text-yellow-600">Management</span></h1>
                <button @click="openCreate" class="bg-yellow-600 hover:bg-yellow-700 text-white font-black px-6 py-3 rounded-xl transition-all shadow-lg shadow-yellow-600/20 uppercase tracking-widest text-sm">
                    + Add New Film
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div v-for="film in films" :key="film.id" class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden hover:border-white/20 transition-all group">
                    <div class="aspect-2/3 relative overflow-hidden">
                        <img :src="film.image?.path || 'https://via.placeholder.com/300x450?text=No+Image'" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <div class="absolute inset-0 bg-linear-to-t from-black/80 via-transparent to-transparent flex flex-col justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <div class="flex gap-2">
                                <button @click="openEdit(film)" class="flex-1 py-2 bg-white/10 hover:bg-white/20 rounded-lg font-bold text-xs uppercase tracking-widest border border-white/10 transition-all">Edit</button>
                                <button @click="deleteFilm(film.id)" class="flex-1 py-2 bg-red-600/20 hover:bg-red-600 rounded-lg font-bold text-xs uppercase tracking-widest border border-red-600/30 transition-all">Delete</button>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold truncate">{{ film.title }}</h3>
                        <p class="text-xs text-gray-500 flex justify-between mt-1">
                            <span>{{ film.duration }} min</span>
                            <span>{{ film.rate }} / 10</span>
                        </p>
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

const films = ref([]);
const genres = ref([]);
const loading = ref(true);
const saving = ref(false);
const formError = ref('');
const fileInput = ref(null);

const modal = reactive({ show: false, mode: 'create', film: null });
const form = reactive({ title: '', description: '', duration: 90, rate: '', trailer: '', genres: [] });

const resetForm = () => {
    form.title = ''; form.description = ''; form.duration = 90;
    form.rate = ''; form.trailer = ''; form.genres = [];
    formError.value = '';
    if (fileInput.value) fileInput.value.value = '';
};

const openCreate = () => { resetForm(); modal.film = null; modal.mode = 'create'; modal.show = true; };
const openEdit = (film) => {
    resetForm();
    modal.film = film; modal.mode = 'edit'; modal.show = true;
    form.title = film.title; form.description = film.description;
    form.duration = film.duration; form.rate = film.rate; form.trailer = film.trailer || '';
    form.genres = film.genres?.map(g => g.id) || [];
};
const closeModal = () => { modal.show = false; resetForm(); };
const toggleGenre = (id) => {
    const idx = form.genres.indexOf(id);
    idx === -1 ? form.genres.push(id) : form.genres.splice(idx, 1);
};

let selectedFile = null;
const onFile = (e) => { selectedFile = e.target.files[0]; };

const fetchFilms = async () => {
    const res = await axios.get('/api/v1/films');
    films.value = res.data.data || res.data;
};
const fetchGenres = async () => {
    const res = await axios.get('/api/v1/genres');
    genres.value = res.data.genres || res.data || [];
};

const saveFilm = async () => {
    saving.value = true; formError.value = '';
    try {
        const fd = new FormData();
        Object.entries(form).forEach(([k, v]) => {
            if (k === 'genres') v.forEach(g => fd.append('genres[]', g));
            else fd.append(k, v);
        });
        if (selectedFile) fd.append('image', selectedFile);

        if (modal.mode === 'create') {
            const res = await axios.post('/api/v1/films', fd, { headers: { Authorization: `Bearer ${auth.token}`, 'Content-Type': 'multipart/form-data' } });
            films.value.unshift(res.data);
        } else {
            fd.append('_method', 'PUT');
            const res = await axios.post(`/api/v1/films/${modal.film.id}`, fd, { headers: { Authorization: `Bearer ${auth.token}`, 'Content-Type': 'multipart/form-data' } });
            const idx = films.value.findIndex(f => f.id === modal.film.id);
            if (idx !== -1) films.value[idx] = res.data;
        }
        closeModal();
    } catch (err) {
        formError.value = err.response?.data?.message || JSON.stringify(err.response?.data?.errors || 'Save failed');
    } finally {
        saving.value = false;
    }
};

const deleteFilm = async (id) => {
    if (!confirm('Delete this film? This cannot be undone.')) return;
    try {
        await axios.delete(`/api/v1/films/${id}`, { headers: { Authorization: `Bearer ${auth.token}` } });
        films.value = films.value.filter(f => f.id !== id);
    } catch (err) { alert(err.response?.data?.message || 'Delete failed'); }
};

onMounted(async () => {
    try { await Promise.all([fetchFilms(), fetchGenres()]); }
    finally { loading.value = false; }
});
</script>
