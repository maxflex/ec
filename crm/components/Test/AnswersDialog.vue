<script setup lang="ts">
const emit = defineEmits<{
  (e: "saved", answers: TestAnswers): void
}>()

const answers = ref<TestAnswers>([])

const { dialog, width } = useDialog(500)

function open(preSelect: TestAnswers) {
  dialog.value = true
  answers.value = [...preSelect]
}

function deleteAnswer(i: number) {
  answers.value?.splice(i, 1)
}

function save() {
  dialog.value = false
  emit("saved", answers.value)
}

function add() {
  answers.value.push({
    correct: null,
    score: null,
  })
  nextTick(() =>
    document
      .querySelector(".test-dialog__answers")
      ?.scrollTo({ top: 9999, behavior: "smooth" }),
  )
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-content">
      <div class="dialog-header">
        <span>
          Ответы
          <span class="ml-1 text-gray" v-if="answers.length">
            {{ answers.length }}
          </span>
        </span>
        <v-btn icon="$save" :size="48" @click="save()" color="#fafafa" />
      </div>
      <div class="dialog-body pt-0">
        <div class="test-dialog__answers">
          <div v-for="(a, i) in answers">
            <h2>
              Вопрос {{ i + 1 }}
              <v-btn
                icon="$close"
                variant="plain"
                color="red"
                :size="32"
                :ripple="false"
                @click="deleteAnswer(i)"
              >
              </v-btn>
            </h2>
            <v-item-group selected-class="bg-success" v-model="a.correct">
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
              <v-spacer />
              <v-text-field
                v-model="a.score"
                label="балл"
                type="number"
                hide-spin-buttons
                density="comfortable"
                hide-details
              />
            </v-item-group>
          </div>
        </div>
        <v-btn @click="add()" size="x-large" color="primary">
          добавить ответ
        </v-btn>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.test-dialog {
  &__answers {
    &::-webkit-scrollbar {
      display: none;
    }
    flex: 1;
    // padding: 20px;
    overflow: scroll;
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
