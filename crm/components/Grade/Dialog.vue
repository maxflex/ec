<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  deleted: [r: GradeResource]
  updated: [r: RealGradeItem]
  created: [r: RealGradeItem, fakeItemId: string]
}>()
const modelDefaults: GradeResource = {
  id: newId(),
  year: currentAcademicYear(),
  grade: null,
}
const { dialog, width } = useDialog('default')
const itemId = ref<number>()
let fakeItemId: string = ''
const item = ref<GradeResource>(modelDefaults)
const loading = ref(false)
const deleting = ref(false)

async function edit(gradeId: number) {
  itemId.value = gradeId
  dialog.value = true
  loading.value = true
  const { data } = await useHttp<GradeResource>(
    `grades/${gradeId}`,
  )
  if (data.value) {
    item.value = data.value
  }
  loading.value = false
}

async function create(g: FakeGradeItem) {
  itemId.value = undefined
  fakeItemId = g.id
  item.value = clone({
    ...modelDefaults,
    year: g.year,
    client: g.client,
    program: g.program,
    quarter: g.quarter,
  })
  dialog.value = true
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить отчёт?')) {
    return
  }
  deleting.value = true
  const { status } = await useHttp(`grades/${item.value.id}`, {
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
    const { data } = await useHttp<RealGradeItem>(`grades/${itemId.value}`, {
      method: 'put',
      body: item.value,
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
  else {
    const { data } = await useHttp<RealGradeItem>('grades', {
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
        <tempate v-if="itemId">
          Редактирование оценки
        </tempate>
        <template v-else>
          Новая оценка
        </template>
        <v-btn
          icon="$save"
          :size="48"
          variant="text"
          @click="save()"
        />
      </div>
      <UiLoaderr v-if="loading" />
      <div v-else class="dialog-body">
        <div class="double-input">
          <div v-if="item.client">
            <v-text-field
              :model-value="formatName(item.client)"
              label="Клиент"
              disabled
            />
          </div>

          <UiClearableSelect
            v-model="item.program"
            :items="selectItems(ProgramLabel)"
            label="Программа"
            disabled
          />
        </div>
        <div class="double-input">
          <UiClearableSelect
            v-model="item.year"
            label="Учебный год"
            :items="selectItems(YearLabel)"
            disabled
          />
          <UiClearableSelect
            v-model="item.quarter"
            :items="selectItems(QuarterLabel)"
            label="Четверть"
            disabled
          />
        </div>
        <div>
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
          <!-- <UiClearableSelect
            v-model="item.grade"
            label="Оценка"
            :items="selectItems(LessonScoreLabel)"
          /> -->
        </div>
        <div
          v-if="itemId"
          class="dialog-bottom"
        >
          <span v-if="item.created_at">
            оценка создана
            {{ formatDateTime(item.created_at) }}
          </span>
          <v-btn
            icon="$delete"
            :size="48"
            color="red"
            variant="plain"
            :loading="deleting"
            @click="destroy()"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
