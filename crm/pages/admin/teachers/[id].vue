<script setup lang="ts">
import type { TeacherDialog } from '#build/components'
import type { Teacher } from '~/utils/models'

const route = useRoute()
const teacher = ref<Teacher>()
const teacherDialog = ref<InstanceType<typeof TeacherDialog>>()

const tabs = {
  payments: 'платежи',
} as const
const selectedTab = ref<keyof typeof tabs>('payments')

async function loadData() {
  const { data } = await useHttp<Teacher>(`teachers/${route.params.id}`)
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
            @click="teacherDialog?.edit(teacher)"
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
    <div
      v-if="selectedTab === 'payments'"
      class="table"
    >
      <TeacherPaymentList
        :items="teacher.payments"
        create
      />
    </div>
  </div>
  <TeacherDialog
    ref="teacherDialog"
    @updated="onTeacherUpdated"
  />
</template>

<style lang="scss">
.teacher {
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
