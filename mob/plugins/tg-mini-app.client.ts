export default defineNuxtPlugin(() => {
  // @ts-expect-error
  const isTgMiniApp = !!window.Telegram?.WebApp?.initData

  if (isTgMiniApp) {
    nextTick(() => document.documentElement.classList.add('tg-mini-app'))
  }

  return {
    provide: {
      isTgMiniApp,
    },
  }
})
