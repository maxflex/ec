<script setup lang="ts">
// No script logic needed for just the animation
</script>

<template>
  <div class="sound-wave">
    <div v-for="index in 3" :key="index" class="bar" />
  </div>
</template>

<style scoped>
.sound-wave {
  display: flex;
  align-items: flex-end;
  gap: 8px; /* Increased gap for better visual balance */
}

.bar {
  width: 8px; /* Slightly wider bars for better visibility */
  height: 20px;
  background-color: #007bff;
  animation: wave 1.2s infinite cubic-bezier(0.4, 0, 0.2, 1); /* Adjusted timing function for smoothness */
}

/* Delayed animation to create a wave effect */
.bar:nth-child(2) {
  animation-delay: 0.2s;
}

.bar:nth-child(3) {
  animation-delay: 0.4s;
}

/* Keyframes for smooth height transitions */
@keyframes wave {
  0%,
  100% {
    height: 20px;
  }
  50% {
    height: 40px;
  }
}
</style>
