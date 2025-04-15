<script setup lang="ts">
import type { ClientTestResource } from '.'
import type { TestAnswers } from '../Test'
import { mdiMessageAlertOutline } from '@mdi/js'

const route = useRoute()
const test = ref<ClientTestResource>()
const answers = ref<TestAnswers>({})

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
    <iframe :src="test.file.url" />
    <div>
      <div class="test__questions">
        <div
          v-for="i in test.question_counts.length"
          :key="i"
        >
          <h2>
            Вопрос {{ i }}
            <span class="text-gray">
              {{ test.results!.answers[i].score }} / {{ test.results!.answers[i].total }} балла
            </span>
          </h2>
          <div v-if="test.question_counts[i - 1] > 1" class="test__answers-count">
            <v-icon :icon="mdiMessageAlertOutline" color="deepOrange" />
            укажите {{ test.question_counts[i - 1] }} ответа
          </div>
          <div class="test__questions-answers">
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
                'test-answer--correct': test.questions[i - 1].answers.includes(n),
                'test-answer--my': i in test.answers && test.answers[i].includes(n),
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
          {{ test.results!.score }} / {{ test.results!.total }}
        </span>
        балла
      </div>
    </div>
  </div>
</template>
