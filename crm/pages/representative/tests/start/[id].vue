<script setup lang="ts">
definePageMeta({ middleware: ['check-active-test'] })

const route = useRoute()
const item = ref<ClientTestResource>()
const loading = ref(false)

async function loadData() {
  const { data } = await useHttp(`client-tests/${route.params.id}`)
  item.value = data.value as ClientTestResource
}

async function start() {
  loading.value = true
  await useHttp(`client-tests/start/${item.value?.id}`, {
    method: 'post',
  })
  useCookie('answers').value = null
  navigateTo({ name: 'tests-active' })
}

nextTick(loadData)
</script>

<template>
  <div v-if="item" class="test-start">
    <h1>
      {{ item.name }}
    </h1>
    <div class="test-start__info">
      Длительность: {{ item.minutes }} минут.
      {{ plural(item.question_counts.length, ["вопрос", "вопроса", "вопросов"]) }}.
      <br>
      Во время прохождения не закрывайте браузер!
    </div>
    <v-btn
      color="primary"
      size="x-large"
      width="300"
      :loading="loading"
      @click="start()"
    >
      начать тест
    </v-btn>
  </div>
</template>

<style lang="scss">
.test-start {
  width: 100%;
  text-align: center;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  &__info {
    font-size: 20px;
    // text-align: left;
    margin: 50px 0 100px;
    // & > div {
    //   display: flex;
    //   & > div {
    //     &:first-child {
    //       width: 300px;
    //     }
    //   }
    // }
  }
}
</style>
