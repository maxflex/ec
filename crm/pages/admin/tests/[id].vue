<script setup lang="ts">
import type { ClientTestResource } from '~/components/ClientTest'
import { mdiMessageAlertOutline } from '@mdi/js'

const route = useRoute()
const test = ref<ClientTestResource>()

async function loadData() {
  const { data } = await useHttp<ClientTestResource>(
    `client-tests/${route.params.id}`,
  )
  if (data.value) {
    test.value = data.value
  }
}

nextTick(loadData)
</script>

<template>
  <div v-if="test" class="test">
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
              {{ plural(test.questions[i - 1].score!, ['балл', 'балла', 'баллов']) }}
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
                'bg-success': test.questions[i - 1].answers.includes(n),
              }"
            >
              {{ n }}
            </v-btn>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
