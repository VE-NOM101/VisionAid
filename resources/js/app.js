import './bootstrap';
import 'admin-lte/plugins/bootstrap/js/bootstrap.bundle.min.js';
import 'admin-lte/dist/js/adminlte.min.js';


import { createApp } from 'vue/dist/vue.esm-bundler.js';
import { createRouter, createWebHistory } from 'vue-router';

import { createPinia } from 'pinia'
const pinia = createPinia()

// Import Routes and Components
import AdminRoutes from './adminRoutes.js';
import DoctorRoutes from './doctorRoutes.js';
import UserRoutes from './userRoutes.js';
import Admin from './Admin.vue';
import Doctor from './Doctor.vue';
import User from './User.vue';

// Fetch the authenticated user's role from the API
async function getAuthenticatedUserRole() {
    try {
        const response = await axios.get('/api/auth/role');
        return response.data.role; // e.g., 'admin', 'doctor', or 'user'
    } catch (error) {
        console.error('Error fetching user role:', error);
        return null; // Handle errors gracefully
    }
}

// Initialize the app based on the user's role
getAuthenticatedUserRole().then((role) => {
    if (role === 'admin') {
        const admin = createApp(Admin);
        const routerAdmin = createRouter({
            routes: AdminRoutes,
            history: createWebHistory(),
        });
        admin.use(routerAdmin);
        admin.use(pinia);
        admin.mount('#admin');
    } else if (role === 'doctor') {
        const doctor = createApp(Doctor);
        const routerDoctor = createRouter({
            routes: DoctorRoutes,
            history: createWebHistory(),
        });
        doctor.use(routerDoctor);
        doctor.use(pinia);
        doctor.mount('#doctor');
    } else if (role === 'user') {
        const user = createApp(User);
        const routerUser = createRouter({
            routes: UserRoutes,
            history: createWebHistory(),
        });
        user.use(routerUser);
        user.use(pinia);
        user.mount('#user');
    } else {
        // Redirect to login if role is invalid or not found
        window.location.href = '/login';
    }
});
