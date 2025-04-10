<script setup lang="ts">
import { clone } from 'rambda'
import type { ActiveTest, TestAnswers } from '~/components/Test'

const answers = ref<TestAnswers>({})
const cookie = useCookie<TestAnswers>('answers', { maxAge: 60 * 60 * 3 }) // 3 hours
const test = ref<ClientTestResource>()
const finishing = ref(false)
const seconds = ref(0)
let interval: NodeJS.Timeout

// const answered = computed(
//   () => answers.value.filter((e) => e !== undefined && e !== null).length,
// )

function saveAnswers() {
  console.log('save answers', cookie.value, answers.value)
  cookie.value = answers.value
}

async function loadData() {
  const { data, error } = await useHttp<ActiveTest>(`client-tests/active`)
  // нет активного теста
  if (error.value) {
    return navigateTo({ name: 'tests' })
  }
  if (data.value) {
    test.value = data.value.test
    seconds.value = data.value.seconds
    if (seconds.value > 0) {
      interval = setInterval(() => {
        seconds.value--
        if (seconds.value <= 0) {
          clearInterval(interval)
        }
      }, 1000)
    }
    if (cookie.value) {
      answers.value = clone(cookie.value)
    }
  }
}

async function finish() {
  finishing.value = true
  await useHttp(`client-tests/finish`, {
    method: 'post',
    body: {
      answers: answers.value,
    },
  })
  navigateTo({
    name: 'tests-result-id',
    params: {
      id: test.value?.id,
    },
  })
}

function toggleAnswer(i: number, n: number) {
  if (i in answers.value) {
    const index = answers.value[i].findIndex(e => e === n)
    if (index === -1) {
      // максимальное кол-во ответов
      if (answers.value[i].length >= 3) {
        return
      }
      answers.value[i].push(n)
    }
    else {
      answers.value[i].splice(index, 1)
    }
  }
  else {
    answers.value[i] = [n]
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
          v-for="i in test.questions_count"
          :key="i"
        >
          <h2>Вопрос {{ i }}</h2>
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
        <!-- <div class="test__timer">
          <v-icon :icon="mdiClockOutline" />
          {{ $dayjs.duration(seconds, "second").format("mm:ss") }}
        </div> -->
        <v-btn
          v-if="seconds > 0"
          color="primary"
          size="x-large"
          block
          :loading="finishing"
          @click="finish()"
        >
          отправить ответы
          <!-- <span class="ml-2" style="width: 50px; opacity: 0.5">
            {{ answered }}/{{ test.answers?.length }}
          </span> -->
          <ClientTestCountDown class="test__timer-in-btn" :item="test" />
        </v-btn>
        <v-btn
          v-else
          size="x-large"
          block
          disabled
        >
          время истекло
        </v-btn>
      </div>
    </div>
  </div>
</template>
