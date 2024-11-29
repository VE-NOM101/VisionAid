import * as VueRouter from 'vue-router';
import Ex from './components/Ex.vue';
const routes = [
    {path: '/home',component: Ex}
];

const router = VueRouter.createRouter({
    history: VueRouter.createWebHistory(),
    routes,
});

export default router;