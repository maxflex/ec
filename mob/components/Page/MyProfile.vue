<script setup lang="ts">
const { user, logOut } = useAuthStore()
const { $isTgMiniApp } = useNuxtApp()
</script>

<template>
  <UiPageTitle>
    Мой профиль
  </UiPageTitle>
  <div v-if="user" class="my-profile">
    <div class="my-profile__head">
      <div>
        <UiAvatar :item="user" :size="100" />
      </div>
      <div>
        <h4>{{ formatFullName(user) }}</h4>
        <div class="text-subtitle-1 text-gray">
          {{ EntityTypeLabel[user.entity_type] }}
        </div>
      </div>
    </div>
    <v-btn v-if="!$isTgMiniApp" color="primary" style="width: 300px" @click="logOut()">
      выйти из профиля
    </v-btn>
  </div>
</template>

<style lang="scss">
.my-profile {
  padding: 20px;
  &__head {
    display: flex;
    gap: 30px;
    margin-bottom: 50px;
  }
}
</style>
