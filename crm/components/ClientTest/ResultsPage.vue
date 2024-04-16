<script setup lang="ts">
import type { ClientTest } from "~/utils/models"

const route = useRoute()
const test = ref<ClientTest>()
const answers = ref<TestAnswers>()

async function loadData() {
  const { data } = await useHttp<ClientTest>(`tests/results/${route.params.id}`)
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
  <div class="test" v-if="test && test.is_finished">
    <iframe :src="test.file" />
    <div>
      <div class="test__questions">
        <div v-for="(q, i) in test.questions">
          <h2>
            Вопрос {{ i + 1 }}
            <span
              v-if="answers && answers[i] === q.answer"
              class="text-success"
            >
              +{{ plural(q.score as number, ["балл", "балла", "баллов"]) }}
            </span>
            <span class="text-error" v-else>
              -{{ plural(q.score as number, ["балл", "балла", "баллов"]) }}
            </span>
          </h2>
          <div class="v-item-group">
            <v-btn
              height="48"
              width="48"
              variant="text"
              icon
              border
              v-for="n in 6"
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
