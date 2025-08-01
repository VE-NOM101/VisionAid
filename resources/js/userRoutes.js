import Profile from "./components/profile/Profile.vue";
import Dashboard from "./components/user/Dashboard.vue";
import QuickTest from "./components/user/QuickTest.vue";
import Gemini from "./components/user/Gemini.vue";
import DeepTest from "./components/user/DeepTest.vue";
import Archive from "./components/user/Archive.vue";
import ProgressionTracking from "./components/user/ProgressionTracking.vue";
import ProgressionTimeline from "./components/user/ProgressionTimeline.vue";
import TryOn from "./components/user/tryOn/TryOn.vue";

export default [
    {
        path: "/_user/dashboard",
        name: "user_dashboard",
        component: Dashboard,
    },
    {
        path: "/_user/quicktest",
        name: "quick_test",
        component: QuickTest,
    },
    {
        path: "/_user/gemini",
        name: "gemini",
        component: Gemini,
    },
    {
        path: "/_user/profile",
        name: "user_profile",
        component: Profile,
    },
    {
        path: "/_user/deeptest",
        name: "deep_test",
        component: DeepTest,
    },
    {
        path: "/_user/archive/:id",
        name: "archive",
        component: Archive,
        props: true,
    },
    {
        path: "/_user/progression-tracking/",
        name: "progression_tracking",
        component: ProgressionTracking,
        props: true,
    },

    {
        path: "/_user/progression-timeline/",
        name: "progression_timeline",
        component: ProgressionTimeline,
        props: true,
    },
    {
        path: "/_user/try-on/",
        name: "TryOn",
        component: TryOn,
        props: true,
    },
];
