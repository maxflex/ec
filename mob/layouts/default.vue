<script setup lang="ts">
const route = useRoute()
const { user } = useAuthStore()

const currentPageClass = computed(() => [
  `page-${(route.name as string) || 'default'}`,
  `entity-${user ? getEntityString(user.entity_type) : 'default'}`,
])

const { globalMessage } = useGlobalMessage()
</script>

<template>
  <ClientOnly>
    <v-app>
      <v-main :class="currentPageClass">
        <AppHeader />
        <NuxtPage />
      </v-main>
      <UiBottomBar v-model="globalMessage.value" :color="globalMessage.color">
        {{ globalMessage.text }}
      </UiBottomBar>
    </v-app>
  </ClientOnly>
</template>
