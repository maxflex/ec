<script setup lang="ts">
const router = useRouter()
const { user, isPreviewMode } = useAuthStore()
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
      <h1>
        {{ user!.first_name }}, здравствуйте!
      </h1>
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
      <v-btn color="primary" width="300" class="mt-10" size="x-large" @click="ok()">
        понятно
      </v-btn>
    </div>
  </v-fade-transition>
</template>

<style lang="scss">
.client-greeting {
  // display: flex;
  // justify-content: center;
  // flex-direction: column;
  // padding-left: 25vw;
  // margin-top: -100px;
  padding: 40px;
  // font-size: 18px;

  ul {
    padding-left: 10px;
  }
  li {
    padding-left: 10px;
    &::marker {
      content: '–';
    }
  }
  p {
    margin: 20px 0;
  }
}
</style>
