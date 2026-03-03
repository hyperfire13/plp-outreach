<template>
  <LoadingOverlay />
  <router-view />
</template>

<script setup>
import LoadingOverlay from './components/shared/LoadingOverlay.vue'
import { onMounted } from 'vue'

onMounted(() => {
  document.body.classList.add(
    'layout-fixed',
    'sidebar-expand-lg',
    'bg-body-tertiary'
  )
})
</script>