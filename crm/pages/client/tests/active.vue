<script setup lang="ts">
import { mdiMessageAlertOutline } from '@mdi/js'
import { clone } from 'rambda'
import type { ClientTestResource } from '~/components/ClientTest'
import type { TestAnswers } from '~/components/Test'

const isTimeout = ref(false)
const answers = ref<TestAnswers>({})
const cookie = useCookie<TestAnswers>('answers', { maxAge: 60 * 60 * 3 }) // 3 hours
const test = ref<ClientTestResource>()
const finishing = ref(false)

function saveAnswers() {
  cookie.value = answers.value
}

async function loadData() {
  const { data, error } = await useHttp<ClientTestResource>(`client-tests/active`)
  // нет активного теста
  if (error.value) {
    return navigateTo({ name: 'tests' })
  }
  if (data.value) {
    test.value = data.value
    if (cookie.value) {
      answers.value = clone(cookie.value)
    }
  }
}

async function finish() {
  finishing.value = true
  await useHttp(
    `client-tests/finish`,
    {
      method: 'post',
      body: {
        answers: answers.value,
      },
    },
  )
  navigateTo({
    name: 'tests-result-id',
    params: {
      id: test.value?.id,
    },
  })
}

function toggleAnswer(i: number, answer: number) {
  // максимальное кол-во ответов
  const maxAnswers = test.value!.question_counts[i - 1]
  console.log({ maxAnswers })

  if (maxAnswers === 1) {
    if (i in answers.value && answers.value[i][0] === answer) {
      delete answers.value[i]
    }
    else {
      answers.value[i] = [answer]
    }
    return
  }

  if (i in answers.value) {
    const index = answers.value[i].findIndex(a => a === answer)
    if (index === -1) {
      if (answers.value[i].length >= maxAnswers) {
        return
      }
      answers.value[i].push(answer)
    }
    else {
      answers.value[i].splice(index, 1)
    }
  }
  else {
    answers.value[i] = [answer]
  }
}

watch(answers, saveAnswers, { deep: true })

nextTick(loadData)
</script>

<template>
  <div
    v-if="test"
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
              :class="{ 'bg-primary': (i in answers) && answers[i].some(e => e === n) }"
              @click="toggleAnswer(i, n)"
            >
              {{ n }}
            </v-btn>
          </div>
        </div>
      </div>
      <div class="test__controls">
        <v-btn
          v-if="isTimeout"
          size="x-large"
          block
          disabled
        >
          время истекло
        </v-btn>
        <v-btn
          v-else
          color="primary"
          size="x-large"
          block
          :loading="finishing"
          @click="finish()"
        >
          отправить ответы
          <ClientTestCountDown class="test__timer-in-btn" :item="test" @timeout="isTimeout = true" />
        </v-btn>
      </div>
    </div>
  </div>
</template>
