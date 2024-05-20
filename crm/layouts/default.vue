<template>
  <ClientOnly>
    <v-app>
      <v-navigation-drawer
        permanent
        absolute
      >
        <UiMenu />
      </v-navigation-drawer>
      <v-main>
        <NuxtPage />
      </v-main>
    </v-app>
  </ClientOnly>
</template>
