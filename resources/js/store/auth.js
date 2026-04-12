import { defineStore } from 'pinia';
import Cookies from 'js-cookie';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: Cookies.get('user') ? JSON.parse(Cookies.get('user')) : null,
        token: Cookies.get('token') || null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        isAdmin: (state) => !!(state.user?.is_admin),
    },

    actions: {
        setAuth(user, token) {
            this.user = user;
            this.token = token;

            // Set cookies with 7 days expiration (customizable)
            Cookies.set('user', JSON.stringify(user), { expires: 7 });
            Cookies.set('token', token, { expires: 7 });
        },

        logout() {
            this.user = null;
            this.token = null;

            Cookies.remove('user');
            Cookies.remove('token');
        },
    },
});
