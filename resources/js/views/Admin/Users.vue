<template>
    <div class="admin-users min-h-screen bg-surface text-on-surface">
        <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
        </div>

        <div v-else class="max-w-7xl mx-auto px-6 py-10 space-y-8">
            <!-- Header bar -->
            <header class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-on-surface-variant">Audience</p>
                    <h1 class="font-serif text-4xl text-on-surface">User <span class="text-primary">Management</span></h1>
                </div>
                <div class="count-chip">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m6-1.13a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z"/></svg>
                    <span class="text-on-surface-variant text-xs uppercase tracking-[0.18em] font-semibold">Total</span>
                    <span class="text-on-surface font-bold tabular-nums">{{ users.length }}</span>
                </div>
            </header>

            <!-- Data table -->
            <div class="bg-surface-container border border-outline-variant rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[640px]">
                        <thead class="bg-surface-container-high">
                            <tr class="text-on-surface-variant uppercase tracking-[0.18em] text-[0.7rem] font-bold">
                                <th scope="col" class="px-6 py-4">User</th>
                                <th scope="col" class="px-6 py-4">Email</th>
                                <th scope="col" class="px-6 py-4">Role</th>
                                <th scope="col" class="px-6 py-4">Status</th>
                                <th scope="col" class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in users"
                                :key="user.id"
                                class="user-row border-t border-outline-variant"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="avatar-initial" aria-hidden="true">
                                            {{ user.name.charAt(0) }}
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-semibold text-on-surface truncate">{{ user.name }}</span>
                                            <span class="text-xs text-on-surface-variant truncate md:hidden">{{ user.email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-on-surface-variant">{{ user.email }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="role-chip"
                                        :class="user.is_admin ? 'role-admin' : 'role-user'"
                                    >
                                        <svg v-if="user.is_admin" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3Z"/></svg>
                                        <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM4 21v-1a6 6 0 0 1 12 0v1"/></svg>
                                        {{ user.is_admin ? 'Admin' : 'User' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="status-badge"
                                        :class="user.status === 'active' ? 'bg-green-500/15 text-green-400 border border-green-500/30' : 'bg-red-500/15 text-red-400 border border-red-500/30'"
                                    >
                                        <svg v-if="user.status === 'active'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                                        <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18.36 6.64a9 9 0 1 1-12.73 0M12 2v8"/></svg>
                                        {{ user.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        v-if="user.id !== auth.user?.id"
                                        @click="toggleStatus(user)"
                                        :aria-label="user.status === 'active' ? `Ban ${user.name}` : `Unban ${user.name}`"
                                        class="action-btn"
                                        :class="user.status === 'active' ? 'action-ban' : 'action-unban'"
                                    >
                                        <svg v-if="user.status === 'active'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18.36 5.64a9 9 0 1 1-12.73 0M5.64 5.64l12.73 12.73"/></svg>
                                        <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                                        {{ user.status === 'active' ? 'Ban' : 'Unban' }}
                                    </button>
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

<style scoped>
.count-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    background: var(--color-surface-container);
    border: 1px solid var(--color-outline-variant);
}

/* Table rows: zebra + hover */
.user-row {
    transition: background-color 0.16s ease;
}
.user-row:nth-child(even) {
    background-color: rgba(255, 255, 255, 0.015);
}
.user-row:hover {
    background-color: var(--color-surface-container-high);
}

.avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 9999px;
    background: rgba(212, 160, 23, 0.18);
    color: var(--color-primary);
    font-weight: 700;
    text-transform: uppercase;
    flex-shrink: 0;
    border: 1px solid rgba(212, 160, 23, 0.25);
}

.role-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.7rem;
    border-radius: 9999px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    border: 1px solid var(--color-outline-variant);
}
.role-admin {
    background: rgba(212, 160, 23, 0.12);
    color: var(--color-primary);
    border-color: rgba(212, 160, 23, 0.3);
}
.role-user {
    background: var(--color-surface-container-high);
    color: var(--color-on-surface-variant);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 0.9rem;
    border-radius: 0.625rem;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    border: 1px solid var(--color-outline-variant);
    transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
}
.action-ban {
    color: #f87171;
}
.action-ban:hover {
    background: rgba(239, 68, 68, 0.12);
    border-color: rgba(239, 68, 68, 0.4);
}
.action-unban {
    color: #4ade80;
}
.action-unban:hover {
    background: rgba(34, 197, 94, 0.12);
    border-color: rgba(34, 197, 94, 0.4);
}
</style>
