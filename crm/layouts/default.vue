<script setup lang="ts">
import { globalMessage } from '~/composables/useGlobalMessage'

const route = useRoute()
const { user } = useAuthStore()

const currentPageClass = computed(() => [
  `page-${(route.name as string) || 'default'}`,
  `entity-${user ? getEntityString(user.entity_type) : 'default'}`,
])
</script>

<template>
  <ClientOnly>
    <v-app>
      <v-navigation-drawer permanent>
        <MenuBase />
      </v-navigation-drawer>
      <v-main :class="currentPageClass">
        <NuxtPage />
      </v-main>
      <UiBottomBar v-model="globalMessage.value" :color="globalMessage.color">
        {{ globalMessage.text }}
      </UiBottomBar>
    </v-app>
  </ClientOnly>
</template>
