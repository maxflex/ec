import type { RouterConfig } from "@nuxt/schema"

export default <RouterConfig>{
  // https://router.vuejs.org/api/interfaces/routeroptions.html#routes
  routes(routes) {
    if (import.meta.server) return
    debugger
    // const { user } = useAuthStore()
    // debugger
    // const config = useRuntimeConfig()
    // const response = await fetch(`${config.public.baseUrl}/auth/login`, {
    //   headers: {
    //     Authorization: `Bearder sdfsdfsdf`,
    //   },
    // })
    // console.log(response)
    return routes
  },
}
