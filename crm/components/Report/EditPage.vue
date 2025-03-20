<script setup lang="ts">
const route = useRoute()
const router = useRouter()
const id = Number.parseInt(route.params.id as string)
const { isTeacher } = useAuthStore()
const loading = ref(true)
const deleting = ref(false)
const saving = ref(false)
const items = ref<ReportResource[]>([])
const index = ref<number>(-1)
const item = computed<ReportResource>(() => items.value[index.value])

const tabs = {
  edit: 'редактирование отчёта',
  lessons: 'посещаемость и пройденные темы',
} as const

const selectedTab = ref<keyof typeof tabs>('edit')

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
    return !availableTeacherStatuses.includes(item.value.status) && item.value.status !== 'refused'
  }
  // админ не может редактировать статус "черновик"
  return item.value.status === 'draft'
})

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
  if (item.value.id > 0) {
    await useHttp<RealReport>(
      `reports/${item.value.id}`,
      {
        method: 'put',
        body: { ...item.value },
      },
    )
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
  const { data } = await useHttp<ReportResource[]>(
    `reports/tabs`,
    {
      params: {
        id: id || undefined,
        ...route.query,
      },
    },
  )
  items.value = data.value!
  index.value = items.value.findIndex(r => r.id === (id || -1))
  loading.value = false
}

watch(index, () => smoothScroll('main', 'top', 'instant'))

// async function loadData() {
//   loading.value = true
//   id ? await edit() : await create()
//   await loadAssociatedReports()
//   loading.value = false
// }
//
nextTick(loadData)
</script>

<template>
  <div v-if="!loading && item">
    <div class="tabs report-edit-page__tabs">
      <div
        v-for="(r, i) in items"
        :key="r.id"
        class="tabs-item report-edit-page__tabs-item"
        :class="{ 'tabs-item--active': index === i }"
        @click="index = i"
      >
        <template v-if="r.id === -1">
          новый отчёт
        </template>
        <template v-else>
          отчёт от {{ formatDate(r.created_at!) }}
          на {{ r.client_lessons.length }} занятий
        </template>
      </div>
    </div>
    <div :key="index" class="report-edit-page">
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
        <v-spacer />
        <div class="report-edit-page__actions">
          <template v-if="item.id > 0">
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
      <div v-if="selectedTab === 'edit'" class="report-edit-page__inputs">
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
              <span :class="`text-score text-score--${value}`" style="position: absolute;">
                {{ value }}
              </span>
              <span class="ml-5">
                {{ LessonScoreLabel[value as LessonScore] }}
              </span>
            </template>
            <template #item="{ props, item: { raw: { value } } }">
              <v-list-item v-bind="props">
                <template #title>
                  <span :class="`text-score text-score--${value}`" class="mr-2">
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
  </div>
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

  &__btn-tabs {
    display: flex;
    gap: 20px;
    padding: 20px;
    max-width: $width;
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
