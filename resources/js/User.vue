<script setup>
import Footer from './components/layout/Footer.vue';
import LeftSideBar from './components/layout/LeftSideBar.vue';
import NavBar from './components/layout/NavBar.vue';
import RightSideBar from './components/layout/RightSideBar.vue';


</script>

<template>
    <NavBar />
    <LeftSideBar />
    <div class="content-wrapper">
        <router-view></router-view>
    </div>
    <RightSideBar />
    <Footer />
</template>
