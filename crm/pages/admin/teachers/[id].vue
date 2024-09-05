<script setup lang="ts">
import type { TeacherDialog } from '#build/components'

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
  instructions: 'инструкции',
} as const

const selectedTab = ref<keyof typeof tabs>('groups')

const groupFilters = ref<{ year: Year }>({
  year: currentAcademicYear(),
})
const reviewFilters = ref<{ type?: number }>({})

async function loadData() {
  const { data } = await useHttp<TeacherResource>(`teachers/${route.params.id}`)
  if (data.value) {
    teacher.value = data.value
  }
}

function onUpdated(t: TeacherResource) {
  teacher.value = t
}

nextTick(loadData)
</script>

<template>
  <template v-if="teacher">
    <div class="panel">
      <div class="panel-info">
        <div>
          <UiAvatar :item="teacher" />
        </div>
        <div>
          <div>преподаватель</div>
          <div>
            {{ formatFullName(teacher) }}
            <PhoneActions :items="teacher.phones" :person="teacher" />
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
        <UiFilters>
          <div>
            <v-select
              v-model="groupFilters.year"
              label="Учебный год"
              :items="selectItems(YearLabel)"
              density="comfortable"
            />
          </div>
        </UiFilters>
      </template>
      <template #default="{ items }">
        <div class="table table--padding">
          <GroupList
            :items="items"
          />
        </div>
      </template>
    </UiDataLoader>
    <Schedule
      v-else-if="selectedTab === 'schedule'"
      :teacher-id="teacher.id"
      show-teeth
    />
    <UiDataLoader
      v-else-if="selectedTab === 'reviews'"
      url="client-reviews"
      :filters="{
        teacher_id: teacher.id,
        with: 'teacher',
        ...reviewFilters,
      }"
    >
      <template #filters>
        <UiFilters>
          <div>
            <UiClearableSelect
              v-model="reviewFilters.type"
              label="Тип"
              :items="yesNo('созданные', 'требуется создание')"
              density="comfortable"
            />
          </div>
        </UiFilters>
      </template>
      <template #default="{ items }">
        <ClientReviewList
          no-teacher
          style="top: -20px"
          :items="items"
        />
      </template>
    </UiDataLoader>
    <InstructionTab v-else-if="selectedTab === 'instructions'" :teacher-id="teacher.id" />
    <ReportTab v-else-if="selectedTab === 'reports'" :teacher-id="teacher.id" />
    <TeacherPaymentTab v-else-if="selectedTab === 'payments'" :teacher-id="teacher.id" />
    <TeacherServiceTab v-else-if="selectedTab === 'services'" :teacher-id="teacher.id" />
    <Balance v-else :teacher-id="teacher.id" />
  </template>

  <TeacherDialog ref="teacherDialog" @updated="onUpdated" />
</template>
