<script setup lang="ts">
import type { TestQuestion } from '.'

const emit = defineEmits<{
  (e: 'saved', questions: TestQuestion[]): void
}>()

const questions = ref<TestQuestion[]>([])

const { dialog, width, transition } = useDialog('default')

function open(preSelect: TestQuestion[]) {
  dialog.value = true
  questions.value = [...preSelect]
}

function deleteQuestion(i: number) {
  questions.value?.splice(i, 1)
}

function save() {
  dialog.value = false
  emit('saved', questions.value)
}

function add() {
  questions.value.push({
    answers: [],
    score: null,
  })
  nextTick(() =>
    document
      .querySelector('.test-dialog__questions')
      ?.scrollTo({ top: 9999, behavior: 'smooth' }),
  )
}

function toggleAnswer(q: TestQuestion, n: number) {
  const index = q.answers.findIndex(e => e === n)
  index === -1
    ? q.answers.push(n)
    : q.answers.splice(index, 1)
}

defineExpose({ open })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
    :transition="transition"
  >
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <span>
          Редактировать вопросы
        </span>
        <v-btn
          icon="$save"
          :size="48"
          variant="text"
          @click="save()"
        />
      </div>
      <div class="dialog-body pt-0">
        <div class="test-dialog__questions">
          <div
            v-for="(q, i) in questions"
            :key="i"
          >
            <h2>
              Вопрос {{ i + 1 }}
              <v-btn
                icon="$close"
                variant="plain"
                color="red"
                :size="32"
                :ripple="false"
                @click="deleteQuestion(i)"
              />
            </h2>
            <div class="test-dialog__questions-answers">
              <v-btn
                v-for="n in 6"
                :key="n"
                height="48"
                width="48"
                variant="text"
                border
                :class="{
                  'bg-success': q.answers.some(e => e === n),
                }"
                icon
                @click="toggleAnswer(q, n)"
              >
                {{ n }}
              </v-btn>
              <v-spacer />
              <v-text-field
                v-model.number="q.score"
                label="балл"
                type="number"
                hide-spin-buttons
                density="comfortable"
                hide-details
              />
            </div>
          </div>
        </div>
        <v-btn
          size="x-large"
          color="primary"
          @click="add()"
        >
          добавить вопрос
        </v-btn>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.test-dialog {
  &__questions {
    flex: 1;
    // padding: 20px;
    overflow: scroll;
    &-answers {
      display: flex;
      gap: 8px;
    }
    &::-webkit-scrollbar {
      display: none;
    }
    h2 {
      margin-bottom: 16px;
      cursor: default;
      .v-icon {
        opacity: 0;
        font-size: 24px !important;
        top: -2px;
        position: relative;
        transition: opacity 250ms cubic-bezier(0.4, 0, 0.2, 1);
      }
      &:hover {
        .v-icon {
          opacity: 1;
        }
      }
    }
    .v-item-group {
      gap: 10px;
      display: flex;
      // justify-content: center;
    }
    .v-text-field {
      // margin-top: 30px;
      position: relative;
      // top: 4px;
      width: 48px;
      // height: 40px;
    }
    & > div {
      margin-bottom: 50px;
      &:first-child {
        margin-top: 20px;
      }
    }
  }
}
</style>
