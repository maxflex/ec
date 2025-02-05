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
const associatedReports = ref<RealReport[]>([])

const tabs = {
  edit: 'редактирование отчёта',
  lessons: 'посещаемость и пройденные темы',
} as const

const selectedTab = ref<keyof typeof tabs>('edit')

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

const isDisabled = computed(() => {
// если статус = черновик или на проверку, или пустой отчет,
// то препод может редактировать все. Если остальные типы, то отчет нельзя редактировать
  if (isTeacher) {
    return !availableTeacherStatuses.includes(item.value.status) && item.value.status !== 'refused'
  }
  // админ не может редактировать статус "черновик"
  return item.value.status === 'draft'
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
  const r = await useHttp<ApiResponse<ReportListResource>>(
    `reports`,
    {
      params: {
        ...route.query,
        requirement: 'required', // требуется отчет
      },
    },
  )

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

async function loadAssociatedReports() {
  const { year, program, teacher, client } = item.value
  const { data } = await useHttp<ApiResponse<RealReport>>(
    `reports`,
    {
      params: {
        requirement: 'created',
        year,
        program,
        teacher_id: teacher!.id,
        client_id: client!.id,
      },
    },
  )
  associatedReports.value = data.value!.data
}

async function loadData() {
  loading.value = true
  id ? await edit() : await create()
  await loadAssociatedReports()
  loading.value = false
}

nextTick(loadData)
</script>

<template>
  <v-fade-transition group>
    <div v-if="!loading" class="tabs report-edit-page__tabs">
      <RouterLink
        v-for="r in associatedReports" :key="r.id" class="tabs-item report-edit-page__tabs-item"
        :class="{ 'tabs-item--active': r.id === id }"
        :to="{ name: 'reports-id-edit', params: { id: r.id } }"
      >
        отчёт от {{ formatDate(r.created_at) }}
        на {{ r.lessons_count }} занятий
      </RouterLink>
    </div>
    <div v-if="!loading" class="report-edit-page">
      <div class="report-edit-page__btn-tabs">
        <v-btn
          v-for="(label, tab) in tabs"
          :key="tab"
          class="tab-btn"
          :class="{ 'tab-btn--active': selectedTab === tab }"
          variant="plain"
          :ripple="false"
          @click="selectedTab = tab"
        >
          {{ label }}
        </v-btn>
      </div>

      <div v-if="selectedTab === 'edit'" class="report-edit-page__inputs">
        <div class="report-edit-page__header">
          <h2>
            {{ id ? 'Редактирование отчёта' : 'Новый отчёт' }}
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
                  color="gray"
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
      <div v-else>
        <UiNoData v-if="item.client_lessons.length === 0" />
        <JournalList v-else :items="item.client_lessons" class="pt-6" />
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
    padding: 0 0 $padding $padding;
    max-width: $width;
    display: flex;
    gap: 6px;
    align-items: center;
    justify-content: space-between;
  }

  &__btn-tabs {
    display: flex;
    gap: 20px;
    padding: 20px;
  }

  &__tabs {
    position: sticky;
    top: 0;
    min-height: 51px;
    z-index: 1;
    background: white;
    a {
      color: black !important;
    }
    &-item {
      background: white;
      & > div {
        text-align: center;
        //&:last-child {
        //  font-size: 13px;
        //}
      }
      &--active {
        background: #e4e4e4 !important;
      }
    }
  }
}
</style>
