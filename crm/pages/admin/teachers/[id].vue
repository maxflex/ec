<script setup lang="ts">
import type { TeacherDialog } from '#build/components'
import type { Filters } from '~/components/Report/TeacherFilters.vue'

const route = useRoute()
const teacher = ref<TeacherResource>()
const teacherDialog = ref<InstanceType<typeof TeacherDialog>>()

const tabs = {
  groups: 'группы',
  schedule: 'расписание',
  payments: 'платежи',
  balance: 'баланс',
  reports: 'отчеты',
  reviews: 'отзывы',
  services: 'допуслуги',
} as const

const selectedTab = ref<keyof typeof tabs>('groups')

const groupFilters = ref<{ year: Year }>({
  year: currentAcademicYear(),
})
const reportFilters = ref<Filters>({
  year: currentAcademicYear(),
})

const paymentFilters = ref<{ year?: Year }>({})
const serviceFilters = ref<{ year?: Year }>({})
// const reviewFilters = ref<{ year?: Year }>({})

async function loadData() {
  const { data } = await useHttp<TeacherResource>(`teachers/${route.params.id}`)
  if (data.value) {
    teacher.value = data.value
  }
}

function onTeacherUpdated(t: TeacherResource) {
  teacher.value = t
}

nextTick(loadData)
</script>

<template>
  <div
    v-if="teacher"
    class="teacher"
  >
    <div class="panel">
      <div class="panel-info">
        <div>
          <UiAvatar :item="teacher" />
        </div>
        <div>
          <div>преподаватель {{ teacher.id }}</div>
          <div>
            {{ formatFullName(teacher) }}
            <PhoneActions :items="teacher.phones" />
          </div>
        </div>
        <div>
          <div>предметы</div>
          <div>
            {{ teacher.subjects.map(s => SubjectLabel[s]).join(', ') }}
          </div>
        </div>
        <div>
          <div>статус</div>
          <div>
            {{ TeacherStatusLabel[teacher.status] }}
          </div>
        </div>
        <div class="panel-actions">
          <PreviewModeBtn
            :user="{
              id: teacher.id,
              entity_type: EntityType.teacher,
            }"
          />
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="teacherDialog?.edit(teacher!)"
          />
        </div>
      </div>
      <div class="tabs">
        <div
          v-for="(label, key) in tabs"
          :key="key"
          class="tabs-item"
          :class="{ 'tabs-item--active': selectedTab === key }"
          @click="selectedTab = key"
        >
          {{ label }}
        </div>
      </div>
    </div>
    <UiDataLoader
      v-if="selectedTab === 'groups'"
      :key="`groups${groupFilters.year}`"
      url="groups"
      :filters="{
        teacher_id: teacher.id,
        ...groupFilters,
      }"
    >
      <template #filters>
        <div class="filters">
          <div class="filters-inputs">
            <div>
              <v-select
                v-model="groupFilters.year"
                label="Учебный год"
                :items="selectItems(YearLabel)"
                density="comfortable"
              />
            </div>
          </div>
        </div>
      </template>
      <template #default="{ items }">
        <div class="table table--padding">
          <GroupList
            :items="items"
          />
        </div>
      </template>
    </UiDataLoader>
    <LessonList
      v-else-if="selectedTab === 'schedule'"
      :id="teacher.id"
      entity="teacher"
    />
    <UiDataLoader
      v-else-if="selectedTab === 'reports'"
      url="reports"
      :filters="{
        teacher_id: teacher.id,
        ...reportFilters,
      }"
    >
      <template #filters>
        <div class="filters">
          <ReportTeacherFilters @apply="f => (reportFilters = f)" />
        </div>
      </template>
      <template #default="{ items }">
        <ReportList :items="items" />
      </template>
    </UiDataLoader>
    <UiDataLoader
      v-else-if="selectedTab === 'payments'"
      :key="`payments${paymentFilters.year}`"
      url="teacher-payments"
      :filters="{
        teacher_id: teacher.id,
        ...paymentFilters,
      }"
    >
      <template #filters>
        <div class="filters">
          <div class="filters-inputs">
            <div>
              <UiClearableSelect
                v-model="paymentFilters.year"
                label="Учебный год"
                :items="selectItems(YearLabel)"
                density="comfortable"
              />
            </div>
          </div>
        </div>
      </template>
      <template #default="{ items }">
        <TeacherPaymentList
          :items="items"
          :teacher-id="teacher.id"
        />
      </template>
    </UiDataLoader>
    <UiDataLoader
      v-else-if="selectedTab === 'reviews'"
      url="client-reviews"
      :filters="{
        teacher_id: teacher.id,
        with: 'teacher',
      }"
    >
      <template #default="{ items }">
        <ClientReviewList
          no-teacher
          style="top: -20px"
          :items="items"
        />
      </template>
    </UiDataLoader>
    <UiDataLoader
      v-else-if="selectedTab === 'services'"
      :key="JSON.stringify(serviceFilters)"
      url="teacher-services"
      :filters="{
        teacher_id: teacher.id,
        ...serviceFilters,
      }"
    >
      <template #filters>
        <div class="filters">
          <div class="filters-inputs">
            <div>
              <UiClearableSelect
                v-model="serviceFilters.year"
                label="Учебный год"
                :items="selectItems(YearLabel)"
                density="comfortable"
              />
            </div>
          </div>
        </div>
      </template>
      <template #default="{ items }">
        <TeacherServiceList :items="items" :teacher-id="teacher.id" />
      </template>
    </UiDataLoader>
    <BalanceList
      v-else
      :id="teacher.id"
      entity="teacher"
    />
  </div>
  <TeacherDialog
    ref="teacherDialog"
    @updated="onTeacherUpdated"
  />
</template>

<style lang="scss">
.teacher {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  h3 {
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    .v-btn {
      margin-left: 2px;
    }
    .v-icon {
      font-size: calc(var(--v-icon-size-multiplier) * 1.5rem) !important;
    }
  }
}
</style>
