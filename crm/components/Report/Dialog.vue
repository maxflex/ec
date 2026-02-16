<script setup lang="ts">
import type { RealReport, ReportResource, ReportTextField, ReportTextFields } from '.'
import { mdiAutoFix } from '@mdi/js'
import { cloneDeep } from 'lodash-es'
import { getReportTextFields, ReportTextFieldLabel } from '.'

const emit = defineEmits<{
  updated: [r: ReportResource]
}>()

const { dialog, width } = useDialog('medium')
const item = ref<ReportResource>()
const deleting = ref(false)
const saving = ref(false)
const aiLoading = ref(false)
const aiLoading2 = ref(false)
const router = useRouter()
const { isAdmin, isTeacher, user } = useAuthStore()
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

const aiTestComment = ref<string | null>(null)
const testComment = ref('')

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

const textFields = computed(() => getReportTextFields(item.value!))
// новый режим единого поля для отчета
const isSingleField = computed(() => textFields.value.length === 1)

function open(report: ReportResource) {
  item.value = cloneDeep(report)
  aiTestComment.value = null
  testComment.value = ''
  dialog.value = true
}

async function save() {
  saving.value = true
  if (item.value!.id > 0) {
    const { data } = await useHttp<ReportResource>(
      `reports/${item.value!.id}`,
      {
        method: 'put',
        body: cloneDeep(item.value),
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
          ...cloneDeep(item.value),
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
  const { status } = await useHttp(
    `reports/${item.value!.id}`,
    {
      method: 'delete',
    },
  )
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
  for (const field of textFields.value) {
    total += (item.value[field] ? item.value[field].length : 0)
  }

  return Math.min(Math.round(total * 100 / max), 100)
})

async function improve() {
  if (!item.value) {
    return
  }
  // Старый AI-режим для 4 полей полностью отключен: улучшаем только единое поле.
  if (!isSingleField.value) {
    return
  }
  if (!item.value.comment) {
    useGlobalMessage('Введите текст отчета', 'error')
    return
  }
  aiLoading.value = true
  const { data, error } = await useHttp<Partial<ReportTextFields>>(
    `reports/improve`,
    {
      method: 'POST',
      body: {
        id: item.value.id,
        comment: item.value.comment,
      },
    },
  )
  if (error.value) {
    aiLoading.value = false
    setTimeout(() => useGlobalMessage(`<b>Ошибка ИИ</b>: ${error.value!.data.message}`, 'error'), 100)
  }
  if (data.value) {
    item.value.ai_comment = data.value.comment || null
  }
  aiLoading.value = false
}

// временно
const isTest = computed(() => isAdmin && user && [1, 5, 151, 212].includes(user.id))

async function improve2(company: Company) {
  if (!item.value) {
    return
  }
  if (!testComment.value) {
    useGlobalMessage('Введите текст отчета', 'error')
    return
  }
  aiLoading2.value = true
  const { data, error } = await useHttp<Partial<ReportTextFields>>(
    `reports/improve`,
    {
      method: 'POST',
      body: {
        company,
        id: item.value.id,
        comment: testComment.value,
      },
    },
  )
  if (error.value) {
    aiLoading2.value = false
    setTimeout(() => useGlobalMessage(`<b>Ошибка ИИ</b>: ${error.value!.data.message}`, 'error'), 100)
  }
  if (data.value) {
    aiTestComment.value = data.value.comment || null
  }
  aiLoading2.value = false
}

// для теста
function fillComment() {
  if (!item.value) {
    return
  }

  if (item.value.comment) {
    testComment.value = item.value.comment
    return
  }

  const result: string[] = []
  const fields: ReportTextField[] = [
    'homework_comment',
    'cognitive_ability_comment',
    'knowledge_level_comment',
    'recommendation_comment',
  ]

  for (const f of fields) {
    const text = item.value[f]
    if (text) {
      result.push(`${ReportTextFieldLabel[f]}
${text}`)
    }
  }

  testComment.value = result.join(`

`)
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
          <v-menu v-if="isTest">
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                :icon="mdiAutoFix"
                :size="48"
                :loading="aiLoading2"
                color="success"
                variant="text"
              />
            </template>
            <v-list>
              <v-list-item @click="fillComment()">
                заполнить текст отчета
              </v-list-item>
              <v-list-item @click="improve2('ooo')">
                сгенерировать (ООО – оригинал)
              </v-list-item>
              <v-list-item @click="improve2('ip')">
                сгенерировать (ИП – Костя)
              </v-list-item>
              <v-list-item @click="improve2('ano')">
                сгенерировать (АНО – Антон)
              </v-list-item>
              <v-list-item target="_blank" href="https://v3-api.ege-centr.ru/storage/ai.txt" :disabled="!aiTestComment">
                открыть инструкцию
              </v-list-item>
            </v-list>
          </v-menu>
          <v-btn
            v-if="isAdmin"
            :icon="mdiAutoFix"
            :size="48"
            :loading="aiLoading"
            variant="text"
            :disabled="isDisabled || !isSingleField"
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
            <div v-if="!(isDisabled || isTeacher)" class="d-flex ga-2 under-input">
              <a v-for="i in [200, 400]" :key="i" @click="item.price = i">{{ i }}</a>
            </div>
          </div>
        </div>

        <div v-if="isTest" class="report-dialog__text-field mb-4">
          <v-textarea
            v-model="testComment"
            :rows="5"
            no-resize
            auto-grow
            label="Тестирование"
            color="success"
            :hide-details="!!aiTestComment"
            persistent-hint
            :disabled="aiLoading2"
            hint="Это поле видно только вам"
          />
          <div v-if="aiTestComment" class="ai-report ai-suggest__wrapper">
            <div class="ai-suggest ai-report__text" v-html="aiTestComment" />
          </div>
        </div>

        <div v-for="field in textFields" :key="field" class="report-dialog__text-field">
          <v-textarea
            v-model="item[field]"
            :disabled="isDisabled || aiLoading"
            :rows="isSingleField ? 20 : 5"
            no-resize
            auto-grow
            :label="ReportTextFieldLabel[field]"
          />
          <div v-if="isAdmin && isSingleField && item.ai_comment" class="ai-suggest__wrapper">
            <div class="ai-suggest ai-report__text" v-html="item.ai_comment" />
          </div>
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

  &__text-field {
    &:has(.ai-suggest) {
      .v-textarea {
        .v-field {
          border-radius: 4px 4px 0 0 !important;
        }
      }
    }
  }
}
</style>
