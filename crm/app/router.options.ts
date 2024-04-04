import type { RouterConfig } from "@nuxt/schema"

export default <RouterConfig>{
  // https://router.vuejs.org/api/interfaces/routeroptions.html#routes
  routes: (_routes) => {
    // console.log("SMENTOVSKY", _routes)
    // debugger
    return _routes
    // return [
    //   {
    //     name: "home",
    //     path: "/",
    //     component: () => import("~/pages/home.vue").then((r) => r.default || r),
    //   },
    // ]
  },
}
