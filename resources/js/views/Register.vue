<template>
    <main class="min-h-[80vh] flex items-center justify-center py-12 px-6 relative bg-surface overflow-hidden">
        <!-- Atmospheric Background Elements -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-30">
            <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-primary/10 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-primary-fixed/20 blur-3xl"></div>
        </div>

        <div class="w-full max-w-[480px] z-10">
            <div class="bg-surface-container p-6 sm:p-10 md:p-14 rounded-2xl border border-outline-variant shadow-[0_8px_30px_rgba(0,0,0,0.45)] flex flex-col gap-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <!-- Branding/Header -->
                <div class="text-center space-y-2">
                    <h1 class="font-serif text-3xl font-bold text-primary">Join Cinehall</h1>
                    <p class="text-sm text-on-surface-variant">Create your profile to experience modern cinematic opulence.</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleRegister" class="flex flex-col gap-6">
                    <!-- Full Name Input -->
                    <div class="relative group">
                        <label class="text-[10px] uppercase tracking-widest text-outline transition-colors group-focus-within:text-primary font-bold block" for="name">Full Name</label>
                        <input 
                            v-model="form.name" 
                            type="text"
                            id="name"
                            autocomplete="name"
                            required
                            class="w-full bg-transparent border-t-0 border-x-0 border-b border-outline-variant focus:border-primary focus:ring-0 px-0 py-3 font-sans transition-all duration-300 placeholder:text-on-surface-variant/60 text-base text-on-surface"
                            placeholder="Alexander Mercer"
                        />
                    </div>

                    <!-- Email Input -->
                    <div class="relative group">
                        <label class="text-[10px] uppercase tracking-widest text-outline transition-colors group-focus-within:text-primary font-bold block" for="email">Email Address</label>
                        <input 
                            v-model="form.email" 
                            type="email"
                            id="email"
                            autocomplete="email"
                            required
                            class="w-full bg-transparent border-t-0 border-x-0 border-b border-outline-variant focus:border-primary focus:ring-0 px-0 py-3 font-sans transition-all duration-300 placeholder:text-on-surface-variant/60 text-base text-on-surface"
                            placeholder="alexander@luxuryliving.com"
                        />
                    </div>

                    <!-- Password Input -->
                    <div class="relative group">
                        <label class="text-[10px] uppercase tracking-widest text-outline transition-colors group-focus-within:text-primary font-bold block" for="password">Password</label>
                        <input 
                            v-model="form.password" 
                            type="password"
                            id="password"
                            autocomplete="new-password"
                            required
                            class="w-full bg-transparent border-t-0 border-x-0 border-b border-outline-variant focus:border-primary focus:ring-0 px-0 py-3 font-sans transition-all duration-300 placeholder:text-on-surface-variant/60 text-base text-on-surface"
                            placeholder="••••••••••••"
                        />
                    </div>

                    <!-- Confirm Password Input -->
                    <div class="relative group">
                        <label class="text-[10px] uppercase tracking-widest text-outline transition-colors group-focus-within:text-primary font-bold block" for="password_confirmation">Confirm Password</label>
                        <input 
                            v-model="form.password_confirmation" 
                            type="password"
                            id="password_confirmation"
                            autocomplete="new-password"
                            required
                            class="w-full bg-transparent border-t-0 border-x-0 border-b border-outline-variant focus:border-primary focus:ring-0 px-0 py-3 font-sans transition-all duration-300 placeholder:text-on-surface-variant/60 text-base text-on-surface"
                            placeholder="••••••••••••"
                        />
                    </div>

                    <div v-if="error" role="alert" class="text-red-400 text-xs font-semibold bg-red-500/10 p-4 border border-red-500/30 rounded-lg">
                        {{ error }}
                    </div>

                    <!-- CTA Button -->
                    <button 
                        type="submit" 
                        :disabled="loading"
                        class="w-full mt-2 gold-gradient py-4 px-8 rounded-lg font-bold text-xs uppercase tracking-[0.2em] shadow-lg shadow-primary/20 hover:brightness-105 active:scale-[0.98] transition-all duration-300 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ loading ? 'Creating account...' : 'Create Account' }}
                    </button>
                </form>

                <!-- Divider -->
                <div class="relative flex items-center gap-4">
                    <div class="flex-1 h-px bg-outline-variant/30"></div>
                    <span class="text-[10px] font-bold text-outline-variant">OR</span>
                    <div class="flex-1 h-px bg-outline-variant/30"></div>
                </div>

                <!-- Footer Action -->
                <div class="text-center">
                    <p class="text-sm text-on-surface-variant">
                        Already have an account? 
                        <router-link to="/login" class="text-primary font-bold hover:underline underline-offset-4 ml-1">Sign In</router-link>
                    </p>
                </div>
            </div>
        </div>
    </main>
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
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
});

const handleRegister = async () => {
    loading.value = true;
    error.value = '';
    try {
        const response = await axios.post('/api/v1/register', form);
        auth.setAuth(response.data.user, response.data.access_token);
        router.push('/');
    } catch (err) {
        error.value = err.response?.data?.message || 'Registration rejected. Check specified values.';
    } finally {
        loading.value = false;
    }
};
</script>
