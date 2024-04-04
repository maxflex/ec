import type { RouterConfig } from "@nuxt/schema"
import { getCookie, getEntityString } from "~/utils"

export default <RouterConfig>{
  // https://router.vuejs.org/api/interfaces/routeroptions.html#routes
  routes(routes) {
    // routes in root /pages
    // (index.vue, login.vue)
    const myRoutes = routes.filter(
      // @ts-expect-error
      (r) => r.name.indexOf("-") === -1,
    )

    const user = getCookie("user") as User
    if (user !== null) {
      const entityString = getEntityString(user)

      routes
        // @ts-expect-error
        .filter((r) => r.name.startsWith(entityString))
        .forEach((r) =>
          myRoutes.push({
            ...r,
            // admin-clients-id => clients-id
            // @ts-expect-error
            name: r.name.replace(entityString + "-", ""),
            // /admin/clients/:id() => /clients/:id()
            path: r.path.replace(entityString + "/", ""),
          }),
        )
      // debugger
    }
    return myRoutes
  },
}
