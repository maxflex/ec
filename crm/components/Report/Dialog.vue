<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  deleted: [r: ReportResource]
  updated: [r: RealReportItem]
  created: [r: RealReportItem, fakeItemId: string]
}>()
const modelDefaults: ReportResource = {
  id: newId(),
  year: currentAcademicYear(),
  is_moderated: false,
  is_published: false,
  price: null,
  grade: null,
  client_lessons: [],
}
const { dialog, width } = useDialog('default')
const itemId = ref<number>()
let fakeItemId: string = ''
const item = ref<ReportResource>(modelDefaults)
const loading = ref(false)
const deleting = ref(false)

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

async function create(r: FakeReportItem) {
  itemId.value = undefined
  fakeItemId = r.id
  item.value = clone({
    ...modelDefaults,
    year: r.year,
    teacher: r.teacher,
    client: r.client,
    program: r.program,
  })
  dialog.value = true
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
    const { data } = await useHttp<RealReportItem>(`reports/${itemId.value}`, {
      method: 'put',
      body: item.value,
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
  else {
    const { data } = await useHttp<RealReportItem>('reports', {
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
          <v-btn
            v-if="itemId"
            icon="$delete"
            :size="48"
            class="remove-btn"
            variant="text"
            :loading="deleting"
            @click="destroy()"
          />
          <v-btn
            icon="$save"
            :size="48"
            variant="text"
            @click="save()"
          />
        </div>
      </div>
      <UiLoader v-if="loading" />
      <div v-else class="dialog-body">
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
          <v-select v-model="item.grade" label="Оценка" :items="selectItems(LessonScoreLabel)">
            <template #selection="{ item }">
              <span :class="`score score--${item.raw.value}`" style="position: absolute;">
                {{ item.raw.value }}
              </span>
              <span class="ml-10">
                {{ LessonScoreLabel[item.raw.value] }}
              </span>
            </template>
            <template #item="{ props, item }">
              <v-list-item v-bind="props">
                <template #title>
                  <span :class="`score score--${item.raw.value}`" class="mr-2">
                    {{ item.raw.value }}
                  </span>
                  {{ LessonScoreLabel[item.raw.value] }}
                </template>
                <template #prepend>
                  <v-spacer />
                </template>
              </v-list-item>
            </template>
          </v-select>
          <v-text-field
            v-model="item.price"
            label="Цена"
            type="number"
            suffix="руб."
            hide-spin-buttons
          />
        </div>
        <div>
          <v-textarea
            v-model="item.homework_comment"
            rows="3"
            no-resize
            auto-grow
            label="Выполнение домашнего задания"
          />
        </div>
        <div>
          <v-textarea
            v-model="item.cognitive_ability_comment"
            rows="3"
            no-resize
            auto-grow
            label="Способность усваивать новый материал"
          />
        </div>
        <div>
          <v-textarea
            v-model="item.knowledge_level_comment"
            rows="3"
            no-resize
            auto-grow
            label="Текущий уровень знаний"
          />
        </div>
        <div>
          <v-textarea
            v-model="item.recommendation_comment"
            rows="3"
            no-resize
            auto-grow
            label="Рекомендации родителям"
          />
        </div>
        <div>
          <v-checkbox
            v-model="item.is_moderated"
            label="Промодерирован"
          />
          <v-checkbox
            v-model="item.is_published"
            label="Опубликован"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
