<script setup lang="ts">
const { user } = useAuthStore()
const entityType = user!.entity_type
</script>

<template>
  <div class="menu-logo">
    <img src="/img/logo.svg">
    <h3>ЕГЭ-Центр</h3>
  </div>
  <MenuAdmin v-if="entityType === EntityType.user" />
  <MenuTeacher v-else-if="entityType === EntityType.teacher" />
  <MenuClient v-else />
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
