<script>

export default {

    setup() {
        const gotoHome = () => {
            window.location.href = '/';
        };

        const gotoLogout = () => {
            window.location.href = '/logout';
        };


        return {
            gotoLogout,
            gotoHome,
        };
    },
}

</script>

<template>
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <button @click="gotoHome" class="btn btn-block btn-outline-primary nav-link">Home</button>
            </li>
            <li class="nav-item d-none d-sm-inline-block ml-2">
                <button @click="gotoLogout" class="btn btn-block btn-outline-danger nav-link">Logout</button>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                    <i class="fas fa-th-large"></i>
                </a>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->
</template>