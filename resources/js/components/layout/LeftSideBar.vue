<script>
import axios from "axios";
import { ref, onMounted } from "vue";

export default {
    setup() {
        const detail = ref({ role: "Guest", name: "Guest User" }); // Initialize with default values

        const getRole = () => {
            axios
                .get('/api/auth/role')
                .then((response) => {
                    detail.value = response.data; // Update reactive object with API data
                })
                .catch((error) => {
                    console.error("Failed to fetch role:", error);
                });
        };

        onMounted(() => {
            getRole();
        });

        return {
            detail,
        };
    },
};
</script>




<template>
    <aside class="main-sidebar sidebar-dark-warning elevation-4">
        <!-- Brand Logo -->
        <a href="index3.html" class="brand-link">
            <img src="https://adminlte.io/themes/v3/dist/img/AdminLTELogo.png" alt="AdminLTE Logo"
                class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light" v-if="detail.role === 'admin'">
                Admin
            </span>
            <span class="brand-text font-weight-light" v-if="detail.role === 'doctor'">
                Doctor
            </span>
            <span class="brand-text font-weight-light" v-if="detail.role === 'user'">
                User
            </span>

        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg" class="img-circle elevation-2"
                        alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block">{{ detail.name }}</a>
                </div>
            </div>

            <!-- SidebarSearch Form -->
            <div class="form-inline">
                <div class="input-group" data-widget="sidebar-search">
                    <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                        aria-label="Search">
                    <div class="input-group-append">
                        <button class="btn btn-sidebar">
                            <i class="fas fa-search fa-fw"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                    data-accordion="false" v-if="detail.role === 'admin'">
                    <!-- Add icons to the links using the .nav-icon class
             with font-awesome or any other icon font library -->
                    <li class="nav-item">
                        <router-link to="/_admin/dashboard" active-class="active" class="nav-link">
                            <i class="nav-icon fas fa-th"></i>
                            <p>
                                Dashboard
                            </p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/_admin/users" active-class="active" class="nav-link">
                            <i class="nav-icon fas fa-user"></i>
                            <p>
                                Users
                            </p>
                        </router-link>
                    </li>
                </ul>

                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                    data-accordion="false" v-if="detail.role === 'doctor'">
                    <!-- Add icons to the links using the .nav-icon class
             with font-awesome or any other icon font library -->
                    <li class="nav-item">
                        <router-link to="" active-class="active" class="nav-link">
                            <i class="nav-icon fas fa-th"></i>
                            <p>
                                Dashboard
                            </p>
                        </router-link>
                    </li>
                </ul>


                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                    data-accordion="false" v-if="detail.role === 'user'">
                    <!-- Add icons to the links using the .nav-icon class
             with font-awesome or any other icon font library -->
                    <li class="nav-item">
                        <router-link to="" active-class="active" class="nav-link">
                            <i class="nav-icon fas fa-th"></i>
                            <p>
                                Dashboard
                            </p>
                        </router-link>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

</template>