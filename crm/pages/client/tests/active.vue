<script setup lang="ts">
import type { Test } from "~/utils/models"

const answers = ref<TestAnswersFront>([])
const cookie = useCookie<TestAnswersFront>("answers", {
  maxAge: 60 * 60 * 3,
}) // 3 hours
const test = ref<Test>()
const finishing = ref(false)

const answered = computed(
  () => answers.value.filter((e) => e !== undefined && e !== null).length,
)

function saveAnswers() {
  console.log("save answers", cookie.value, answers.value)
  cookie.value = answers.value
}

async function loadData() {
  const { data } = await useHttp(`client/tests/active`)
  test.value = data.value as Test
  if (cookie.value) {
    answers.value = cookie.value.slice()
  }
}

async function finish() {
  finishing.value = true
  await useHttp(`client/tests/finish`, {
    method: "post",
    body: {
      answers: answers.value,
    },
  })
  navigateTo({
    name: "client-tests-result-id",
    params: {
      id: test.value?.id,
    },
  })
}

watch(answers, saveAnswers)

onMounted(async () => {
  await loadData()
})
</script>

<template>
  <div class="test" v-if="test">
    <iframe :src="test.file" />
    <div>
      <div class="test__answers">
        <div v-for="(a, i) in test.answers">
          <h2>Вопрос {{ i + 1 }}</h2>
          <v-item-group selected-class="bg-primary" v-model="answers[i]">
            <v-item v-for="n in 6" :key="n">
              <template v-slot:default="{ toggle, selectedClass }">
                <v-btn
                  height="48"
                  width="48"
                  variant="text"
                  icon
                  border
                  @click="toggle"
                  :class="selectedClass"
                >
                  {{ n }}
                </v-btn>
              </template>
            </v-item>
          </v-item-group>
        </div>
      </div>
      <div class="test__controls">
        <v-btn
          color="primary"
          size="x-large"
          block
          @click="finish()"
          :loading="finishing"
        >
          отправить ответы
          <span class="ml-2" style="width: 50px; opacity: 0.5">
            {{ answered }}/{{ test.answers?.length }}
          </span>
        </v-btn>
      </div>
    </div>
  </div>
  <!-- <v-dialog
    v-model="dialog"
    :fullscreen="false"
    transition="v-dialog-transition"
    width="auto"
    origin="center center"
  >
    <v-card
      max-width="400"
      prepend-icon="mdi-update"
      text="Your application will relaunch automatically after the update is complete."
      title="Update in progress"
    >
      <template v-slot:actions>
        <v-btn class="ms-auto" text="Ok" @click="dialog = false"></v-btn>
      </template>
    </v-card>
  </v-dialog> -->
</template>
