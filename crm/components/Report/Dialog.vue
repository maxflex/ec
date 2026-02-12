<script setup lang="ts">
import type { RealReport, ReportResource, ReportTextField, ReportTextFields } from '.'
import { mdiAutoFix } from '@mdi/js'
import { cloneDeep } from 'lodash-es'
import { getAiDiff, getReportTextFields, isAiTextEqual, ReportTextFieldLabel } from '.'

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

const aiText = ref<Partial<ReportTextFields>>()
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
const aiImproved = ref<Partial<Record<ReportTextField, boolean>>>({})
const aiDiff = ref<Partial<Record<ReportTextField, string | boolean>>>({})

function open(report: ReportResource) {
  item.value = cloneDeep(report)
  aiText.value = undefined
  aiTestComment.value = null
  testComment.value = ''
  aiImproved.value = {}
  aiDiff.value = {}
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
  if (isSingleField.value && !item.value.comment) {
    useGlobalMessage('Введите текст отчета', 'error')
    return
  }
  aiLoading.value = true
  const body = isSingleField.value
    ? {
        id: item.value.id,
        comment: item.value.comment,
      }
    : textFields.value.reduce((carry, field) => ({ ...carry, [field]: item.value![field] }), {
        id: item.value.id,
      })

  const { data, error } = await useHttp<Partial<ReportTextFields>>(
    `reports/improve`,
    {
      method: 'POST',
      body,
    },
  )
  if (error.value) {
    aiLoading.value = false
    setTimeout(() => useGlobalMessage(`<b>Ошибка ИИ</b>: ${error.value!.data.message}`, 'error'), 100)
  }
  if (data.value) {
    if (isSingleField.value) {
      item.value.ai_comment = data.value.comment || null
    }
    else {
      aiText.value = data.value
      for (const f in data.value) {
        const field = f as ReportTextField
        aiDiff.value[field] = isAiTextEqual(item.value, aiText.value, field)
          ? false
          : getAiDiff(item.value, aiText.value, field)
      }
    }
  }
  aiImproved.value = {}
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

function applyAi(field: ReportTextField) {
  item.value![field] = aiText.value![field]!
  aiImproved.value[field] = true
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
            <div v-if="!(isDisabled || isTeacher)" class="d-flex ga-2 under-input">
              <a v-for="i in [200, 400]" :key="i" @click="item.price = i">{{ i }}</a>
            </div>
          </div>
        </div>

        <div v-if="isTest" class="report-dialog__text-field">
          <v-textarea
            v-model="testComment"
            rows="3"
            no-resize
            auto-grow
            label="Тестирование"
          />
          <div v-if="aiTestComment" class="ai-report ai-suggest__wrapper">
            <div class="ai-suggest ai-report__text" v-html="aiTestComment" />
          </div>
          <div v-else class="ai-suggest__wrapper">
            <div class="ai-suggest">
              <div class="ai-suggest__empty">
                {{ testComment }}
              </div>
            </div>
          </div>
        </div>

        <div v-for="field in textFields" :key="field" class="report-dialog__text-field">
          <v-textarea
            v-model="item[field]"
            :disabled="isDisabled"
            rows="3"
            no-resize
            auto-grow
            :label="ReportTextFieldLabel[field]"
          />
          <template v-if="isAdmin">
            <div v-if="isSingleField" class="ai-suggest__wrapper">
              <div v-if="item.ai_comment" class="ai-suggest ai-report__text" v-html="item.ai_comment" />
              <div v-else class="ai-suggest">
                <div class="ai-suggest__empty">
                  {{ item[field] }}
                </div>
              </div>
            </div>
            <div v-else-if="aiText && aiText[field]" class="ai-suggest__wrapper">
              <template v-if="!aiDiff[field]">
                <div class="ai-suggest">
                  <span class="text-label">без изменений</span>
                </div>
              </template>
              <template v-else>
                <div class="ai-suggest" v-html="aiDiff[field]" />
                <div class="ai-suggest__apply under-input">
                  <span v-if="aiImproved[field]" class="text-gray">
                    применить изменения
                  </span>
                  <a v-else @click="applyAi(field)">
                    применить изменения
                  </a>
                </div>
              </template>
            </div>
            <div v-else class="ai-suggest__wrapper">
              <div class="ai-suggest">
                <div class="ai-suggest__empty">
                  {{ item[field] }}
                </div>
              </div>
            </div>
          </template>
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

  /* Удаленный текст: светло-красный фон, зачеркнутый текст */
  .diff-removed {
    background-color: #ffeef0;
    // color: #b31d28;
    // text-decoration: line-through;
    // text-decoration-color: rgb(var(--v-theme-error)); /* Цвет зачеркивания */
    background-color: #edc6cb;
  }

  /* Добавленный текст: светло-зеленый фон */
  .diff-added {
    // background-color: #e6ffed;
    // color: #22863a;
    background-color: #bdeec4;
    // background-color: rgb(var(--v-theme-success));
  }
}
</style>
