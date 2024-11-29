<template>
    <div>
        <h1>Hello, Vue 3 + Laravel!</h1>
        <router-link to="/home">Home</router-link>
        <router-view></router-view>
    </div>
</template>

<script>
export default {
    name: 'App',
};
</script>

<style scoped>
h1 {
    color: #42b983;
}
</style>
