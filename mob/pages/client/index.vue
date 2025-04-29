<script setup lang="ts">
const router = useRouter()
const { rememberUser, isPreviewMode } = useAuthStore()
const showGreeting = ref(false)

async function redirect() {
  await router.replace({ name: 'journal' })
}

async function ok() {
  localStorage.setItem('show-greeting', 'true')
  await redirect()
}

nextTick(async () => {
  if (localStorage.getItem('show-greeting') || isPreviewMode) {
    await redirect()
    return
  }
  showGreeting.value = true
})
</script>

<template>
  <v-fade-transition>
    <div v-if="showGreeting" class="client-greeting">
      <UiPageTitle>
        {{ rememberUser!.first_name }}, здравствуйте!
      </UiPageTitle>
      <p>
        В этом личном кабинете вы сможете видеть:
      </p>
      <ul>
        <li>
          расписание на год
        </li>
        <li>
          отчеты преподавателей
        </li>
        <li>
          темы занятий и домашнее задание
        </li>
        <li>
          расписание внеклассных мероприятий
        </li>
        <li>
          оценки на занятиях с комментариями и четвертные оценки
        </li>
        <li>
          журнал посещений с опозданиями и пропусками уроков
        </li>
      </ul>
      <p>
        В телеграмм-боте:
      </p>
      <ul>
        <li>
          почти все то же самое, только в виде сообщений
        </li>
      </ul>
      <p>
        <v-btn color="primary" width="300" class="mt-10" size="x-large" @click="ok()">
          понятно
        </v-btn>
      </p>
    </div>
  </v-fade-transition>
</template>

<style lang="scss">
.client-greeting {
  padding-bottom: 30px;
  p {
    padding: var(--offset);
  }
  ul {
    padding-left: 40px;
    padding-right: var(--offset);
  }
}
</style>
