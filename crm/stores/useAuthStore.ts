import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', () => {
  const forever = { maxAge: 60 * 60 * 24 * 1000 }
  const user = ref<AuthResource>()
  const token = useCookie('token', forever)
  const rememberUser = useCookie<RememberUser | undefined>('remember-user', forever)
  const previewToken = useCookie('preview-token')
  const isAdmin = ref(false)
  const isClient = ref(false)
  const isTeacher = ref(false)
  const isPreviewMode = !!previewToken.value

  /**
   *
   * @param u Пользователь будет записан в useAuthStore
   * @param t Токен для сохранения в Cookies
   * @param preview Если true, токен сохранится в Cookies "preview-token". Иначе в "token"
   */
  function logIn(u: AuthResource, t: string, preview: boolean = false) {
    user.value = u
    if (preview) {
      previewToken.value = t

      // сохранить path перед переходом в режим просмотра,
      // чтобы после выхода из режима просмотра вернуться на исходную страницу
      sessionStorage.setItem('redirect', useRoute().fullPath)
      setTimeout(() => window.location.href = '/')
    }
    else {
      const redirectTo = isPreviewMode
        ? (sessionStorage.getItem('redirect') || '/')
        : '/'
      previewToken.value = undefined
      token.value = t
      setTimeout(() => window.location.href = redirectTo)
      // navigateTo({ path })
    }
  }

  /**
   * u Пользователь будет записан в useAuthStore
   * t Токен для сохранения в Cookies
   * phone Номер телефона для rememberUser
   */
  function logInAndRemember({ token: t, user: u, phone }: TokenResponse) {
    // учителя не сохраняем в rememberUser
    if (u.entity_type !== EntityTypeValue.teacher) {
      rememberUser.value = {
        ...u,
        number: phone.number,
      }
    }

    logIn(u, t)
  }

  function clearCurrentToken() {
    getCurrentToken().value = undefined
  }

  function getCurrentToken() {
    return previewToken.value ? previewToken : token
  }

  // Если в режиме просмотра, получить исходный токен (под кем мы сидим в режиме просмота?)
  function getOriginalToken() {
    return token.value
  }

  async function logOut() {
    await useHttp(`pub/auth/logout`)
    clearCurrentToken()
    const path = sessionStorage.getItem('redirect') || '/'
    window.location.href = path
  }

  async function getLoggedUser() {
    const { data } = await useHttp<AuthResource>(`pub/auth/user`)
    if (data.value) {
      const entityType = data.value.entity_type
      isAdmin.value = entityType === EntityTypeValue.user
      isClient.value = (entityType === EntityTypeValue.client) || (entityType === EntityTypeValue.representative)
      isTeacher.value = entityType === EntityTypeValue.teacher
      user.value = data.value
    }
  }

  return {
    user,
    rememberUser,
    isAdmin,
    isClient,
    isTeacher,
    isPreviewMode,
    logIn,
    logInAndRemember,
    logOut,
    getCurrentToken,
    clearCurrentToken,
    getLoggedUser,
    getOriginalToken,
  }
})
