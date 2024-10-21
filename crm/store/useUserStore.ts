import { defineStore } from 'pinia'

export const useUserStore = defineStore('user', () => {
  const users = ref<UserResource[]>()

  async function loadData() {
    const { data } = await useHttp<ApiResponse<UserResource>>(`users`)
    if (data.value) {
      users.value = data.value.data.sort((a, b) => Number(b.is_active) - Number(a.is_active))
    }
  }

  loadData()

  return {
    users,
  }
})
