<script setup lang="ts">
const emit = defineEmits(['conducted'])
const { dialog } = useDialog('large')
const itemId = ref(-1)
const item = ref<LessonConductResource>()
const { isAdmin, isTeacher } = useAuthStore()

const minutesLateMask = { mask: '###' }
const loading = ref(false)
const saving = ref(false)
const isConducted = ref(false)
const scoresDialog = ref()

function open(lessonId: number, status: LessonStatus) {
  itemId.value = lessonId
  isConducted.value = status === 'conducted'
  dialog.value = true
  loadData()
}

async function loadData() {
  loading.value = true
  const { data } = await useHttp<LessonConductResource>(
    `lessons/${itemId.value}?conduct=1`,
  )
  if (data.value) {
    console.log(data.value)
    item.value = data.value
  }
  loading.value = false
}

/**
 * Провести занятие
 */
async function conduct() {
  saving.value = true
  const { error } = await useHttp<LessonConductResource>(
    `lessons/conduct/${itemId.value}`,
    {
      method: 'post',
      body: item.value,
    },
  )
  if (error.value) {
    // errors.value = error.value.data.errors
    loading.value = false
  }
  onSaved()
}

/**
 * Сохранить ранее проведённое занятие
 */
async function save() {
  saving.value = true
  const { error } = await useHttp<LessonConductResource>(
    `lessons/${itemId.value}`,
    {
      method: 'put',
      body: item.value,
    },
  )
  if (error.value) {
    // errors.value = error.value.data.errors
    loading.value = false
  }
  onSaved()
}

function onSaved() {
  emit('conducted')
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
  useGlobalMessage(isConducted.value ? 'Изменения сохранены' : 'Занятие проведено', 'success')
}

function onScoresSave(cl: ClientLessonResource) {
  if (!item.value) {
    return
  }
  const index = item.value.students.findIndex(e => e.id === cl.id)
  item.value.students.splice(index, 1, cl)
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :transition="false" class="dialog-fullwidth">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <div v-if="isConducted">
          {{ isTeacher ? 'Редактирование' : 'Просмотр' }} проведённого занятия
          <div class="dialog-subheader">
            <template v-if="item?.conducted_at">
              проведено {{ formatDateTime(item.conducted_at) }}
            </template>
          </div>
        </div>
        <div v-else>
          Проводка занятия
        </div>
        <div>
          <v-btn
            icon="$close"
            :size="48"
            variant="text"
            @click="dialog = false"
          />
        </div>
      </div>
      <UiLoader v-if="loading" />
      <div v-else-if="item" class="dialog-body pt-0 conduct-dialog">
        <div v-if="item" class="table table--padding" :class="{ 'no-pointer-events': isAdmin }">
          <div v-for="s in item.students" :key="s.id">
            <div class="table-actionss">
              <v-btn
                v-if="s.status !== 'absent'"
                icon="$edit"
                :size="48"
                @click="scoresDialog.open(s)"
              />
            </div>
            <div style="width: 250px">
              {{ formatName(s.client) }}
            </div>
            <div style="width: 100px">
              <UiToggler
                v-model="s.status"
                :items="selectItems(ClientLessonStatusLabel)"
              />
            </div>
            <div style="width: 230px; position: relative">
              <v-text-field
                v-if="['late', 'lateOnline'].includes(s.status)"
                v-model="s.minutes_late"
                v-maska:[minutesLateMask]
                style="position: absolute; width: 126px; top: -20px"
                type="number"
                hide-spin-buttons
                prefix="на"
                suffix="минут"
                density="compact"
                persistent-placeholder
                placeholder="__"
              />
            </div>
            <div class="conduct-dialog__scores">
              <div v-if="s.comment">
                {{ s.comment }}
              </div>
              <template v-if="s.status !== 'absent'">
                <div v-for="(score, i) in s.scores" :key="i">
                  <span :class="`text-score text-score--small text-score--${score.score}`">
                    {{ score.score }}
                  </span>
                  – {{ score.comment || 'комментария нет' }}
                </div>
              </template>
            </div>
          </div>
        </div>
        <template v-if="isTeacher">
          <div v-if="!item.topic">
            <v-alert type="error" variant="outlined">
              Тема урока не указана. Чтобы провести занятие, необходимо добавить тему урока.
            </v-alert>
          </div>

          <v-btn v-if="isConducted" color="primary" :loading="saving" width="300" @click="save()">
            сохранить
          </v-btn>
          <v-btn v-else color="primary" :loading="saving" width="300" :disabled="!item.topic" @click="conduct()">
            провести занятие
          </v-btn>
        </template>
      </div>
    </div>
  </v-dialog>
  <LessonScoresDialog ref="scoresDialog" @save="onScoresSave" />
</template>

<style lang="scss">
.conduct-dialog {
  .v-field__outline {
    display: none !important;
  }

  &__scores {
    & > div:nth-child(2) {
      margin-top: 8px;
    }
  }
  .table-actionss {
    display: flex;
    align-items: center;
    justify-content: flex-end;
  }
}
</style>
