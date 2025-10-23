<script setup lang="ts">
import type { RealReport, ReportResource, ReportTextFields } from '.'
import { mdiAutoFix } from '@mdi/js'
import { cloneDeep } from 'lodash-es'

const emit = defineEmits<{
  updated: [r: ReportResource]
}>()
const { dialog, width } = useDialog('medium')
const item = ref<ReportResource>()
const deleting = ref(false)
const saving = ref(false)
const aiLoading = ref(false)
const router = useRouter()
const { isAdmin, isTeacher } = useAuthStore()
const availableTeacherStatuses: ReportStatus[] = [
  'draft',
  'toCheck',
  'empty',
]

const availableAdminStatuses: ReportStatus[] = [
  'refused',
  'published',
  'empty',
]

const availableStatuses = isTeacher ? availableTeacherStatuses : availableAdminStatuses

const isDisabled = computed(() => {
// Если статус = черновик или на проверку, или пустой отчет,
// то препод может редактировать все. Если остальные типы, то отчет нельзя редактировать
  if (isTeacher) {
    return !availableTeacherStatuses.includes(item.value!.status) && item.value!.status !== 'refused'
  }
  // админ не может редактировать статус "черновик"
  return item.value!.status === 'draft'
})

function open(report: ReportResource) {
  item.value = cloneDeep(report)
  dialog.value = true
}

async function save() {
  saving.value = true
  if (item.value!.id > 0) {
    const { data } = await useHttp<ReportResource>(
      `reports/${item.value!.id}`,
      {
        method: 'put',
        body: { ...item.value },
      },
    )
    saving.value = false
    dialog.value = false
    emit('updated', data.value!)
  }
  else {
    const { data } = await useHttp<RealReport>(
      `reports`,
      {
        method: 'post',
        body: {
          ...item.value,
          client_id: item.value!.client?.id,
        },
      },
    )
    await router.push({ name: 'reports-id-edit', params: { id: data.value?.id } })
  }
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить отчёт?')) {
    return
  }
  deleting.value = true
  const { status } = await useHttp(`reports/${item.value!.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    deleting.value = false
  }
  else {
    await router.push({ name: 'reports' })
  }
}

const fill = computed<number>(() => {
  if (!item.value) {
    return 0
  }

  const max = 1000 // сколько символов = 100% заполняемость
  let total = 0
  for (const comment of [
    item.value.homework_comment,
    item.value.recommendation_comment,
    item.value.cognitive_ability_comment,
    item.value.knowledge_level_comment,
  ]) {
    total += (comment ? comment.length : 0)
  }

  return Math.min(Math.round(total * 100 / max), 100)
})

async function improve() {
  if (!item.value) {
    return
  }
  aiLoading.value = true
  const { data, error } = await useHttp<ReportTextFields>(`reports/improve/${item.value.id}`)
  if (error.value) {
    setTimeout(() => useGlobalMessage(`<b>Ошибка ИИ</b>: ${error.value!.data.message}`, 'error'), 100)
  }
  if (data.value) {
    item.value.ai_text = data.value!
  }
  aiLoading.value = false
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width" class="report-dialog">
    <div v-if="item" class="dialog-wrapper">
      <div class="dialog-header">
        <div v-if="item.id === -1">
          Новый отчёт
        </div>
        <div v-else>
          Редактирование отчета
          <div class="dialog-subheader">
            {{ formatDateTime(item.created_at!) }}
          </div>
        </div>
        <ReportFill :model-value="fill" />
        <div>
          <v-btn
            v-if="item.id > 0 && !isDisabled"
            :loading="deleting"
            :size="48"
            class="remove-btn"
            icon="$delete"
            variant="text"
            @click="destroy()"
          />
          <v-btn
            v-if="isAdmin"
            :icon="mdiAutoFix"
            :size="48"
            :loading="aiLoading"
            variant="text"
            :disabled="isDisabled"
            @click="improve()"
          />
          <v-btn
            icon="$save"
            :size="48"
            :loading="saving"
            :disabled="isDisabled"
            variant="text"
            @click="save()"
          />
        </div>
      </div>
      <div class="dialog-body">
        <div class="double-input">
          <v-select
            v-model="item.status"
            label="Статус"
            :items="availableStatuses.map(value => ({
              value,
              title: ReportStatusLabel[value],
            }))"
            :disabled="isDisabled"
          >
            <template v-if="!(item.status in availableStatuses)" #selection>
              {{ ReportStatusLabel[item.status] }}
            </template>
          </v-select>
          <v-select
            v-model="item.grade"
            label="Оценка"
            :items="selectItems(LessonScoreLabel)"
            :disabled="isDisabled"
          >
            <template #selection="{ item: { raw: { value } } }">
              <span :class="`text-score text-score--${value}`" style="position: absolute;">
                {{ value }}
              </span>
            </template>
            <template #item="{ props, item: { raw: { value } } }">
              <v-list-item v-bind="props">
                <template #title>
                  <span :class="`text-score text-score--${value}`" class="mr-2">
                    {{ value }}
                  </span>
                </template>
                <template #prepend>
                  <v-spacer />
                </template>
              </v-list-item>
            </template>
          </v-select>
          <div>
            <v-text-field
              v-model="item.price"
              :disabled="isDisabled || isTeacher"
              label="Цена"
              type="number"
              suffix="руб."
              hide-spin-buttons
            />
            <div v-if="!(isDisabled || isTeacher)" class="d-flex ga-2 date-input__today">
              <a v-for="i in [200, 400]" :key="i" @click="item.price = i">{{ i }}</a>
            </div>
          </div>
        </div>
        <div class="double-input">
          <v-textarea
            v-model="item.homework_comment"
            :disabled="isDisabled"
            rows="3"
            no-resize
            auto-grow
            label="Выполнение домашнего задания"
          />
          <v-textarea
            v-if="item.ai_text"
            v-model="item.ai_text.homework_comment"
            :disabled="isDisabled"
            rows="3"
            no-resize
            auto-grow
            label="ИИ: Выполнение домашнего задания"
          />
        </div>
        <div class="double-input">
          <v-textarea
            v-model="item.cognitive_ability_comment"
            :disabled="isDisabled"
            rows="3"
            no-resize
            auto-grow
            label="Способность усваивать новый материал"
          />
          <v-textarea
            v-if="item.ai_text"
            v-model="item.ai_text.cognitive_ability_comment"
            :disabled="isDisabled"
            rows="3"
            no-resize
            auto-grow
            label="ИИ: Способность усваивать новый материал"
          />
        </div>
        <div class="double-input">
          <v-textarea
            v-model="item.knowledge_level_comment"
            :disabled="isDisabled"
            rows="3"
            no-resize
            auto-grow
            label="Текущий уровень знаний"
          />
          <v-textarea
            v-if="item.ai_text"
            v-model="item.ai_text.knowledge_level_comment"
            :disabled="isDisabled"
            rows="3"
            no-resize
            auto-grow
            label="ИИ: Текущий уровень знаний"
          />
        </div>
        <div class="double-input">
          <v-textarea
            v-model="item.recommendation_comment"
            :disabled="isDisabled"
            rows="3"
            no-resize
            auto-grow
            label="Рекомендации родителям"
          />
          <v-textarea
            v-if="item.ai_text"
            v-model="item.ai_text.recommendation_comment"
            :disabled="isDisabled"
            rows="3"
            no-resize
            auto-grow
            label="ИИ: Рекомендации родителям"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.report-dialog {
  .v-progress-linear {
    position: absolute;
    width: 100px;
    top: 26px !important;
    left: 360px;
  }
}
</style>
