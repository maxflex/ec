<script setup lang="ts">
import type { ClientTest } from "~/utils/models"
const { test } = defineProps<{
  test: ClientTest
}>()
const answers = ref<TestAnswers>()
if (test.answers) {
  answers.value = test.answers
}
</script>

<template>
  <span>
    {{
      test.questions
        ?.filter((e, i) => answers && e.answer === answers[i])
        .reduce((c, v) => c + (v.score as number), 0)
    }}
    из
    {{ test.questions?.reduce((c, v) => c + (v.score as number), 0) }}
  </span>
</template>
