<script setup lang="ts">
import { clone } from 'lodash-es'
import { modelDefaults, type TestQuestion, type TestResource } from '.'

const emit = defineEmits<{
  updated: []
}>()

const { dialog, width } = useDialog('default')
const item = ref<TestResource>({ ...modelDefaults })
const input = ref()
const loading = ref(false)
const deleting = ref(false)

function open(t: TestResource) {
  item.value = clone(t)
  dialog.value = true
}

function create() {
  item.value = clone({ ...modelDefaults })
  dialog.value = true
  nextTick(() => {
    input.value.reset()
    input.value.focus()
  })
}

async function save() {
  loading.value = true
  const { data } = item.value.id > 0
    ? await useHttp(`tests/${item.value.id}`, {
      method: 'PUT',
      body: item.value,
    })
    : await useHttp('tests', {
      method: 'POST',
      body: item.value,
    })
  item.value = data.value as TestResource
  dialog.value = false
  loading.value = false
  emit('updated')
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить тест?')) {
    return
  }
  deleting.value = true
  const { status } = await useHttp(`tests/${item.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    deleting.value = false
  }
  else {
    emit('updated')
    dialog.value = false
    setTimeout(() => (deleting.value = false), 300)
  }
}

function deleteQuestion(i: number) {
  item.value.questions.splice(i, 1)
}

function addQuestion() {
  item.value.questions.push({
    answers: [],
    score: null,
  })
  smoothScroll('dialog', 'bottom')
}

function toggleAnswer(q: TestQuestion, n: number) {
  const index = q.answers.findIndex(e => e === n)
  index === -1
    ? q.answers.push(n)
    : q.answers.splice(index, 1)
}

defineExpose({ open, create })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div
      v-if="item"
      class="dialog-wrapper"
    >
      <div class="dialog-header">
        <div v-if="item.id > 0">
          Редактирование теста
          <div class="dialog-subheader">
            {{ item.user ? formatName(item.user) : 'неизвестно' }}
            <template v-if="item.created_at">
              {{ formatDateTime(item.created_at) }}
            </template>
          </div>
        </div>
        <span v-else> Добавить тест </span>
        <div>
          <v-btn
            v-if="item.id > 0"
            :loading="deleting"
            :size="48"
            class="remove-btn"
            icon="$delete"
            variant="text"
            @click="destroy()"
          />
          <v-btn
            icon="$save"
            :size="48"
            :loading="loading"
            variant="text"
            @click="save()"
          />
        </div>
      </div>
      <div class="dialog-body">
        <div>
          <v-text-field
            ref="input"
            v-model="item.name"
            label="Название"
          />
        </div>
        <div>
          <v-text-field
            v-model="item.description"
            label="Техническое описание"
          />
        </div>
        <div>
          <v-text-field
            v-model="item.minutes"
            label="Минут на выполнение"
            type="number"
            hide-spin-buttons
          />
        </div>
        <div>
          <FileUploader v-model="item.file" folder="tests" />
        </div>
        <div>
          <div class="test-dialog__questions">
            <div
              v-for="(q, i) in item.questions"
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
            block
            @click="addQuestion()"
          >
            добавить вопрос
          </v-btn>
        </div>
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
