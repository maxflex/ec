<script setup lang="ts">
const emit = defineEmits(['conducted'])
const { width, dialog } = useDialog('large')
const itemId = ref<number>()
const item = ref<LessonConductResource>()
const { isTeacher } = useAuthStore()

const minutesLateMask = { mask: '###' }
const loading = ref(false)
const saving = ref(false)
const isConducted = ref(false)
const scores = [5, 4, 3, 2] as LessonScore[]

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

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <template v-if="isConducted">
          <div>
            Редактирование проведённого занятия
            <div class="dialog-subheader">
              <template v-if="item?.conducted_at">
                проведено {{ formatDateTime(item.conducted_at) }}
              </template>
            </div>
          </div>
          <div>
            <v-btn
              icon="$save"
              :size="48"
              variant="text"
              :loading="saving"
              @click="save()"
            />
          </div>
        </template>
        <template v-else>
          <div>
            Проводка занятия
          </div>
          <div>
            <v-btn v-if="isTeacher" color="primary" :loading="saving" @click="conduct()">
              провести занятие
            </v-btn>
          </div>
        </template>
      </div>
      <UiLoader v-if="loading" />
      <div v-else-if="item" class="dialog-body pt-0 conduct-dialog">
        <table class="dialog-table">
          <tbody>
            <tr v-for="s in item?.students" :key="s.id">
              <td width="290">
                {{ formatName(s.client) }}
              </td>
              <td width="100">
                <UiToggler
                  v-model="s.status"
                  :items="selectItems(ClientLessonStatusLabel)"
                />
              </td>
              <td width="80">
                <v-text-field
                  v-if="['late', 'lateOnline'].includes(s.status)"
                  v-model="s.minutes_late"
                  v-maska:[minutesLateMask]
                  type="number"
                  hide-spin-buttons
                  placeholder="минут"
                />
              </td>
              <td width="57" class="conduct-dialog__scores">
                <div
                  v-for="(score, index) in s.scores" :key="index"
                  :class="`conduct-dialog__score conduct-dialog__score--${score.score}`"
                >
                  <UiToggler
                    v-model="s.scores[index].score"
                    :items="scores.map(e => ({ value: e, title: e.toString() }))"
                  />
                </div>
              </td>
              <td class="conduct-dialog__score-comments">
                <div v-for="(score, index) in s.scores" :key="index">
                  <v-text-field
                    v-model="s.scores[index].comment"
                    density="compact"
                    placeholder="комментарий"
                  />
                  <v-icon
                    icon="$close"
                    @click="s.scores.splice(index, 1)"
                  />
                </div>
              </td>
              <td width="20">
                <a v-if="s.scores.length < 3" @click="s.scores.push({ score: 5, comment: null })">
                  <v-icon icon="$plus" />
                </a>
              </td>
            </tr>
            <tr>
              <td colspan="100" />
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.contract-lesson {
  // transition: background cubic-bezier(0.4, 0, 0.2, 1) 0.3s;
  align-items: flex-start !important;
  & > div {
    &:first-child {
      padding-top: 4px;
    }
    &:not(:first-child) {
      padding: 16px 0;
    }
  }
  // &--absent {
  //   background: rgba(var(--v-theme-error), 0.1);
  // }
  // &--late {
  //   background: rgba(var(--v-theme-warning), 0.1);
  // }
  &__scores {
    position: relative;
    top: -6px;
    margin-bottom: 6px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    & > div {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    input {
      padding-left: 10px !important;
      padding-right: 10px !important;
    }
    .v-icon {
      opacity: 0.25;
      cursor: pointer;
      font-size: 20px !important;
      &:hover {
        opacity: 1;
        color: rgb(var(--v-theme-error));
      }
    }
  }
}
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
      .v-field__input {
        height: 55px;
      }
    }
  }

  &__scores,
  &__score-comments {
    & > div {
      height: $height;
      &:not(:last-child) {
        border-bottom: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
      }
    }
  }
  &__score-comments {
    & > div {
      position: relative;
    }
    .v-icon {
      position: absolute;
      right: 12px;
      top: 19px;
      opacity: 0.25;
      z-index: 1;
      // transition: all ease-in-out 0.1s;
      cursor: pointer;
      font-size: 20px !important;
      &:hover {
        opacity: 1;
        color: rgb(var(--v-theme-error));
      }
    }
    input {
      padding-right: 30px !important;
    }
  }
  &__score {
    a {
      color: black !important;
      &:hover:before {
        background: rgba(white, 0.1) !important;
      }
    }
    &--5 {
      a {
        background: rgb(var(--v-theme-success), 0.5);
      }
    }
    &--4 {
      a {
        background: rgb(var(--v-theme-success), 0.2);
      }
    }
    &--3 {
      a {
        background: rgb(var(--v-theme-orange), 0.4);
      }
    }
    &--2 {
      a {
        background: rgb(var(--v-theme-error), 0.4);
      }
    }
  }
}
</style>
