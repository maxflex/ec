<script setup lang="ts">
const { isAdmin, isTeacher } = useAuthStore()
</script>

<template>
  <div class="menu-logo">
    <RouterLink to="/mango-test">
      <img src="/img/logo.svg">
    </RouterLink>
    <h3>ЕГЭ-Центр</h3>
  </div>
  <MenuAdminMenu v-if="isAdmin" />
  <MenuTeacherMenu v-else-if="isTeacher" />
  <MenuClientMenu v-else />
</template>

<style lang="scss">
.menu-logo {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px 0;
  img {
    margin-right: 12px;
  }
  span {
    font-size: 24px;
    font-weight: 500;
  }
}
</style>
