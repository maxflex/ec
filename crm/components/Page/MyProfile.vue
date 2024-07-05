<script setup lang="ts">
const { user, logOut } = useAuthStore()
</script>

<template>
  <div v-if="user" class="my-profile">
    <div class="my-profile__head">
      <UiAvatar :item="user" :size="120" />
      <div>
        <h2>{{ formatFullName(user) }}</h2>
        <div class="text-subtitle-1 text-gray">
          {{ EntityTypeLabel[user.entity_type] }}
        </div>
      </div>
    </div>
		<v-btn color="primary" style="width: 300px" @click="logOut()">
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
		margin-bottom: 50px
	}
}
</style>
