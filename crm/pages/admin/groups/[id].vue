<script setup lang="ts">
import type { GroupDialog } from '#build/components'

const tabs = {
  schedule: 'расписание',
  visits: 'посещаемость',
  students: 'ученики',
  acts: 'акты',
} as const

const selectedTab = ref<keyof typeof tabs>('schedule')

const route = useRoute()
const group = ref<GroupResource>()
const groupDialog = ref<InstanceType<typeof GroupDialog>>()

async function loadData() {
  const { data } = await useHttp(`groups/${route.params.id}`)
  group.value = data.value as GroupResource
}

function onGroupDeleted() {
  navigateTo('/groups')
}

nextTick(loadData)
</script>

<template>
  <template v-if="group">
    <div class="panel">
      <div class="panel-info">
        <div>
          <div>учебный год</div>
          <div v-if="group.year">
            {{ YearLabel[group.year] }}
          </div>
        </div>
        <div>
          <div>программа</div>
          <div v-if="group.program">
            {{ ProgramLabel[group.program] }}
          </div>
        </div>
        <div>
          <div>преподаватели</div>
          <div v-for="t in group.teachers" :key="t.id">
            <RouterLink :to="{ name: 'teachers-id', params: { id: t.id } }">
              {{ formatNameInitials(t) }}
            </RouterLink>
          </div>
        </div>
        <div>
          <div>
            расписание
          </div>
          <TeethAsText :items="group.teeth!" />
        </div>
        <div>
          <div>
            занятий
          </div>
          <div>
            {{ group.lessons_count }}
            <span v-if="group.lessons_free_count" class="text-deepOrange">
              + {{ group.lessons_free_count }}
            </span>
          </div>
        </div>
        <div class="panel-actions">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="groupDialog?.edit(group!)"
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
    <Schedule
      v-if="selectedTab === 'schedule'"
      :group-id="group.id"
      :year="group.year"
      :program="group.program"
    />
    <GroupVisitsTab v-else-if="selectedTab === 'visits'" :id="group.id" />
    <GroupActTab v-else-if="selectedTab === 'acts'" :id="group.id" />
    <GroupStudentsTab v-else :group="group" />
  </template>
  <GroupDialog
    ref="groupDialog"
    @updated="g => (group = g)"
    @deleted="onGroupDeleted"
  />
</template>
