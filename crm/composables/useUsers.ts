const users = ref<UserResource[]>()

export default function () {
  async function loadData() {
    const { data } = await useHttp<ApiResponse<UserResource>>(`users`)
    if (data.value) {
      users.value = data.value.data.sort((a, b) => Number(b.is_active) - Number(a.is_active))
    }
  }
  if (users.value === undefined) {
    nextTick(loadData)
  }
  return users
}
