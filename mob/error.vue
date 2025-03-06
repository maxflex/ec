<script setup lang="ts">
import type { NuxtError } from '#app'
import { mdiCancel } from '@mdi/js'

const { error } = defineProps({
  error: Object as () => NuxtError,
})
</script>

<template>
  <ClientOnly>
    <v-app>
      <v-main v-if="error" class="error-page">
        <h1 v-if="error.statusCode === 404" class="error-page__header">
          {{ error.statusCode }}
        </h1>
        <span v-else>
          <div>
            <v-icon :icon="mdiCancel" />
          </div>
          У вас нет доступа к этой странице
        </span>
      </v-main>
      <MenuBase />
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
    font-size: 100px;
    font-weight: 500 !important;
  }
  & > span {
    font-size: 20px;
    display: flex;
    align-items: center;
    flex-direction: column;
    gap: 20px;
    .v-icon {
      font-size: 80px;
      opacity: 0.3;
      color: rgb(var(--v-theme-gray));
    }
  }
}
</style>
