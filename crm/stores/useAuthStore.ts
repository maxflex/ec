import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', () => {
  const forever = { maxAge: 60 * 60 * 24 * 1000 }
  const user = ref<AuthResource>()
  const token = useCookie('token', forever)
  const rememberUser = useCookie<RememberUser | undefined>('remember-user', forever)
  const previewToken = useCookie('preview-token')
  const redirectCookie = useCookie('redirect')
  const isAdmin = ref(false)
  const isClient = ref(false)
  const isTeacher = ref(false)
  const isStudent = ref(false)
  const isRepresentative = ref(false)
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
      const route = useRoute()
      previewToken.value = t
      /**
       * Сохранить path перед переходом в режим просмотра,
       * чтобы после выхода из режима просмотра вернуться на исходную страницу
       */
      saveRedirectUrl(route.fullPath)

      return redirect('/')
    }

    const url = redirectCookie.value || '/'
    previewToken.value = undefined
    redirectCookie.value = undefined
    token.value = t

    redirect(url)
  }

  function redirect(url: string) {
    setTimeout(() => window.location.href = url)
  }

  /**
   * Сохраняем URL для редиректа после успешного логина
   */
  function saveRedirectUrl(url: string) {
    redirectCookie.value = url
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
    redirectCookie.value = undefined
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
    const url = redirectCookie.value || '/'
    clearCurrentToken()

    redirect(url)
  }

  async function getLoggedUser() {
    const { data } = await useHttp<AuthResource>(`pub/auth/user`)
    if (data.value) {
      switch (data.value.entity_type) {
        case EntityTypeValue.client:
          isClient.value = true
          isStudent.value = true
          break

        case EntityTypeValue.representative:
          isClient.value = true
          isRepresentative.value = true
          break

        case EntityTypeValue.teacher:
          isTeacher.value = true
          break

        case EntityTypeValue.user:
          isAdmin.value = true
          break
      }

      user.value = data.value
    }
  }

  return {
    user,
    rememberUser,
    isAdmin,
    isClient,
    isTeacher,
    isStudent,
    isRepresentative,
    isPreviewMode,
    logIn,
    logInAndRemember,
    logOut,
    getCurrentToken,
    clearCurrentToken,
    getLoggedUser,
    getOriginalToken,
    saveRedirectUrl,
  }
})
