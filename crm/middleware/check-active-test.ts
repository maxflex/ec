// Если у клиента есть активный тест,
// то делаем редирект на страницу прохождения теста
export default defineNuxtRouteMiddleware(async () => {
  const { data } = await useHttp<ActiveTest>(`tests/active`)
  if (data.value?.test) {
    return navigateTo({ name: 'tests-active' })
  }
})
