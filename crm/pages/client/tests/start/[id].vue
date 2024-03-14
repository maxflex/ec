<script setup lang="ts">
import type { Test } from "~/utils/models"

const route = useRoute()
const test = ref<Test>()
const loading = ref(false)

onMounted(async () => {
  await loadData()
})

async function loadData() {
  const { data } = await useHttp(`client/tests/${route.params.id}`)
  test.value = data.value as Test
}

async function start() {
  loading.value = true
  await useHttp(`client/tests/start/${route.params.id}`, {
    method: "post",
  })
  navigateTo({ name: "client-tests-active" })
}
</script>

<template>
  <div class="test-start" v-if="test">
    <h1>
      {{ test.name }}
    </h1>
    <div class="test-start__info">
      Длительность: {{ test.minutes }} минут.
      <template v-if="test.answers?.length">
        {{ plural(test.answers.length, ["вопрос", "вопроса", "вопросов"]) }}.
      </template>
      <br />
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
