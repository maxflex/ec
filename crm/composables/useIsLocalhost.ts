export default function () {
  // Единая точка проверки локального окружения для всего фронтенда.
  const { public: { env } } = useRuntimeConfig()

  return env === 'local'
}
