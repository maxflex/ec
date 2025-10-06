<script setup lang="ts">
import type { TeacherDialog } from '#build/components'
import type { TeacherResource } from '~/components/Teacher'

const route = useRoute()
const teacher = ref<TeacherResource>()
const teacherDialog = ref<InstanceType<typeof TeacherDialog>>()

const { tabs, selectedTab } = useTabs({
  groups: 'группы',
  schedule: 'расписание',
  events: 'события',
  payments: 'платежи',
  balance: 'баланс',
  reports: 'отчёты',
  clientComplaints: 'жалобы',
  clientReviews: 'отзывы',
  services: 'допуслуги',
  instructions: 'инструкции',
  stats: 'статистика',
  // stats2: 'статистика 2.0',
  headTeacherClients: 'классрук',
  headTeacherReports: 'отчёты кр',
})

type Tab = keyof typeof tabs

// вкладка "отчёты КР" только у is_head_teacher
const availableTabs = computed<Tab[]>(() => {
  const allTabs = Object.keys(tabs) as Tab[]
  return teacher.value?.is_head_teacher
    ? allTabs
    : allTabs.filter(t => !t.startsWith('headTeacher'))
})

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
            <div v-if="teacher.phones" class="mt-5">
              <PhoneList :items="teacher.phones" />
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
          <CommentBtn
            :entity-id="teacher.id"
            :entity-type="EntityTypeValue.teacher"
          />
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="teacherDialog?.edit(teacher!.id)"
          />
        </div>
      </div>
      <div v-if="teacher.schedule" class="panel-schedule">
        <TeethBar :items="teacher.schedule" />
        <LessonCurrentLesson :item="teacher.current_lesson" :teacher-id="teacher.id" />
      </div>
      <UiTabs v-model="selectedTab" :items="tabs" :available="availableTabs" />
    </div>
    <TeacherGroupsTab v-if="selectedTab === 'groups'" :teacher-id="teacher.id" />
    <Schedule v-else-if="selectedTab === 'schedule'" :teacher-id="teacher.id" />
    <InstructionTab v-else-if="selectedTab === 'instructions'" :teacher-id="teacher.id" />
    <ReportTab v-else-if="selectedTab === 'reports'" :teacher-id="teacher.id" />
    <EventTab v-else-if="selectedTab === 'events'" :teacher-id="teacher.id" />
    <TeacherPaymentTab v-else-if="selectedTab === 'payments'" :teacher-id="teacher.id" />
    <TeacherServiceTab v-else-if="selectedTab === 'services'" :teacher-id="teacher.id" />
    <ClientComplaintTab v-else-if="selectedTab === 'clientComplaints'" :teacher-id="teacher.id" />
    <ClientReviewTab v-else-if="selectedTab === 'clientReviews'" :teacher-id="teacher.id" />
    <Balance v-else-if="selectedTab === 'balance'" :teacher-id="teacher.id" :split="teacher.is_split_balance" />
    <TeacherStatsCharts v-else-if="selectedTab === 'stats'" :teacher="teacher" />
    <!-- <TeacherStats2 v-else-if="selectedTab === 'stats2'" :teacher-id="teacher.id" /> -->
    <HeadTeacherReportTab v-else-if="selectedTab === 'headTeacherReports'" :teacher-id="teacher.id" />
    <HeadTeacherClientsTab v-else-if="selectedTab === 'headTeacherClients'" :teacher-id="teacher.id" />
  </template>

  <TeacherDialog ref="teacherDialog" @updated="onUpdated" />
</template>
