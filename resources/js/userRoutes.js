
import Dashboard from './components/user/Dashboard.vue'
import QuickTest from './components/user/QuickTest.vue'
export default[
    {
        path: '/_user/dashboard',
        name: 'user_dashboard',
        component: Dashboard,
    }
    ,
    {
        path: '/_user/quicktest',
        name: 'quick_test',
        component: QuickTest,
    }
]