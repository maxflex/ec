<script setup lang="ts">
const route = useRoute()
const { user, isClient } = useAuthStore()
const { $isTgMiniApp } = useNuxtApp()

const currentPageClass = computed(() => [
  $isTgMiniApp ? 'tg-mini-app' : '',
  `page-${(route.name as string) || 'default'}`,
  `entity-${user ? getEntityString(user.entity_type) : 'default'}`,
])
</script>

<template>
  <ClientOnly>
    <v-app>
      <v-main v-if="isClient || user?.id === 5" :class="currentPageClass">
        <AppHeader />
        <NuxtPage />
      </v-main>
      <PageOnlyDesktop v-else />
    </v-app>
    <TgMiniAppControls />
  </ClientOnly>
</template>
