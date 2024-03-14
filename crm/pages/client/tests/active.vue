<script setup lang="ts">
import type { Test } from "~/utils/models"

const answers = ref<TestAnswersFront>([])
const cookie = useCookie<TestAnswersFront>("answers", {
  maxAge: 60 * 60 * 3,
}) // 3 hours
const test = ref<Test>()
const loading = ref(false)
const dialog = ref(false)

const answered = computed(
  () => answers.value.filter((e) => e !== undefined).length,
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
  dialog.value = true
}

watch(answers, saveAnswers)

onMounted(async () => {
  await loadData()
})
</script>

<template>
  <!-- <UiTopPanel>
    <v-btn> добавить тест </v-btn>
  </UiTopPanel> -->
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
          <!-- <v-radio-group>
          <v-radio label="письменная речь" value="one"></v-radio>
          <v-radio label="записная речь" value="two"></v-radio>
          <v-radio label="устная речь" value="three"></v-radio>
        </v-radio-group> -->
        </div>
      </div>
      <div class="test__controls">
        <v-btn color="primary" size="x-large" block @click="finish()">
          сдать ответы
          <span class="text-gray ml-2" style="width: 50px">
            {{ answered }}/{{ test.answers?.length }}
          </span>
        </v-btn>
      </div>
    </div>
  </div>
  <v-dialog
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
  </v-dialog>
</template>

<style lang="scss">
.test {
  height: 100vh;
  display: flex;
  overflow: hidden;
  iframe {
    width: 67%;
    border: 0;
    border-right: 1px solid #e0e0e0;
  }
  & > div {
    flex: 1;
    padding: 0 20px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
  }
  &__answers {
    padding-top: 20px;
    flex: 1;
    overflow: scroll;
    h2 {
      margin-bottom: 16px;
    }
    .v-item-group {
      gap: 10px;
      display: flex;
      // justify-content: center;
    }
    & > div {
      margin-bottom: 50px;
    }
  }
  &__controls {
    padding: 10px 0 20px;
  }
}
</style>
