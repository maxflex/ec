<script setup lang="ts">
const emit = defineEmits(['conducted'])
const { width, dialog } = useDialog('large')
const itemId = ref<number>()
const item = ref<LessonConductResource>()

const loading = ref(false)
const saving = ref(false)
const isConducted = ref(false)

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
            <div style="width: 110px">
              <UiDropdown
                v-model="c.status"
                :items="selectItems(ContractLessonStatusLabel)"
              />
            </div>
            <div v-if="c.status !== 'absent'" style="width: 110px">
              <UiDropdown
                v-model="c.is_remote"
                :items="[
                  { value: false, title: 'очно' },
                  { value: true, title: 'удалённо' },
                ]"
              />
            </div>
            <div v-if="c.status !== 'absent'">
              <div v-for="(score, index) in c.scores" :key="index" class="contract-lesson__scores">
                <span :class="`score score--${score.score}`">
                  {{ score.score }}
                </span>
                <v-text-field
                  v-model="c.scores[index].comment"
                  density="compact"
                  placeholder="комментарий к оценке"
                  persistent-placeholder
                />
              </div>
              <v-menu>
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
                    v-for="score in [5, 4, 3, 2] as LessonScore[]"
                    :key="score"
                    @click="c.scores.push({ score, comment: null })"
                  >
                    <span :class="`score score--${score}`">
                      {{ score }}
                    </span>
                    {{ LessonScoreLabel[score] }}
                  </v-list-item>
                </v-list>
              </v-menu>
            </div>

            <!-- <div v-if="c.status === 'late'" style="width: 190px">
                <v-text-field
                  v-model="c.minutes_late"
                  type="number"
                  hide-spin-buttons
                  density="compact"
                  prefix="опоздание"
                  suffix="минут"
                  persistent-placeholder
                />
              </div> -->
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
  &--absent {
    background: rgba(var(--v-theme-error), 0.1);
  }
  // &--late {
  //   background: rgba(var(--v-theme-warning), 0.1);
  // }
  &__scores {
    display: flex;
    align-items: center;
  }
}
</style>
