<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  deleted: [r: ReportResource]
  updated: [r: RealReport]
  created: [r: RealReport, fakeItemId: string]
}>()
const modelDefaults: ReportResource = {
  id: newId(),
  year: currentAcademicYear(),
  status: 'new',
  price: null,
  grade: null,
  client_lessons: [],
}
const { dialog, width } = useDialog('large')
const { isTeacher } = useAuthStore()
const itemId = ref<number>()
let fakeItemId: string = ''
const item = ref<ReportResource>(modelDefaults)
const loading = ref(false)
const deleting = ref(false)

// если статус = разрабатывается или на проверку, или пустой отчет,
// то препод может редактировать все. Если остальные типы, то отчет нельзя редактировать
const isDisabled = computed(() => {
  return isTeacher && !([
    'new',
    'toCheck',
    'empty',
  ] as ReportStatus[]).includes(item.value.status)
})

async function edit(reportId: number) {
  itemId.value = reportId
  dialog.value = true
  loading.value = true
  const { data } = await useHttp<ReportResource>(
    `reports/${reportId}`,
  )
  if (data.value) {
    item.value = data.value
  }
  loading.value = false
}

async function create(r: FakeReport) {
  itemId.value = undefined
  fakeItemId = r.id
  const { year, program, teacher, client } = r
  item.value = clone({
    ...modelDefaults,
    year,
    teacher,
    client,
    program,
  })
  dialog.value = true
  const { data } = await useHttp<ReportClientLessonResource[]>(`reports/lessons`, {
    params: {
      year,
      program,
      client_id: client.id,
    },
  })
  item.value.client_lessons = data.value!
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить отчёт?')) {
    return
  }
  deleting.value = true
  const { status } = await useHttp(`reports/${item.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    deleting.value = false
  }
  else {
    emit('deleted', item.value)
    dialog.value = false
    setTimeout(() => (deleting.value = false), 300)
  }
}

async function save() {
  dialog.value = false
  if (itemId.value) {
    const { data } = await useHttp<RealReport>(`reports/${itemId.value}`, {
      method: 'put',
      body: item.value,
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
  else {
    const { data } = await useHttp<RealReport>('reports', {
      method: 'post',
      body: {
        ...item.value,
        client_id: item.value.client?.id,
      },
    })
    if (data.value) {
      emit('created', data.value, fakeItemId)
    }
  }
}

defineExpose({ edit, create })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <div v-if="itemId">
          Редактирование отчёта
          <div class="dialog-subheader">
            <template v-if="item.created_at">
              {{ formatDateTime(item.created_at) }}
            </template>
          </div>
        </div>
        <template v-else>
          Новый отчёт
        </template>
        <div>
          <template v-if="itemId">
            <v-btn
              v-if="!isDisabled"
              icon="$delete"
              :size="48"
              class="remove-btn"
              variant="text"
              :loading="deleting"
              @click="destroy()"
            />
            <div style="position: relative; display: inline-block">
              <CommentBtn
                variant="text"
                :entity-id="itemId"
                :entity-type="EntityTypeValue.report"
              />
            </div>
          </template>
          <v-btn
            icon="$save"
            :size="48"
            variant="text"
            :disabled="isDisabled"
            @click="save()"
          />
        </div>
      </div>
      <UiLoader v-if="loading" />
      <div v-else class="dialog-body">
        <div v-if="item.client_lessons.length">
          <div class="font-weight-bold mb-4">
            Посещаемость и пройденные темы:
          </div>
          <ReportClientLessons :items="item.client_lessons" />
        </div>
        <div class="double-input">
          <div v-if="item.teacher">
            <v-text-field
              :model-value="formatNameInitials(item.teacher)"
              label="Преподаватель"
              disabled
            />
          </div>
          <div v-if="item.client">
            <v-text-field
              :model-value="formatName(item.client)"
              label="Клиент"
              disabled
            />
          </div>
        </div>
        <div class="double-input">
          <UiClearableSelect
            v-model="item.year"
            label="Учебный год"
            :items="selectItems(YearLabel)"
            disabled
          />
          <UiClearableSelect
            v-model="item.program"
            :items="selectItems(ProgramLabel)"
            label="Программа"
            disabled
          />
        </div>
        <div class="double-input">
          <v-select
            v-model="item.status"
            label="Статус"
            :items="selectItems(ReportStatusLabel)"
            :disabled="isDisabled"
          />
          <v-select
            v-model="item.grade"
            label="Оценка"
            :items="selectItems(LessonScoreLabel)"
            :disabled="isDisabled"
          >
            <template #selection="{ item: { raw: { value } } }">
              <span :class="`score score--${value}`" style="position: absolute;">
                {{ value }}
              </span>
              <span class="ml-10">
                {{ LessonScoreLabel[value as LessonScore] }}
              </span>
            </template>
            <template #item="{ props, item: { raw: { value } } }">
              <v-list-item v-bind="props">
                <template #title>
                  <span :class="`score score--${value}`" class="mr-2">
                    {{ value }}
                  </span>
                  {{ LessonScoreLabel[value as LessonScore] }}
                </template>
                <template #prepend>
                  <v-spacer />
                </template>
              </v-list-item>
            </template>
          </v-select>
          <v-text-field
            v-model="item.price"
            :disabled="isDisabled"
            label="Цена"
            type="number"
            suffix="руб."
            hide-spin-buttons
          />
        </div>

        <div>
          <v-textarea
            v-model="item.homework_comment"
            :disabled="isDisabled"
            rows="3"
            no-resize
            auto-grow
            label="Выполнение домашнего задания"
          />
        </div>
        <div>
          <v-textarea
            v-model="item.cognitive_ability_comment"
            :disabled="isDisabled"
            rows="3"
            no-resize
            auto-grow
            label="Способность усваивать новый материал"
          />
        </div>
        <div>
          <v-textarea
            v-model="item.knowledge_level_comment"
            :disabled="isDisabled"
            rows="3"
            no-resize
            auto-grow
            label="Текущий уровень знаний"
          />
        </div>
        <div>
          <v-textarea
            v-model="item.recommendation_comment"
            :disabled="isDisabled"
            rows="3"
            no-resize
            auto-grow
            label="Рекомендации родителям"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
