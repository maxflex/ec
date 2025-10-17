/**
 * Открытие внутри ссылок из бота в контексте TG mini app
 */
export default defineNuxtRouteMiddleware((to) => {
  // server only
  if (!import.meta.server) {
    return
  }

  console.log('hia')

  const config = useRuntimeConfig()
  const { tgWebAppStartParam } = to.query

  if (config.public.isTgMiniApp && tgWebAppStartParam) {
    console.log('navigateTo', (tgWebAppStartParam as string).replaceAll('_', '/'))
    return navigateTo((tgWebAppStartParam as string).replaceAll('_', '/'))
  }
})
