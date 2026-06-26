<template>
    <div class="space-y-8 pb-16">
        <!-- TMDB Import Modal Overlay -->
        <div v-if="tmdbModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" @click.self="closeTmdbModal">
            <div role="dialog" aria-modal="true" aria-labelledby="tmdb-modal-title" class="bg-surface-container border border-outline-variant rounded-3xl p-8 w-full max-w-2xl shadow-2xl space-y-6 max-h-[85vh] flex flex-col" @click.stop>
                <div class="flex justify-between items-center shrink-0">
                    <div class="space-y-1">
                        <p class="text-[0.65rem] font-bold text-cinema-gold uppercase tracking-[0.2em]">Global Database</p>
                        <h2 id="tmdb-modal-title" class="font-serif text-2xl font-bold tracking-tight text-white">Import from <span class="text-cinema-gold">TMDB</span></h2>
                    </div>
                    <button @click="closeTmdbModal" aria-label="Close" class="w-11 h-11 flex items-center justify-center rounded-xl text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high transition-colors cursor-pointer">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>

                <!-- TMDB Search Bar -->
                <div class="relative shrink-0">
                    <label for="tmdb-search" class="sr-only">Search TMDB movies</label>
                    <input
                        id="tmdb-search"
                        v-model="tmdbModal.query"
                        @keyup.enter="searchTmdb"
                        type="text"
                        placeholder="Search global movie database (e.g. Dune)..."
                        class="w-full glass-input rounded-2xl px-5 py-3.5 pr-12 text-sm text-white placeholder:text-on-surface-variant/60"
                    />
                    <button @click="searchTmdb" :disabled="tmdbModal.searching" aria-label="Search" class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 flex items-center justify-center rounded-lg text-on-surface-variant hover:text-cinema-gold transition-colors disabled:opacity-50">
                        <svg class="w-5 h-5 fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                    </button>
                </div>

                <!-- TMDB Results List -->
                <div class="overflow-y-auto flex-1 space-y-3 pr-1">
                    <div v-if="tmdbModal.searching" class="flex justify-center items-center py-12">
                        <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-cinema-gold"></div>
                    </div>
                    <template v-else-if="tmdbModal.results.length">
                        <div v-for="movie in tmdbModal.results" :key="movie.id" class="bg-surface-container-low border border-outline-variant p-4 rounded-2xl flex gap-4 hover:border-cinema-gold/30 transition-all items-center">
                            <img :src="movie.poster_path ? 'https://image.tmdb.org/t/p/w92' + movie.poster_path : 'https://via.placeholder.com/92x138?text=No+Poster'" :alt="movie.title" class="w-16 h-24 object-cover rounded-lg border border-outline-variant shrink-0" />
                            <div class="flex-1 space-y-1 min-w-0">
                                <h4 class="font-bold text-white truncate text-base leading-tight">{{ movie.title }}</h4>
                                <p class="text-xs text-cinema-gold font-bold uppercase tracking-wider">{{ movie.release_date || 'N/A' }}</p>
                                <p class="text-xs text-on-surface-variant line-clamp-2 leading-relaxed">{{ movie.overview }}</p>
                            </div>
                            <button
                                @click="importMovie(movie.id)"
                                :disabled="tmdbModal.importing[movie.id]"
                                class="px-5 py-3 gold-gradient hover:opacity-90 font-bold uppercase text-[0.65rem] tracking-widest rounded-xl transition-opacity disabled:opacity-30 shrink-0 cursor-pointer flex items-center gap-1.5"
                            >
                                <svg v-if="!tmdbModal.importing[movie.id]" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v10m0 0-4-4m4 4 4-4M5 19h14"/></svg>
                                {{ tmdbModal.importing[movie.id] ? 'Importing...' : 'Import' }}
                            </button>
                        </div>
                    </template>
                    <div v-else-if="tmdbModal.searched" class="text-center py-12 text-on-surface-variant text-sm">
                        No TMDB blockbusters matched your search query.
                    </div>
                    <div v-else class="flex flex-col items-center text-center py-12 text-on-surface-variant text-sm gap-3">
                        <svg class="w-10 h-10 text-on-surface-variant/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                        Type a movie title and press Enter to query the TMDB API.
                    </div>
                </div>

                <div v-if="tmdbModal.error" class="flex items-start gap-2 text-red-400 text-xs bg-red-500/10 border border-red-500/20 rounded-xl p-3 shrink-0">
                    <svg class="w-4 h-4 mt-px shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
                    <span>{{ tmdbModal.error }}</span>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal Overlay -->
        <div v-if="modal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" @click.self="closeModal">
            <div role="dialog" aria-modal="true" aria-labelledby="film-modal-title" class="bg-surface-container border border-outline-variant rounded-3xl p-8 w-full max-w-lg shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="flex justify-between items-center">
                    <h2 id="film-modal-title" class="font-serif text-2xl font-bold tracking-tight text-white">{{ modal.mode === 'create' ? 'Add New Film' : 'Edit Film' }}</h2>
                    <button @click="closeModal" aria-label="Close" class="w-11 h-11 flex items-center justify-center rounded-xl text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high transition-colors cursor-pointer">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>

                <form @submit.prevent="saveFilm" class="space-y-5">
                    <div>
                        <label for="film-title" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Title</label>
                        <input id="film-title" v-model="form.title" type="text" required placeholder="Film title"
                            class="w-full glass-input rounded-xl px-4 py-3 text-white text-sm" />
                    </div>
                    <div>
                        <label for="film-description" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Description</label>
                        <textarea id="film-description" v-model="form.description" rows="3" placeholder="Film description"
                            class="w-full glass-input rounded-xl px-4 py-3 text-white text-sm resize-none"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="film-duration" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Duration (min)</label>
                            <input id="film-duration" v-model.number="form.duration" type="number" min="1" required placeholder="120"
                                class="w-full glass-input rounded-xl px-4 py-3 text-white text-sm" />
                        </div>
                        <div>
                            <label for="film-rate" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Rate / Rating</label>
                            <select id="film-rate" v-model="form.rate" required class="w-full glass-input rounded-xl px-4 py-3 text-white text-sm cursor-pointer">
                                <option value="G">G</option>
                                <option value="PG">PG</option>
                                <option value="PG-13">PG-13</option>
                                <option value="R">R</option>
                                <option value="NC-17">NC-17</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="film-trailer" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Trailer URL</label>
                        <input id="film-trailer" v-model="form.trailer" type="url" placeholder="https://youtube.com/..."
                            class="w-full glass-input rounded-xl px-4 py-3 text-white text-sm" />
                    </div>
                    <div>
                        <span class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-2">Genres</span>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="genre in genres" :key="genre.id"
                                type="button"
                                @click="toggleGenre(genre.id)"
                                :class="form.genres.includes(genre.id) ? 'bg-cinema-gold text-black border-cinema-gold' : 'bg-surface-container-low border-outline-variant text-on-surface-variant hover:border-cinema-gold/40'"
                                class="px-3.5 py-1.5 rounded-full border text-[0.65rem] font-bold uppercase tracking-wider transition-all cursor-pointer"
                            >{{ genre.name }}</button>
                        </div>
                    </div>
                    <div>
                        <label for="film-image" class="block text-[0.7rem] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Poster Image</label>
                        <input id="film-image" type="file" @change="onFile" accept="image/*" ref="fileInput"
                            class="w-full glass-input rounded-xl px-4 py-3 text-white text-xs file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[0.65rem] file:font-bold file:bg-cinema-gold file:text-black cursor-pointer" />
                    </div>

                    <div v-if="formError" class="flex items-start gap-2 text-red-400 text-sm bg-red-500/10 border border-red-500/20 rounded-xl p-3">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
                        <span>{{ formError }}</span>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="closeModal" class="flex-1 py-3.5 rounded-xl border border-outline-variant hover:bg-surface-container-high text-xs font-bold uppercase tracking-widest text-white transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" :disabled="saving" class="flex-1 gold-gradient hover:opacity-90 disabled:opacity-50 font-bold py-3.5 rounded-xl text-xs uppercase tracking-widest transition-opacity cursor-pointer">
                            {{ saving ? 'Saving...' : modal.mode === 'create' ? 'Create Film' : 'Update Film' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-cinema-gold"></div>
        </div>

        <div v-else class="space-y-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="space-y-1">
                    <p class="text-[0.7rem] font-bold text-cinema-gold uppercase tracking-[0.2em]">Catalogue</p>
                    <h1 class="font-serif text-4xl font-bold tracking-tight text-white">Film <span class="text-cinema-gold">Management</span></h1>
                </div>

                <div class="flex gap-3 w-full md:w-auto">
                    <button @click="openTmdbModal" class="flex-1 md:flex-none flex items-center justify-center gap-2 bg-surface-container-high hover:bg-surface-container-highest text-white font-bold px-6 py-3.5 rounded-xl border border-outline-variant transition-colors uppercase tracking-widest text-xs cursor-pointer">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                        Import from TMDB
                    </button>
                    <button @click="openCreate" class="flex-1 md:flex-none flex items-center justify-center gap-2 gold-gradient hover:opacity-90 font-bold px-6 py-3.5 rounded-xl transition-opacity shadow-lg shadow-cinema-gold/20 uppercase tracking-widest text-xs cursor-pointer">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                        Add New Film
                    </button>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="!films.length" class="flex flex-col items-center justify-center text-center py-20 bg-surface-container border border-dashed border-outline-variant rounded-2xl">
                <div class="w-14 h-14 rounded-2xl bg-surface-container-high flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-on-surface-variant" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="2" y="3" width="20" height="18" rx="2"/><path stroke-linecap="round" d="M7 3v18M17 3v18M2 9h5m10 0h5M2 15h5m10 0h5"/></svg>
                </div>
                <h3 class="font-serif text-xl text-white mb-1">No films in the catalogue</h3>
                <p class="text-on-surface-variant text-sm">Add a film manually or import one from TMDB to get started.</p>
            </div>

            <!-- Film Cards Grid -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div v-for="film in films" :key="film.id" class="bg-surface-container border border-outline-variant rounded-2xl overflow-hidden hover:border-cinema-gold/40 transition-all duration-300 group flex flex-col h-full justify-between">
                    <div>
                        <div class="aspect-2/3 relative overflow-hidden bg-cinema-dark">
                            <img :src="getImageUrl(film.image)" :alt="film.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                            <!-- Rating badge -->
                            <span class="absolute top-3 left-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-black/70 backdrop-blur-sm border border-cinema-gold/40 text-cinema-gold text-[0.6rem] font-bold uppercase tracking-widest">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2 2.9 6.3L22 9.3l-5 4.9 1.2 7L12 17.8 5.8 21.2 7 14.2l-5-4.9 7.1-1z"/></svg>
                                {{ film.rate }}
                            </span>
                            <!-- Coming soon flag -->
                            <span v-if="film.coming_soon" class="absolute top-3 right-3 inline-flex items-center px-2.5 py-1 rounded-full bg-amber-500/20 backdrop-blur-sm border border-amber-500/40 text-amber-300 text-[0.6rem] font-bold uppercase tracking-widest">
                                Soon
                            </span>
                            <div class="absolute inset-0 bg-linear-to-t from-cinema-black/95 via-cinema-black/40 to-transparent flex flex-col justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <div class="flex gap-2 z-10">
                                    <button @click="openEdit(film)" class="flex-1 py-2.5 flex items-center justify-center gap-1.5 bg-white/10 hover:bg-white/20 text-white rounded-xl font-bold text-[0.65rem] uppercase tracking-widest border border-white/15 transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        Edit
                                    </button>
                                    <button @click="deleteFilm(film.id)" class="flex-1 py-2.5 flex items-center justify-center gap-1.5 bg-red-500/25 hover:bg-red-600 text-white rounded-xl font-bold text-[0.65rem] uppercase tracking-widest border border-red-500/40 transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14"/></svg>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="p-5 space-y-2">
                            <h3 class="font-bold text-white text-base truncate leading-tight">{{ film.title }}</h3>
                            <div class="flex flex-wrap gap-1.5" v-if="film.genres?.length">
                                <span v-for="g in film.genres.slice(0, 2)" :key="g.id" class="px-2 py-0.5 rounded-full bg-surface-container-high text-on-surface-variant text-[0.6rem] font-bold uppercase tracking-wider">{{ g.name }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-[0.65rem] text-on-surface-variant font-bold uppercase tracking-wider pt-1">
                                <svg class="w-3.5 h-3.5 text-cinema-gold" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>
                                {{ film.duration }} min
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const films = ref([]);
const genres = ref([]);
const loading = ref(true);
const saving = ref(false);
const formError = ref('');
const fileInput = ref(null);

const modal = reactive({ show: false, mode: 'create', film: null });
const form = reactive({ title: '', description: '', duration: 90, rate: 'G', trailer: '', genres: [] });

// TMDB Modal States
const tmdbModal = reactive({
    show: false,
    query: '',
    searching: false,
    searched: false,
    results: [],
    importing: {},
    error: ''
});

const openTmdbModal = () => {
    tmdbModal.query = '';
    tmdbModal.results = [];
    tmdbModal.searched = false;
    tmdbModal.error = '';
    tmdbModal.show = true;
};

const closeTmdbModal = () => {
    tmdbModal.show = false;
};

const searchTmdb = async () => {
    const q = tmdbModal.query.trim();
    if (q.length < 2) return;
    tmdbModal.searching = true;
    tmdbModal.error = '';
    try {
        const res = await axios.get(`/api/v1/tmdb/search?query=${encodeURIComponent(q)}`);
        tmdbModal.results = res.data;
        tmdbModal.searched = true;
    } catch (err) {
        tmdbModal.error = err.response?.data?.message || 'TMDB Search query failed.';
    } finally {
        tmdbModal.searching = false;
    }
};

const importMovie = async (tmdbId) => {
    tmdbModal.importing[tmdbId] = true;
    tmdbModal.error = '';
    try {
        const res = await axios.post('/api/v1/tmdb/import', { tmdb_id: tmdbId });
        films.value.unshift(res.data.film);
        
        // Success alert or update
        tmdbModal.importing[tmdbId] = false;
        closeTmdbModal();
    } catch (err) {
        tmdbModal.error = err.response?.data?.message || 'Film import operation failed.';
        tmdbModal.importing[tmdbId] = false;
    }
};

const resetForm = () => {
    form.title = ''; form.description = ''; form.duration = 90;
    form.rate = 'G'; form.trailer = ''; form.genres = [];
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

const getImageUrl = (image) => {
    if (!image || !image.path) return 'https://via.placeholder.com/300x450?text=No+Image';
    if (image.path.startsWith('http://') || image.path.startsWith('https://')) {
        return image.path;
    }
    return `/storage/${image.path}`;
};

const fetchFilms = async () => {
    const res = await axios.get('/api/v1/films?all=true');
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
            const res = await axios.post('/api/v1/films', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
            films.value.unshift(res.data);
        } else {
            fd.append('_method', 'PUT');
            const res = await axios.post(`/api/v1/films/${modal.film.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
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
        await axios.delete(`/api/v1/films/${id}`);
        films.value = films.value.filter(f => f.id !== id);
    } catch (err) { alert(err.response?.data?.message || 'Delete failed'); }
};

onMounted(async () => {
    try { await Promise.all([fetchFilms(), fetchGenres()]); }
    finally { loading.value = false; }
});
</script>
