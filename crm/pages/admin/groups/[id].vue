<script setup lang="ts">
import type { GroupDialog } from '#build/components'

const tabs = {
  schedule: 'расписание',
  schedule2: 'расписание-2',
  students: 'ученики',
} as const

const selectedTab = ref<keyof typeof tabs>('schedule')

const route = useRoute()
const group = ref<GroupResource>()
const groupDialog = ref<InstanceType<typeof GroupDialog>>()

async function loadData() {
  const { data } = await useHttp(`groups/${route.params.id}`)
  group.value = data.value as GroupResource
}

async function removeFromGroup(c: ContractResource) {
  await useHttp(`groups/remove-contract`, {
    method: 'post',
    params: {
      group_id: group.value?.id,
      contract_id: c.id,
    },
  })
  loadData()
}

function onGroupDeleted() {
  navigateTo('/groups')
}

nextTick(loadData)
</script>

<template>
  <div
    v-if="group"
    class="group"
  >
    <div class="panel">
      <div class="panel-info">
        <div>
          <div>группа {{ group.id }}</div>
          <div>
            {{ group.is_archived ? 'заархивирована' : 'активна' }}
          </div>
        </div>
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
          <div>zoom</div>
          <div>
            {{ group.zoom?.id || 'не установлено' }}
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
    <div v-if="selectedTab === 'schedule2'">
      <ScheduleList :id="group.id!" entity="group" editable conductable />
    </div>
    <div v-else-if="selectedTab === 'schedule'">
      <LessonList :id="group.id" editable entity="group" />
    </div>
    <div
      v-else
      class="table table--actions-on-hover"
    >
      <div
        v-for="contract in group.contracts"
        :key="contract.id"
      >
        <div style="width: 300px">
          <NuxtLink
            :to="{
              name: 'clients-id',
              params: { id: contract.client.id },
            }"
          >
            {{ formatName(contract.client) }}
          </NuxtLink>
        </div>
        <div class="text-left table-actions">
          <v-btn
            icon="$close"
            variant="plain"
            color="red"
            :size="48"
            :ripple="false"
            @click="removeFromGroup(contract)"
          />
        </div>
      </div>
    </div>
  </div>
  <GroupDialog
    ref="groupDialog"
    @updated="g => (group = g)"
    @deleted="onGroupDeleted"
  />
</template>
