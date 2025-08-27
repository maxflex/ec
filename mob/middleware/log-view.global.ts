export default defineNuxtRouteMiddleware((to) => {
  // skip middleware on server
  if (import.meta.server) {
    return
  }

  const { user } = useAuthStore()
  if (!user || to.name === 'login' || to.fullPath === '/') {
    return
  }

  useHttp(`logs`, {
    method: 'post',
    body: {
      url: to.fullPath,
    },
  })
})
