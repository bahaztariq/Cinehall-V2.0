import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/Home.vue';
import Login from '../views/Login.vue';
import Register from '../views/Register.vue';
import FilmDetail from '../views/FilmDetail.vue';
import Reservation from '../views/Reservation.vue';
import Profile from '../views/Profile.vue';
import NotFound from '../views/NotFound.vue';
import Favourites from '../views/Favourites.vue';

// Admin Views
import AdminDashboard from '../views/Admin/Dashboard.vue';
import AdminUsers from '../views/Admin/Users.vue';
import AdminFilms from '../views/Admin/Films.vue';
import AdminSessions from '../views/Admin/Sessions.vue';
import AdminRooms from '../views/Admin/Rooms.vue';

import { useAuthStore } from '../store/auth';

const routes = [
    {
        path: '/',
        name: 'home',
        component: Home
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: { guest: true }
    },
    {
        path: '/Film/:id',
        name: 'film-detail',
        component: FilmDetail
    },
    {
        path: '/reservation/:sessionId',
        name: 'reservation',
        component: Reservation,
        meta: { requiresAuth: true }
    },
    {
        path: '/profile',
        name: 'profile',
        component: Profile,
        meta: { requiresAuth: true }
    },
    {
        path: '/favourites',
        name: 'favourites',
        component: Favourites,
        meta: { requiresAuth: true }
    },
    // Admin Routes
    {
        path: '/admin',
        children: [
            { path: '', name: 'admin-dashboard', component: AdminDashboard },
            { path: 'users', name: 'admin-users', component: AdminUsers },
            { path: 'films', name: 'admin-films', component: AdminFilms },
            { path: 'sessions', name: 'admin-sessions', component: AdminSessions },
            { path: 'rooms', name: 'admin-rooms', component: AdminRooms },
        ],
        meta: { requiresAdmin: true }
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'notFound',
        component: NotFound
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach((to, from, next) => {
    const auth = useAuthStore();
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
    const requiresAdmin = to.matched.some(record => record.meta.requiresAdmin);
    const isGuest = to.matched.some(record => record.meta.guest);

    if (requiresAdmin && (!auth.isAuthenticated || !auth.isAdmin)) {
        next('/');
    } else if (requiresAuth && !auth.isAuthenticated) {
        next('/login');
    } else if (isGuest && auth.isAuthenticated) {
        next('/');
    } else {
        next();
    }
});

export default router;
