import { defineStore } from "pinia"

export const useAuthStore = defineStore("auth", () => {
  const user = ref<User>()
  function logIn(u: User) {
    user.value = u
  }
  function logOut() {
    user.value = undefined
  }
  return { user, logIn, logOut }
})
