<template>
  <ClientOnly>
    <v-app>
      <v-navigation-drawer
        permanent
        absolute
      >
        <MenuBase />
      </v-navigation-drawer>
      <v-main>
        <NuxtPage />
      </v-main>
    </v-app>
  </ClientOnly>
</template>
