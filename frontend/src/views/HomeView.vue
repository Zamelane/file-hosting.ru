<template>
  <div class="container-xl main">
    <NavBar />
    <hr />
    <router-view />
    <div v-if="router.currentRoute._value.path == '/'" class="container d-flex gap-2 flex-wrap justify-content-center">
      <div class="card" style="max-width: 18rem;" v-for="(card, key) in cards" :key="key">
        <div class="card-body">
          <h5 class="card-title">{{ card.title }}</h5>
          <p class="card-text">{{ card.description }}</p>
          <router-link :to="card.to" class="btn btn-primary">{{ card.link_text }}</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import NavBar from '@/components/NavBar.vue';
import router from '@/router';

const cards = [
  {
    title: 'Загружайте файлы',
    description: 'Теперь на нашем сайте есть возможность загружать свои файлы!',
    link_text: 'Загрузить',
    to: 'upload'
  },
  {
    title: 'Свои файлы',
    description: 'Теперь на нашем сайте есть возможность просматривать свои файлы!',
    link_text: 'Подробнее',
    to: 'myfiles'
  },
  {
    title: 'Делитесь файлами',
    description: 'Теперь вы можете предоставить доступ к файлу другому пользователю. Дерзайте!',
    link_text: 'Попробовать',
    to: 'myfiles'
  },
]

</script>

<style>
.main {
  padding: 10px;
}
</style>