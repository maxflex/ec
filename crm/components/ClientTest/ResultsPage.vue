<script setup lang="ts">
const route = useRoute()
const test = ref<ClientTestResource>()
const answers = ref<TestAnswers>()

async function loadData() {
  const { data } = await useHttp<ClientTestResource>(
    `client-tests/${route.params.id}`,
  )
  if (data.value) {
    test.value = data.value
    if (test.value.answers) {
      answers.value = test.value.answers
    }
  }
}

nextTick(loadData)
</script>

<template>
  <div
    v-if="test && test.is_finished"
    class="test"
  >
    <iframe :src="test.file" />
    <div>
      <div class="test__questions">
        <div
          v-for="(q, i) in test.questions"
          :key="i"
        >
          <h2>
            Вопрос {{ i + 1 }}
            <span
              v-if="answers && answers[i] === q.answer"
              class="text-success"
            >
              +{{ plural(q.score as number, ["балл", "балла", "баллов"]) }}
            </span>
            <span
              v-else
              class="text-error"
            >
              -{{ plural(q.score as number, ["балл", "балла", "баллов"]) }}
            </span>
          </h2>
          <div class="v-item-group">
            <v-btn
              v-for="n in 6"
              :key="n"
              height="48"
              width="48"
              variant="text"
              icon
              border
              class="no-pointer-events"
              :class="{
                'bg-success': n - 1 === q.answer,
                'bg-error': answers && n - 1 === answers[i],
              }"
            >
              {{ n }}
            </v-btn>
          </div>
        </div>
      </div>
      <div class="test__results">
        Набрано
        <span class="font-weight-bold">
          {{ formatClientTestResults(test) }}
        </span>
        баллов
      </div>
    </div>
  </div>
</template>
