import Profile from './components/profile/Profile.vue'
import Dashboard from './components/doctor/Dashboard.vue'
export default[
    {
        path: '/_doctor/dashboard',
        name: 'doctor_dashboard',
        component: Dashboard,
    }
    ,
    {
        path: '/_doctor/profile',
        name: 'doctor_profile',
        component: Profile,
    }
]