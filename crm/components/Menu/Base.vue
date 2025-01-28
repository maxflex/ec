<script setup lang="ts">
const { isAdmin, isTeacher } = useAuthStore()
</script>

<template>
  <RouterLink to="/" class="menu-logo">
    <img alt="" src="/img/logo.svg">
    <h3>ЕГЭ-Центр</h3>
  </RouterLink>
  <MenuAdminMenu v-if="isAdmin" />
  <MenuTeacherMenu v-else-if="isTeacher" />
  <MenuClientMenu v-else />
  <AppUpdateBtn />
</template>

<style lang="scss">
.menu-logo {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 20px 0;
  transition: color 0.25s cubic-bezier(0.25, 0.8, 0.25, 1);
  //&:not(:hover) {
  //  color: #212121 !important;
  //}
  color: #212121 !important;
  img {
    margin-right: 12px;
  }
  span {
    font-size: 24px;
    font-weight: 500;
  }
}
</style>
