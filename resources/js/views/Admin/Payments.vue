<template>
    <div class="space-y-8 pb-16">
        <!-- Header -->
        <div class="space-y-1">
            <p class="text-[0.7rem] font-bold text-primary uppercase tracking-[0.2em]">Finance</p>
            <h1 class="font-serif text-4xl font-bold tracking-tight">Payment <span class="text-primary">Overview</span></h1>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
        </div>

        <template v-else>
            <!-- Stat cards -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-surface-container border border-outline-variant rounded-2xl p-5 space-y-2 col-span-2 lg:col-span-1">
                    <p class="text-[0.65rem] font-bold text-on-surface-variant uppercase tracking-widest">Revenue (All Time)</p>
                    <p class="font-serif text-2xl font-bold text-primary tabular-nums">{{ formatMoney(totals.revenue_all_time) }}</p>
                </div>
                <div class="bg-surface-container border border-outline-variant rounded-2xl p-5 space-y-2">
                    <p class="text-[0.65rem] font-bold text-on-surface-variant uppercase tracking-widest">This Month</p>
                    <p class="font-serif text-2xl font-bold text-on-surface tabular-nums">{{ formatMoney(totals.revenue_month) }}</p>
                </div>
                <div class="bg-surface-container border border-outline-variant rounded-2xl p-5 space-y-2">
                    <p class="text-[0.65rem] font-bold text-on-surface-variant uppercase tracking-widest">Transactions</p>
                    <p class="font-serif text-2xl font-bold text-on-surface tabular-nums">{{ totals.transactions ?? 0 }}</p>
                </div>
                <div class="bg-surface-container border border-outline-variant rounded-2xl p-5 space-y-2">
                    <p class="text-[0.65rem] font-bold text-on-surface-variant uppercase tracking-widest">Completed</p>
                    <p class="font-serif text-2xl font-bold text-green-400 tabular-nums">{{ totals.completed ?? 0 }}</p>
                </div>
                <div class="bg-surface-container border border-outline-variant rounded-2xl p-5 space-y-2">
                    <p class="text-[0.65rem] font-bold text-on-surface-variant uppercase tracking-widest">Failed</p>
                    <p class="font-serif text-2xl font-bold text-red-400 tabular-nums">{{ totals.failed ?? 0 }}</p>
                </div>
            </div>

            <!-- Empty -->
            <div v-if="!payments.length" class="flex flex-col items-center justify-center text-center py-20 bg-surface-container border border-dashed border-outline-variant rounded-2xl">
                <div class="w-14 h-14 rounded-2xl bg-surface-container-high flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-[28px] text-on-surface-variant" aria-hidden="true">payments</span>
                </div>
                <h3 class="font-serif text-xl mb-1">No transactions yet</h3>
                <p class="text-on-surface-variant text-sm">Payments will appear here once customers check out.</p>
            </div>

            <!-- Table -->
            <div v-else class="bg-surface-container border border-outline-variant rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[820px]">
                        <thead class="bg-surface-container-high">
                            <tr class="text-on-surface-variant uppercase tracking-[0.18em] text-[0.7rem] font-bold">
                                <th scope="col" class="px-6 py-4">ID</th>
                                <th scope="col" class="px-6 py-4">User</th>
                                <th scope="col" class="px-6 py-4">Film</th>
                                <th scope="col" class="px-6 py-4">Method</th>
                                <th scope="col" class="px-6 py-4">Amount</th>
                                <th scope="col" class="px-6 py-4">Status</th>
                                <th scope="col" class="px-6 py-4">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in payments" :key="p.id" class="border-t border-outline-variant hover:bg-surface-container-high transition-colors">
                                <td class="px-6 py-4 text-on-surface-variant tabular-nums">#{{ p.id }}</td>
                                <td class="px-6 py-4 font-semibold text-on-surface truncate max-w-[160px]">{{ p.reservation?.user?.name || '—' }}</td>
                                <td class="px-6 py-4 text-on-surface-variant truncate max-w-[180px]">{{ p.reservation?.session?.film?.title || '—' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[0.7rem] font-bold uppercase tracking-wider border" :class="methodMap[p.payment_method]?.class || 'bg-surface-container-high text-on-surface-variant border-outline-variant'">
                                        {{ methodMap[p.payment_method]?.label || p.payment_method || '—' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-bold text-primary tabular-nums whitespace-nowrap">{{ formatMoney(p.amount) }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[0.7rem] font-bold uppercase tracking-wider border" :class="statusMap[p.status]?.class">
                                        {{ statusMap[p.status]?.label || p.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-on-surface-variant whitespace-nowrap">{{ formatDate(p.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="payments.length" class="flex items-center justify-between gap-4">
                <p class="text-xs text-on-surface-variant uppercase tracking-widest">Page {{ currentPage }} of {{ lastPage }} · {{ total }} total</p>
                <div class="flex gap-2">
                    <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1" class="inline-flex items-center gap-1 px-4 py-2.5 rounded-xl border border-outline-variant text-xs font-bold uppercase tracking-widest text-on-surface hover:bg-surface-container-high transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed min-h-[44px]">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">chevron_left</span> Prev
                    </button>
                    <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= lastPage" class="inline-flex items-center gap-1 px-4 py-2.5 rounded-xl border border-outline-variant text-xs font-bold uppercase tracking-widest text-on-surface hover:bg-surface-container-high transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed min-h-[44px]">
                        Next <span class="material-symbols-outlined text-[18px]" aria-hidden="true">chevron_right</span>
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const payments = ref([]);
const totals = reactive({ revenue_all_time: 0, revenue_month: 0, transactions: 0, completed: 0, failed: 0 });
const loading = ref(true);

const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);

const statusMap = {
    completed: { label: 'Completed', class: 'bg-green-500/15 text-green-400 border-green-500/30' },
    pending: { label: 'Pending', class: 'bg-amber-500/15 text-amber-400 border-amber-500/30' },
    failed: { label: 'Failed', class: 'bg-red-500/15 text-red-400 border-red-500/30' },
};

const methodMap = {
    stripe: { label: 'Stripe', class: 'bg-blue-500/15 text-blue-400 border-blue-500/30' },
    paypal: { label: 'PayPal', class: 'bg-blue-500/15 text-blue-400 border-blue-500/30' },
};

const formatMoney = (v) => `$${Number(v || 0).toFixed(2)}`;

const formatDate = (dt) => {
    if (!dt) return '—';
    return new Date(dt).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
};

const fetchPayments = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/api/v1/admin/payments', { params: { page: currentPage.value } });
        const t = res.data.totals || {};
        totals.revenue_all_time = t.revenue_all_time ?? 0;
        totals.revenue_month = t.revenue_month ?? 0;
        totals.transactions = t.transactions ?? 0;
        totals.completed = t.completed ?? 0;
        totals.failed = t.failed ?? 0;

        const paginator = res.data.payments || {};
        payments.value = paginator.data || [];
        currentPage.value = paginator.current_page || 1;
        lastPage.value = paginator.last_page || 1;
        total.value = paginator.total || 0;
    } catch (err) {
        console.error('Failed to fetch payments:', err);
        payments.value = [];
    } finally {
        loading.value = false;
    }
};

const goToPage = (page) => {
    if (page < 1 || page > lastPage.value) return;
    currentPage.value = page;
    fetchPayments();
};

onMounted(fetchPayments);
</script>
