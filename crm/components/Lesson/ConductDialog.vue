<script setup lang="ts">
const emit = defineEmits(['conducted'])
const { width, dialog } = useDialog('large')
const itemId = ref<number>()
const item = ref<LessonConductResource>()

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
    `lessons/${itemId.value}`,
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
            Редактировать занятие
          </div>
          <div>
            <v-btn
              icon="$save"
              :size="48"
              color="#fafafa"
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
            <v-btn color="primary" :loading="saving" @click="conduct()">
              провести занятие
            </v-btn>
          </div>
        </template>
      </div>
      <UiLoaderr v-if="loading" />
      <div v-else-if="item" class="dialog-body">
        <div>
          <v-alert
            v-if="isConducted"
            type="success"
            variant="tonal"
            title="Это занятие уже проведено"
          >
            При редактировании данных не
            отправляются СМС, комментарии, не производится начисление оплаты
          </v-alert>
          <v-alert
            v-else
            title="В момент проводки занятия автоматически происходит:"
            type="info"
            variant="tonal"
          >
            <div class="pt-2">
              – отправка СМС об опоздании или отсутствии ученика родителям
            </div>
            <div>
              – отправка СМС с вашими комментариями родителям
            </div>
            <div>
              – начисление оплаты и бонусов за проведенное занятие
            </div>
          </v-alert>
        </div>
        <div class="table">
          <div
            v-for="c in item?.contracts" :key="c.id"
            :class="`contract-lesson contract-lesson--${c.status}`"
          >
            <div>
              <UiAvatar :item="c.client" :size="46" />
            </div>
            <div style="width: 220px">
              {{ formatName(c.client) }}
            </div>
            <div style="width: 120px">
              <UiDropdown
                v-model="c.status"
                :items="selectItems(ContractLessonStatusLabel)"
              />
              <div v-if="c.status === 'late'" style="width: 100px">
                <v-text-field
                  v-model="c.minutes_late"
                  class="mt-2"
                  type="number"
                  hide-spin-buttons
                  density="compact"
                  suffix="минут"
                  persistent-placeholder
                />
              </div>
            </div>
            <div v-if="c.status !== 'absent'" style="width: 120px">
              <UiDropdown
                v-model="c.is_remote"
                :items="[
                  { value: false, title: 'очно' },
                  { value: true, title: 'удалённо' },
                ]"
              />
            </div>
            <div v-if="c.status !== 'absent'">
              <div v-if="c.scores.length > 0" class="contract-lesson__scores">
                <div v-for="(score, index) in c.scores" :key="index">
                  <span :class="`score score--${score.score}`">
                    {{ score.score }}
                  </span>
                  <v-text-field
                    v-model="c.scores[index].comment"
                    density="compact"
                    placeholder="комментарий к оценке"
                    persistent-placeholder
                  />
                  <v-icon
                    icon="$close"
                    @click="c.scores.splice(index, 1)"
                  />
                </div>
              </div>
              <v-menu v-if="c.scores.length < 4">
                <template #activator="{ props }">
                  <a
                    v-bind="props"
                    class="ui-dropdown"
                  >
                    добавить оценку
                    <v-icon icon="$expand" />
                  </a>
                </template>
                <v-list>
                  <v-list-item
                    v-for="score in scores"
                    :key="score"
                    @click="c.scores.push({ score, comment: null })"
                  >
                    <span :class="`score score--${score}`" class="mr-3">
                      {{ score }}
                    </span>
                    {{ LessonScoreLabel[score] }}
                  </v-list-item>
                </v-list>
              </v-menu>
            </div>

            <!--  -->
          </div>
        </div>
        <div
          v-if="isConducted"
          class="dialog-bottom mt-4"
        >
          <span v-if="item.conducted_at">
            занятие проведено
            {{ formatDateTime(item.conducted_at) }}
          </span>
        </div>
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
  &--absent {
    background: rgba(var(--v-theme-error), 0.1);
  }
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
</style>
