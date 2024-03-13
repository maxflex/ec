import { useMainStore } from "@/store"

export default defineNuxtPlugin(async () => {
  const store = useMainStore()
  const { data, error } = await useHttp("auth/user")
  // console.log({ error: error.value })
  // console.log({ data: data.value })
  if (data.value) {
    store.user = data.value.user
  }
})
