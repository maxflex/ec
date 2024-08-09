<script setup lang="ts">
const emit = defineEmits<{
  (e: 'saved', questions: TestQuestions): void
}>()

const questions = ref<TestQuestions>([])

const { dialog, width, transition } = useDialog('default')

function open(preSelect: TestQuestions) {
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
    answer: null,
    score: null,
  })
  nextTick(() =>
    document
      .querySelector('.test-dialog__questions')
      ?.scrollTo({ top: 9999, behavior: 'smooth' }),
  )
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
          Вопросы
          <span
            v-if="questions.length"
            class="ml-1 text-gray"
          >
            {{ questions.length }}
          </span>
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
            <v-item-group
              v-model="q.answer"
              selected-class="bg-success"
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
              <v-spacer />
              <v-text-field
                v-model.number="q.score"
                label="балл"
                type="number"
                hide-spin-buttons
                density="comfortable"
                hide-details
              />
            </v-item-group>
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
