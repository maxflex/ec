<script setup lang="ts">
import type { Test } from "~/utils/models"

const route = useRoute()
const answers = ref<TestAnswersFront>([])
const test = ref<Test>()

const answered = computed(
  () => answers.value.filter((e) => e !== undefined && e !== null).length,
)

async function loadData() {
  const { data } = await useHttp(`client/tests/result/${route.params.id}`)
  console.log(data.value)
  // @ts-expect-error
  test.value = data.value.test as Test
  // @ts-expect-error
  answers.value = data.value.answers as TestAnswersFront
}

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
          <h2>
            Вопрос {{ i + 1 }}
            <span v-if="answers[i] === a.correct" class="text-success">
              +{{ plural(a.score as number, ["балл", "балла", "баллов"]) }}
            </span>
            <span class="text-error" v-else>
              -{{ plural(a.score as number, ["балл", "балла", "баллов"]) }}
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
                'bg-success': n - 1 === a.correct,
                'bg-error': n - 1 === answers[i],
              }"
            >
              {{ n }}
            </v-btn>
          </div>
        </div>
      </div>
      <div class="test__results">
        Набрано
        <b>
          {{
            test.answers
              ?.filter((e, i) => e.correct === answers[i])
              .reduce((c, v) => c + (v.score as number), 0)
          }}
          из
          {{ test.answers?.reduce((c, v) => c + (v.score as number), 0) }}
        </b>
        баллов
      </div>
    </div>
  </div>
</template>
