<script setup lang="ts">
const { isAdmin, isTeacher } = useAuthStore()
</script>

<template>
  <MenuAdminMenu v-if="isAdmin" />
  <MenuTeacherMenu v-else-if="isTeacher" />
  <MenuClientMenu v-else />
  <AppUpdateBtn />
</template>

<style lang="scss">
.v-bottom-navigation {
  $height: 50px !important;
  position: sticky !important;
  bottom: 0;
  height: $height;
  min-height: $height;
  &__content {
    & > a {
      min-width: initial !important;
      padding: 0 !important;
      color: black !important;
    }
  }
}
</style>
