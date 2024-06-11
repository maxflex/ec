<script setup lang="ts">
import type { TeacherDialog } from '#build/components'

const route = useRoute()
const teacher = ref<TeacherResource>()
const teacherDialog = ref<InstanceType<typeof TeacherDialog>>()

const tabs = {
  groups: 'группы',
  payments: 'платежи',
  balance: 'баланс',
} as const
const selectedTab = ref<keyof typeof tabs>('groups')

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
      v-if="selectedTab ==='groups'"
      url="groups"
      :filters="{ teacher_id: teacher.id }"
    >
      <template #default="{ items }">
        <div class="table table--padding">
          <GroupList
            :items="items"
          />
        </div>
      </template>
    </UiDataLoader>
    <UiDataLoader
      v-else-if="selectedTab === 'payments'"
      url="teacher-payments"
      :filters="{ teacher_id: teacher.id }"
    >
      <template #default="{ items }">
        <TeacherPaymentList
          :items="items"
          :teacher-id="teacher.id"
        />
      </template>
    </UiDataLoader>
    <TeacherBalance
      v-else
      :teacher="teacher"
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
