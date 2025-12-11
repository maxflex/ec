<script setup lang="ts">
import type { RealReport, ReportResource, ReportTextField, ReportTextFields } from '.'
import { mdiAutoFix } from '@mdi/js'
import { diffWordsWithSpace } from 'diff'
import { cloneDeep } from 'lodash-es'
import { ReportTextFieldLabel } from '.'

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

const aiImproved = ref<Partial<Record<ReportTextField, boolean>>>({})
const aiDiff = ref<Partial<Record<ReportTextField, string | boolean>>>({})

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
  const { data, error } = await useHttp<ReportTextFields>(`reports/improve`, {
    method: 'POST',
    body: {
      cognitive_ability_comment: item.value!.cognitive_ability_comment,
      homework_comment: item.value!.homework_comment,
      knowledge_level_comment: item.value!.knowledge_level_comment,
      recommendation_comment: item.value!.recommendation_comment,
    } as ReportTextFields,
  })
  if (error.value) {
    setTimeout(() => useGlobalMessage(`<b>Ошибка ИИ</b>: ${error.value!.data.message}`, 'error'), 100)
  }
  if (data.value) {
    item.value.ai_text = data.value!
  }
  for (const field in ReportTextFieldLabel) {
    const diffHtml = generateDiffHtml(field as ReportTextField)
    aiDiff.value[field as ReportTextField] = isTextEqual(field as ReportTextField) ? false : diffHtml
  }
  aiImproved.value = {}
  aiLoading.value = false
}

function isTextEqual(field: ReportTextField): boolean {
  // Безопасное получение значений (на случай null/undefined)
  const original = item.value?.[field] ?? ''
  const ai = item.value?.ai_text?.[field] ?? ''

  const normalize = (str: string) => {
    return str
      .trim() // Убираем пробелы по краям
      .replace(/\r\n/g, '\n') // Приводим переносы Windows к Unix
      .replace(/\r/g, '\n') // Убираем старые Mac переносы
      .replace(/\n+/g, '\n') // Считаем 1 Enter и 2 Enter одинаковыми (схлопываем пустые строки)
      .replace(/[ \t]+/g, ' ') // Схлопываем двойные пробелы и табы в один пробел
      // .replace(/ё/g, 'е')      // <-- Раскомментируйте, если хотите считать "е" и "ё" одинаковыми
  }

  return normalize(original) === normalize(ai)
}

// Функция принимает два текста и возвращает HTML с подсветкой
function generateDiffHtml(field: ReportTextField) {
  const diff = diffWordsWithSpace(item.value![field], item.value!.ai_text![field])

  let html = ''

  diff.forEach((part) => {
    // part.added — если добавлено (зеленый)
    // part.removed — если удалено (красный)
    // иначе — обычный текст

    if (part.added) {
      html += `<span class="diff-added">${part.value}</span>`
    }
    else if (part.removed) {
      html += `<span class="diff-removed">${part.value}</span>`
    }
    else {
      html += part.value
    }
  })

  return html
}

function applyAi(field: ReportTextField) {
  item.value![field] = item.value!.ai_text![field]
  // item.value!.ai_text![field] = ''
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
        <div v-for="(label, field) in ReportTextFieldLabel" :key="field">
          <v-textarea
            v-model="item[field]"
            :disabled="isDisabled"
            rows="3"
            no-resize
            auto-grow
            :label="label"
          />
          <div v-if="item.ai_text && item.ai_text[field]" class="ai-suggest__wrapper">
            <template v-if="!aiDiff[field]">
              <div class="ai-suggest">
                <span class="text-label">без изменений</span>
              </div>
            </template>
            <template v-else>
              <div
                class="ai-suggest"
                v-html="aiDiff[field]"
              />
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

  .v-textarea {
    .v-field {
      border-radius: 4px 4px 0 0 !important;
    }
  }

  .ai-suggest {
    padding: 16px 16px;
    // border: 1px solid #9a9a9a;
    border: 1px solid rgb(var(--v-theme-secondary));
    border-radius: 0 0 4px 4px;
    margin: 20px 0 0;
    position: relative;
    background: rgba(var(--v-theme-secondary), 0.05);
    white-space: break-spaces;
    min-height: 104px;

    &__wrapper {
      top: -21px;
      position: relative;
    }

    &__empty {
      visibility: hidden;
    }

    &:before {
      content: 'Предложение ИИ';
      position: absolute;
      background: white;
      z-index: 1;
      font-size: 12px;
      // color: rgb(var(--v-theme-label));
      color: rgb(var(--v-theme-secondary));
      padding: 0 4px;
      left: 11px;
      top: -7px;
      line-height: 12px;
      border-radius: 4px;
    }

    &__apply {
      margin-bottom: 20px;
      cursor: default !important;
      a {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        cursor: pointer;
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
