<template>
  <div v-if="loading" class="overlay">
    <div class="spinner-border text-primary"></div>
  </div>
</template>

<script setup>
import { useLoadingStore } from '../../stores/loading'
import { storeToRefs } from 'pinia'

const store = useLoadingStore()
const { loading } = storeToRefs(store)
</script>

<style>
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(255,255,255,0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}
</style>