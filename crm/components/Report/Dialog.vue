<script setup lang="ts">
import type { RealReport, ReportResource } from '.'
import { mdiAutoFix, mdiFileDocument, mdiKeyboardBackspace } from '@mdi/js'
import { cloneDeep } from 'lodash-es'

const emit = defineEmits<{
  updated: [r: ReportResource]
}>()

const { dialog, width } = useDialog('medium')
const item = ref<ReportResource>()
let initialStatus: ReportStatus = 'draft'
const deleting = ref(false)
const saving = ref(false)
const aiLoading = ref(false)
const isAiEditMode = ref(false) // режим редактирования текста ИИ
const isInstructionDialogOpen = ref(false)
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
    if (initialStatus === 'toCheck') {
      return true
    }
    return !availableTeacherStatuses.includes(item.value!.status) && item.value!.status !== 'refused'
  }
  // админ не может редактировать статус "черновик"
  return item.value!.status === 'draft'
})

function open(report: ReportResource) {
  item.value = cloneDeep(report)
  initialStatus = report.status
  dialog.value = true
}

async function save() {
  saving.value = true
  // Показываем уведомление только при фактической отправке на проверку.
  const isSentToCheck = isTeacher
    && item.value!.status === 'toCheck'
    && initialStatus !== 'toCheck'

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

  if (isSentToCheck) {
    useGlobalMessage('Отчет отправлен на проверку', 'success')
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
  const total = item.value.comment ? item.value.comment.length : 0

  return Math.min(Math.round(total * 100 / max), 100)
})

const aiInstructionParts = computed(() => {
  const raw = item.value?.ai_instruction || ''
  const systemMarker = '[SYSTEM INSTRUCTION]'
  const promptMarker = '[USER PROMPT]'

  const systemStart = raw.indexOf(systemMarker)
  const promptStart = raw.indexOf(promptMarker)

  if (systemStart === -1 && promptStart === -1) {
    return {
      instruction: decodeHtmlEntities(raw.trim()),
      prompt: '',
    }
  }

  // Разбираем снимок генерации на две человекочитаемые части для отображения в диалоге.
  const instructionRaw = systemStart !== -1
    ? raw.slice(systemStart + systemMarker.length, promptStart === -1 ? undefined : promptStart).trim()
    : ''

  const promptRaw = promptStart !== -1
    ? raw.slice(promptStart + promptMarker.length).trim()
    : ''

  return {
    instruction: decodeHtmlEntities(instructionRaw),
    prompt: decodeHtmlEntities(promptRaw),
  }
})

function decodeHtmlEntities(value: string): string {
  // Декодируем HTML-сущности, чтобы instruction/prompt отображались человекочитаемо.
  return value
    .replace(/&quot;/g, '"')
    .replace(/&#34;/g, '"')
    .replace(/&apos;/g, '\'')
    .replace(/&#39;/g, '\'')
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&nbsp;/g, ' ')
    .replace(/&#(\d+);/g, (_, dec: string) => String.fromCodePoint(Number(dec)))
    .replace(/&#x([0-9a-fA-F]+);/g, (_, hex: string) => String.fromCodePoint(Number.parseInt(hex, 16)))
}

interface ReportImproveResponse {
  ai_comment: string
  ai_model: string
  ai_instruction: string
}

async function improve() {
  if (!item.value) {
    return
  }
  if (!item.value.comment) {
    useGlobalMessage('Введите текст отчета', 'error')
    return
  }
  aiLoading.value = true
  const { data, error } = await useHttp<ReportImproveResponse>(
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
    // Фиксируем все фактические AI-данные в текущем состоянии отчета до сохранения.
    item.value.ai_comment = data.value.ai_comment || null
    item.value.ai_model = data.value.ai_model || null
    item.value.ai_instruction = data.value.ai_instruction || null
  }
  aiLoading.value = false
}

function editAiComment() {
  isAiEditMode.value = true
  setTimeout(() => smoothScroll('dialog', 'bottom'), 0)
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
            :disabled="!item.ai_instruction"
            variant="text"
            :size="48"
            class="text-none"
            :icon="mdiFileDocument"
            @click="isInstructionDialogOpen = true"
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
            <div v-if="!(isDisabled || isTeacher)" class="d-flex ga-2 under-input">
              <a v-for="i in [200, 400]" :key="i" @click="item.price = i">{{ i }}</a>
            </div>
          </div>
        </div>

        <div class="report-dialog__text-field">
          <v-textarea
            v-model="item.comment"
            :disabled="isDisabled || aiLoading"
            :rows="20"
            no-resize
            auto-grow
            label="Текст отчета"
          />
          <div v-if="item.ai_comment" class="ai-suggest__wrapper">
            <template v-if="isAiEditMode">
              <v-textarea v-model="item.ai_comment" auto-grow />
              <div class="under-input">
                <a style="position: relative; left: -10px" @click="isAiEditMode = false">
                  <v-icon :icon="mdiKeyboardBackspace" :size="12" />
                  вернуться к просмотру
                </a>
              </div>
            </template>
            <template v-else>
              <div class="ai-suggest ai-report__text" v-html="item.ai_comment" />
              <div class="under-input d-flex justify-space-between">
                <a @click="editAiComment()">редактировать</a>
                <div v-if="isAdmin && item.ai_model" class="pr-4 text-gray d-flex ga-1 align-center">
                  <v-icon :icon="mdiAutoFix" :size="16" class="vf-1" />
                  {{ item.ai_model }}
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </v-dialog>

  <v-dialog v-model="isInstructionDialogOpen" max-width="900">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        Просмотр инструкции
        <v-btn icon="$close" :size="48" variant="text" @click="isInstructionDialogOpen = false" />
      </div>
      <div class="dialog-body">
        <h2>
          Инструкция
        </h2>
        <div class="report-dialog__ai-instruction-text">
          {{ aiInstructionParts.instruction }}
        </div>

        <h2 class="mt-6">
          Промпт
        </h2>
        <div class="report-dialog__ai-instruction-text">
          {{ aiInstructionParts.prompt }}
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

  &__ai-instruction-text {
    white-space: pre-wrap;
    word-break: break-word;
  }
}
</style>
