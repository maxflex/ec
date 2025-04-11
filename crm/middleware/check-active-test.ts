// Если у клиента есть активный тест,

import type { ClientTestResource } from '~/components/ClientTest'

// то делаем редирект на страницу прохождения теста
export default defineNuxtRouteMiddleware(async () => {
  const { data } = await useHttp<ClientTestResource>(`client-tests/active`)
  if (data.value) {
    return navigateTo({ name: 'tests-active' })
  }
})
