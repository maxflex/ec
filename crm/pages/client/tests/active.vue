<script setup lang="ts">
import { mdiClockOutline } from '@mdi/js'

const { $dayjs } = useNuxtApp()
const answers = ref<TestAnswers>([])
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
    answers.value = Array.from({ length: data.value.test.questions_count })
    if (cookie.value) {
      answers.value = cookie.value.slice()
    }
  }
}

async function finish() {
  finishing.value = true
  await useHttp(`tests/finish`, {
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

watch(answers, saveAnswers)
nextTick(loadData)
</script>

<template>
  <div
    v-if="test"
    class="test"
  >
    <iframe :src="test.file" />
    <div>
      <div class="test__questions">
        <div
          v-for="i in test.questions_count"
          :key="i"
        >
          <h2>Вопрос {{ i }}</h2>
          <v-item-group
            v-model="answers[i - 1]"
            selected-class="bg-primary"
          >
            <v-item
              v-for="n in 6"
              :key="n"
            >
              <template #default="{ toggle, selectedClass }">
                <v-btn
                  height="48"
                  width="48"
                  variant="text"
                  icon
                  border
                  :class="selectedClass"
                  @click="toggle"
                >
                  {{ n }}
                </v-btn>
              </template>
            </v-item>
          </v-item-group>
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
          <span class="test__timer-in-btn">
            <v-icon :icon="mdiClockOutline" />
            {{ $dayjs.duration(seconds, "second").format("mm:ss") }}
          </span>
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
