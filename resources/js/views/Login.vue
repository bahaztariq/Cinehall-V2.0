<template>
    <div class="max-w-md mx-auto bg-white/5 backdrop-blur-xl p-8 rounded-2xl border border-white/10 shadow-2xl">
        <h2 class="text-3xl font-black mb-6 text-center uppercase tracking-tighter italic">
            Welcome <span class="text-yellow-600">Back</span>
        </h2>
        
        <form @submit.prevent="handleLogin" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2 uppercase tracking-widest">Email Address</label>
                <input 
                    v-model="form.email" 
                    type="email" 
                    required 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-600 transition-all"
                    placeholder="you@example.com"
                />
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2 uppercase tracking-widest">Password</label>
                <input 
                    v-model="form.password" 
                    type="password" 
                    required 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-600 transition-all"
                    placeholder="••••••••"
                />
            </div>

            <div v-if="error" class="text-red-500 text-sm font-medium bg-red-500/10 p-3 rounded-lg border border-red-500/20">
                {{ error }}
            </div>

            <button 
                type="submit" 
                :disabled="loading"
                class="w-full bg-yellow-600 hover:bg-yellow-700 disabled:opacity-50 text-white font-bold py-4 rounded-xl transition-all shadow-lg shadow-yellow-600/20 uppercase tracking-widest"
            >
                {{ loading ? 'Signing in...' : 'Sign In' }}
            </button>
        </form>

        <p class="mt-8 text-center text-gray-400">
            Don't have an account? 
            <router-link to="/register" class="text-yellow-600 hover:text-yellow-500 font-bold transition-colors">Register</router-link>
        </p>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../store/auth';
const auth = useAuthStore();

const router = useRouter();
const loading = ref(false);
const error = ref('');

const form = reactive({
    email: '',
    password: ''
});

const handleLogin = async () => {
    loading.value = true;
    error.value = '';
    try {
        const response = await axios.post('/api/v1/login', form);
        auth.setAuth(response.data.user, response.data.access_token);
        router.push('/');
    } catch (err) {
        error.value = err.response?.data?.message || 'Login failed. Please check your credentials.';
    } finally {
        loading.value = false;
    }
};
</script>
