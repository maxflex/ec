<script setup lang="ts">
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
      <v-navigation-drawer
        permanent
        absolute
      >
        <MenuBase />
      </v-navigation-drawer>
      <v-main :class="currentPageClass">
        <NuxtPage />
      </v-main>
    </v-app>
  </ClientOnly>
</template>
