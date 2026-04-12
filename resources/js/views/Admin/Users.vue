<template>
    <div>
        <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-yellow-600"></div>
        </div>

        <div v-else class="space-y-8">
            <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                <h1 class="text-4xl font-black uppercase italic tracking-tighter">User <span class="text-yellow-600">Management</span></h1>
                <div class="bg-white/5 border border-white/10 px-6 py-2 rounded-xl text-sm font-bold text-gray-400">
                    Total: {{ users.length }}
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-4">
                    <thead>
                        <tr class="text-gray-500 uppercase tracking-widest text-xs font-black">
                            <th class="px-6 pb-2">User</th>
                            <th class="px-6 pb-2">Email</th>
                            <th class="px-6 pb-2">Role</th>
                            <th class="px-6 pb-2">Status</th>
                            <th class="px-6 pb-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id" class="bg-white/5 border border-white/10 group hover:bg-white/10 transition-all">
                            <td class="px-6 py-4 rounded-l-2xl">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-yellow-600/20 flex items-center justify-center text-yellow-600 font-black">
                                        {{ user.name.charAt(0) }}
                                    </div>
                                    <span class="font-bold">{{ user.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-400">{{ user.email }}</td>
                            <td class="px-6 py-4">
                                <span :class="user.is_admin ? 'text-yellow-600' : 'text-gray-500'" class="text-xs font-black uppercase tracking-widest">
                                    {{ user.is_admin ? 'Admin' : 'User' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="user.status === 'active' ? 'bg-green-500/10 text-green-500 border-green-500/20' : 'bg-red-500/10 text-red-500 border-red-500/20'" class="px-3 py-1 rounded-full text-[0.7rem] font-bold uppercase tracking-widest border">
                                    {{ user.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right rounded-r-2xl">
                                <button 
                                    v-if="user.id !== auth.user?.id"
                                    @click="toggleStatus(user)"
                                    class="px-4 py-2 rounded-lg border border-white/10 hover:bg-white/10 transition-all font-bold text-xs uppercase tracking-widest"
                                >
                                    {{ user.status === 'active' ? 'Ban' : 'Unban' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../store/auth';

const auth = useAuthStore();

const users = ref([]);
const loading = ref(true);

const fetchUsers = async () => {
    try {
        const response = await axios.get('/api/v1/statistics', {
            headers: { Authorization: `Bearer ${auth.token}` }
        });
        users.value = response.data.all_users || [];
    } catch (err) {
        console.error('Failed to fetch users:', err);
    } finally {
        loading.value = false;
    }
};

const toggleStatus = async (user) => {
    try {
        const response = await axios.patch(`/api/v1/users/${user.id}/toggle-status`, {}, {
            headers: { Authorization: `Bearer ${auth.token}` }
        });
        // Update local state
        user.status = response.data.user.status;
    } catch (err) {
        alert(err.response?.data?.message || 'Failed to toggle status');
    }
};

onMounted(fetchUsers);
</script>
