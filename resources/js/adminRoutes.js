
import Dashboard from './components/admin/Dashboard.vue'
import Symptoms from './components/admin/Symptoms.vue'
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
    ,
    {
        path: '/_admin/symptoms',
        name: 'admin_symptoms',
        component: Symptoms,
    }
]