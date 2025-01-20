<script setup lang="ts">
import { clone } from 'rambda'

const modelDefaults: ReportResource = {
  id: newId(),
  year: currentAcademicYear(),
  status: 'draft',
  price: null,
  grade: null,
  client_lessons: [],
}

const route = useRoute()
const router = useRouter()

const id = Number.parseInt(route.params.id as string)

const { isTeacher } = useAuthStore()
const item = ref<ReportResource>(modelDefaults)
const loading = ref(true)
const deleting = ref(false)
const saving = ref(false)

const availableTeacherStatuses = [
  'draft',
  'toCheck',
  'empty',
] as ReportStatus[]

const availableAdminStatuses = [
  'refused',
  'published',
] as ReportStatus[]

const availableStatuses = isTeacher ? availableTeacherStatuses : availableAdminStatuses

// если статус = разрабатывается или на проверку, или пустой отчет,
// то препод может редактировать все. Если остальные типы, то отчет нельзя редактировать
const isDisabled = computed(() => {
  return isTeacher && !availableTeacherStatuses.includes(item.value.status) && item.value.status !== 'refused'
})

async function edit() {
  loading.value = true
  const { data } = await useHttp<ReportResource>(
    `reports/${id}`,
  )
  if (data.value) {
    item.value = data.value
  }
  loading.value = false
}

async function create() {
  const r = await useHttp<ApiResponse<ReportListResource>>(`reports`, {
    params: {
      ...route.query,
      type: 0, // требуется отчет
    },
  })

  const reportListItem: ReportListResource = r.data.value!.data[0]
  const { year, program, teacher, client } = reportListItem

  item.value = clone({
    ...modelDefaults,
    year,
    program,
    teacher,
    client,
  })

  const { data } = await useHttp<JournalResource[]>(
    `reports/lessons`,
    {
      params: {
        year,
        program,
        client_id: client.id,
      },
    },
  )
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
    await router.push({ name: 'reports' })
  }
}

async function save() {
  saving.value = true
  if (id) {
    await useHttp<RealReport>(`reports/${id}`, {
      method: 'put',
      body: item.value,
    })
    saving.value = false
  }
  else {
    const { data } = await useHttp<RealReport>(
      `reports`,
      {
        method: 'post',
        body: {
          ...item.value,
          client_id: item.value.client?.id,
        },
      },
    )
    await router.push({ name: 'reports-id-edit', params: { id: data.value?.id } })
  }
}

async function loadData() {
  loading.value = true
  id ? await edit() : await create()
  loading.value = false
}

nextTick(loadData)
</script>

<template>
  <v-fade-transition>
    <div v-if="!loading" class="report-edit-page">
      <div class="report-edit-page__header">
        <h2>
          <template v-if="id">
            Редактирование отчёта
          </template>
          <template v-else>
            Новый отчёт
          </template>
        </h2>
        <div class="report-edit-page__actions">
          <template v-if="id">
            <v-btn
              v-if="!isDisabled"
              icon="$delete"
              :size="48"
              variant="text"
              :loading="deleting"
              class="remove-btn"
              @click="destroy()"
            />
            <div style="position: relative; display: inline-block">
              <CommentBtn
                variant="text"
                :entity-id="id"
                :entity-type="EntityTypeValue.report"
              />
            </div>
          </template>
          <v-btn
            icon="$save"
            :size="48"
            variant="text"
            :disabled="isDisabled"
            :loading="saving"
            @click="save()"
          />
        </div>
      </div>
      <div class="report-edit-page__inputs">
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
            :disabled="isDisabled || isTeacher"
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
      <div v-if="item.client_lessons.length" class="mt-4">
        <h2 class="page-title">
          Посещаемость и пройденные темы
        </h2>
        <JournalList :items="item.client_lessons" />
      </div>
    </div>
  </v-fade-transition>
</template>

<style lang="scss">
.report-edit-page {
  $width: 1000px;
  $padding: 20px;

  &__inputs {
    padding: $padding;
    max-width: $width;
    display: flex;
    flex-direction: column;
    gap: 30px;
  }

  &__header {
    padding: $padding;
    max-width: $width;
    display: flex;
    gap: 6px;
    align-items: center;
    justify-content: space-between;
  }
}
</style>
