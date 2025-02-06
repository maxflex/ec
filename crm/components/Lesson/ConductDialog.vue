<script setup lang="ts">
const emit = defineEmits(['conducted'])
const { dialog } = useDialog('large')
const itemId = ref<number>()
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
    return
  }
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
  emit('conducted')
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
    return
  }
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
  emit('conducted')
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
              <template v-if="s.status !== 'absent'">
                <div v-for="(score, i) in s.scores" :key="i">
                  <span :class="`cl-score cl-score--${score.score}`">
                    {{ score.score }}
                  </span>
                  – {{ score.comment || 'комментария нет' }}
                </div>
                <div v-if="s.comment">
                  {{ s.comment }}
                </div>
              </template>
            </div>
          </div>
        </div>
        <template v-if="isConducted">
          <v-btn v-if="isTeacher" color="primary" :loading="saving" width="300" @click="conduct()">
            провести занятие
          </v-btn>
        </template>
        <template v-else>
          <v-btn color="primary" :loading="saving" width="300" @click="save()">
            сохранить
          </v-btn>
        </template>
      </div>
    </div>
  </v-dialog>
  <LessonScoresDialog ref="scoresDialog" @save="onScoresSave" />
</template>

<style lang="scss">
.conduct-dialog {
  $height: 56px;
  .dialog-table {
    td {
      height: $height;
      vertical-align: top;
      &:first-child {
        padding-top: 16px;
      }
      a {
        cursor: pointer;
        display: inline-flex;
        //align-items: center;
        height: 100%;
        width: 100%;
        padding: 16px 16px;
        position: relative;
        &:hover {
          //background: rgb(var(--v-theme-bg));
          &:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(var(--v-theme-gray), 0.05);
            //background: rgba(black, 0.1);
          }
        }
      }
    }
  }
  .v-field__outline {
    display: none !important;
  }
}
.cl-score {
  font-weight: bold;

  &--4,
  &--5 {
    color: rgb(var(--v-theme-success));
  }

  &--3 {
    color: #dc8f03;
  }

  &--2,
  &--1 {
    color: rgb(var(--v-theme-error));
  }
}
</style>
