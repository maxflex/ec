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
  clientReviews: 'отзывы',
  services: 'допуслуги',
  instructions: 'инструкции',
} as const

const selectedTab = ref<keyof typeof tabs>('groups')

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
            <div v-if="teacher.phones">
              <PhoneList :items="teacher.phones" :person="teacher" />
            </div>
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
              entity_type: EntityTypeValue.teacher,
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
    <TeacherGroupsTab v-if="selectedTab === 'groups'" :teacher-id="teacher.id" />
    <Schedule
      v-else-if="selectedTab === 'schedule'"
      :teacher-id="teacher.id"
      show-teeth
    />
    <ClientReviewTab v-else-if="selectedTab === 'clientReviews'" :teacher-id="teacher.id" />
    <InstructionTab v-else-if="selectedTab === 'instructions'" :teacher-id="teacher.id" />
    <ReportTab v-else-if="selectedTab === 'reports'" :teacher-id="teacher.id" />
    <TeacherPaymentTab v-else-if="selectedTab === 'payments'" :teacher-id="teacher.id" />
    <TeacherServiceTab v-else-if="selectedTab === 'services'" :teacher-id="teacher.id" />
    <Balance v-else :teacher-id="teacher.id" />
  </template>

  <TeacherDialog ref="teacherDialog" @updated="onUpdated" />
</template>
