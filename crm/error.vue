<script setup lang="ts">
import type { NuxtError } from '#app'

const { error } = defineProps({
  error: Object as () => NuxtError,
})
</script>

<template>
  <ClientOnly>
    <v-app>
      <v-navigation-drawer
        permanent
        absolute
      >
        <MenuBase />
      </v-navigation-drawer>
      <v-main v-if="error" class="error-page">
        <h1 class="error-page__header">
          {{ error.statusCode }}
        </h1>
      </v-main>
    </v-app>
  </ClientOnly>
</template>

<style lang="scss">
.error-page {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  gap: 20px;
  &__header {
    font-size: 200px;
    font-weight: 500 !important;
  }
}
</style>
