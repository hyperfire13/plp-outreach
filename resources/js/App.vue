<template>
  <LoadingOverlay />
  <router-view />
</template>

<script setup>
import LoadingOverlay from './components/shared/LoadingOverlay.vue'
import { onMounted } from 'vue'

onMounted(() => {
  document.body.classList.add(
    'hold-transition',
    'sidebar-mini',
    'layout-fixed'
  )
})
</script>