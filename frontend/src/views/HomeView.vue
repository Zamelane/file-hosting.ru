<template>
  <div class="container-xl main">
    <NavBar/>
    <hr/>
    <router-view/>
  </div>
</template>

<script setup>
import NavBar from '@/components/NavBar.vue';
</script>

<style>
.main {
  padding: 10px;
}
</style>