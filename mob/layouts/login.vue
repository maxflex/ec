<script setup lang="ts">
const config = useRuntimeConfig()
</script>

<template>
  <v-app class="login-layout" :class="{ 'tg-mini-app': config.public.isTgMiniApp }">
    <ClientOnly>
      <slot />
    </ClientOnly>
  </v-app>
</template>
