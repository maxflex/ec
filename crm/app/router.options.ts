import type { RouterConfig } from '@nuxt/schema'

export default <RouterConfig>{
  // https://router.vuejs.org/api/interfaces/routeroptions.html#routes
  routes(routes) {
    // if (import.meta.client) return
    // routes in root /pages (index.vue, login.vue)
    // upd. index.vue - removed (actually moved to each entity's root folder)
    const myRoutes = routes.filter(
      // @ts-expect-error
      r => !r.name.includes('-'),
    )
    const entityString = getEntityStringFromToken()
    // console.log({ entityString })
    if (entityString) {
      routes
        // @ts-expect-error
        .filter(r => r.name.startsWith(entityString))
        .forEach((r) => {
          // handle index.vue for each entity
          if (r.name === entityString) {
            myRoutes.push({
              ...r,
              name: 'index',
              path: '/',
            })
            return
          }
          myRoutes.push({
            ...r,
            // admin-clients-id => clients-id
            // @ts-expect-error
            name: r.name.replace(`${entityString}-`, ''),
            // /admin/clients/:id() => /clients/:id()
            path: r.path.replace(`${entityString}/`, ''),
          })
        })
    }
    return myRoutes
  },
}
