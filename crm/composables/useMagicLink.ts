export default function () {
  const route = useRoute()
  const hasError = ref(false)
  const { logInAndRemember } = useAuthStore()
  const magicLink = ('link' in route.query)
    ? route.query.link
    : null

  async function authViaMagicLink() {
    const { data, error } = await useHttp<TokenResponse>(
      `common/auth/magic-link`,
      {
        method: 'post',
        body: {
          link: magicLink,
        },
      },
    )

    if (error.value) {
      hasError.value = true
      return
    }

    logInAndRemember(data.value!)
  }

  return {
    hasMagicLink: magicLink !== null,
    hasError,
    authViaMagicLink,
  }
}
