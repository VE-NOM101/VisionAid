
import Dashboard from './components/admin/Dashboard.vue'
import Users from './components/admin/Users.vue'
export default[
    {
        path: '/_admin/dashboard',
        name: 'admin_dashboard',
        component: Dashboard,
    }
    ,
    {
        path: '/_admin/users',
        name: 'admin_users',
        component: Users,
    }
]