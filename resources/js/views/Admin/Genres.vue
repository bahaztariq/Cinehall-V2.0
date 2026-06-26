<template>
    <div class="max-w-3xl mx-auto space-y-8">
        <!-- Header -->
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-[0.65rem] font-bold uppercase tracking-[0.2em] text-primary mb-1">Catalogue</p>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold">Genres</h2>
                <p class="text-sm text-on-surface-variant mt-1">Organise films into browsable categories.</p>
            </div>
            <span class="text-xs font-bold uppercase tracking-widest text-on-surface-variant bg-surface-container border border-outline-variant rounded-full px-4 py-1.5">
                {{ genres.length }} total
            </span>
        </div>

        <!-- Add new -->
        <form @submit.prevent="createGenre" class="flex gap-3 bg-surface-container border border-outline-variant rounded-2xl p-4">
            <label for="new-genre" class="sr-only">New genre name</label>
            <input
                id="new-genre"
                v-model="newName"
                type="text"
                placeholder="Add a genre (e.g. Western)"
                class="flex-1 bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/60 focus:border-primary outline-none"
            />
            <button type="submit" :disabled="!newName.trim() || saving" class="gold-gradient px-5 py-3 rounded-xl font-bold uppercase tracking-widest text-xs hover:brightness-110 transition-all disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                {{ saving ? 'Adding...' : 'Add' }}
            </button>
        </form>

        <p v-if="error" role="alert" class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm font-bold rounded-xl px-4 py-3">{{ error }}</p>

        <!-- List -->
        <div v-if="loading" class="flex justify-center py-16">
            <div class="w-8 h-8 border-2 border-primary border-t-transparent rounded-full animate-spin" role="status" aria-label="Loading"></div>
        </div>

        <div v-else-if="genres.length" class="bg-surface-container border border-outline-variant rounded-2xl divide-y divide-outline-variant overflow-hidden">
            <div v-for="genre in genres" :key="genre.id" class="flex items-center gap-3 px-5 py-4 hover:bg-surface-container-high transition-colors">
                <span class="material-symbols-outlined text-primary text-[20px]" aria-hidden="true">sell</span>
                <template v-if="editId === genre.id">
                    <input
                        v-model="editName"
                        type="text"
                        :aria-label="'Edit ' + genre.name"
                        @keyup.enter="saveEdit(genre)"
                        @keyup.esc="cancelEdit"
                        class="flex-1 bg-surface-container-low border border-primary rounded-lg px-3 py-2 text-sm text-on-surface outline-none"
                    />
                    <button @click="saveEdit(genre)" aria-label="Save" class="p-2 text-green-400 hover:bg-green-500/10 rounded-lg cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">check</span>
                    </button>
                    <button @click="cancelEdit" aria-label="Cancel" class="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">close</span>
                    </button>
                </template>
                <template v-else>
                    <span class="flex-1 text-sm font-bold text-on-surface">{{ genre.name }}</span>
                    <button @click="startEdit(genre)" :aria-label="'Edit ' + genre.name" class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">edit</span>
                    </button>
                    <button @click="removeGenre(genre)" :aria-label="'Delete ' + genre.name" class="p-2 text-on-surface-variant hover:text-red-400 hover:bg-red-500/10 rounded-lg cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">delete</span>
                    </button>
                </template>
            </div>
        </div>

        <div v-else class="text-center py-16 border border-dashed border-outline-variant rounded-2xl">
            <span class="material-symbols-outlined text-on-surface-variant text-[40px]" aria-hidden="true">sell</span>
            <p class="text-sm text-on-surface-variant mt-2">No genres yet. Add your first one above.</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const genres = ref([]);
const loading = ref(true);
const saving = ref(false);
const error = ref('');
const newName = ref('');
const editId = ref(null);
const editName = ref('');

const fetchGenres = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/api/v1/genres');
        genres.value = res.data.genres || res.data.data || res.data || [];
    } catch {
        error.value = 'Failed to load genres.';
    } finally {
        loading.value = false;
    }
};

const createGenre = async () => {
    if (!newName.value.trim()) return;
    saving.value = true;
    error.value = '';
    try {
        await axios.post('/api/v1/genres', { name: newName.value.trim() });
        newName.value = '';
        await fetchGenres();
    } catch (e) {
        error.value = e.response?.data?.message || 'Could not add genre.';
    } finally {
        saving.value = false;
    }
};

const startEdit = (genre) => { editId.value = genre.id; editName.value = genre.name; };
const cancelEdit = () => { editId.value = null; editName.value = ''; };

const saveEdit = async (genre) => {
    if (!editName.value.trim()) return;
    try {
        await axios.put(`/api/v1/genres/${genre.id}`, { name: editName.value.trim() });
        cancelEdit();
        await fetchGenres();
    } catch (e) {
        error.value = e.response?.data?.message || 'Could not update genre.';
    }
};

const removeGenre = async (genre) => {
    if (!confirm(`Delete genre "${genre.name}"? Films keep existing but lose this tag.`)) return;
    try {
        await axios.delete(`/api/v1/genres/${genre.id}`);
        await fetchGenres();
    } catch (e) {
        error.value = e.response?.data?.message || 'Could not delete genre.';
    }
};

onMounted(fetchGenres);
</script>
