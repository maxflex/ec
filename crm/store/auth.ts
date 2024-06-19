import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', () => {
  const forever = { maxAge: 60 * 60 * 24 * 1000 }
  const user = ref<User>()
  const token = useCookie('token', forever)
  const previewToken = useCookie('preview-token')
  const rememberUser = useCookie<User | undefined>('remember-user', forever)

  /**
   *
   * @param u Пользователь будет записан в useAuthStore
   * @param t Токен для сохранения в Cookies
   * @param preview Если true, токен сохранится в Cookies "preview-token". Иначе в "token"
   */
  function logIn(u: User, t: string, preview: boolean = false) {
    user.value = u
    if (preview) {
      previewToken.value = t

      // сохранить path перед переходом в режим просмотра,
      // чтобы после выхода из режима просмотра вернуться на исходную страницу
      sessionStorage.setItem('redirect', useRoute().fullPath)
      window.location.href = '/'
    }
    else {
      token.value = t
      // если не учитель, сохраняем в remember me
      if (u.entity_type !== EntityType.teacher) {
        rememberUser.value = u
      }
      window.location.href = sessionStorage.getItem('redirect') || '/'
      // navigateTo({ path })
    }
  }

  function clearCurrentToken() {
    getCurrentToken().value = undefined
  }

  function getCurrentToken() {
    return previewToken.value ? previewToken : token
  }

  async function logOut() {
    await useHttp('common/auth/logout')
  }

  async function getLoggedUser() {
    const { data } = await useHttp<User>('common/auth/user')
    if (data.value) {
      user.value = data.value
    }
  }

  return {
    user,
    rememberUser,
    logIn,
    logOut,
    getCurrentToken,
    clearCurrentToken,
    getLoggedUser,
  }
})
