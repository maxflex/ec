<script setup lang="ts">
import { mdiSwapHorizontal } from '@mdi/js'
import type { GroupAddStudentDialog, GroupSelectorDialog } from '#build/components'

const { group } = defineProps<{ group: GroupResource }>()
const loading = ref(false)
const items = ref<GroupContractResource[]>([])
const groupSelectorDialog = ref<InstanceType<typeof GroupSelectorDialog>>()
const groupAddStudentDialog = ref<InstanceType<typeof GroupAddStudentDialog>>()

async function destroy(gc: GroupContractResource) {
  const index = items.value.findIndex(i => i.id === gc.id)
  if (index === -1) {
    return
  }
  items.value.splice(index, 1)
  await useHttp(`group-contracts/${gc.id}`, {
    method: 'delete',
  })
}

async function onGroupSelected(g: GroupListResource, contractId: number) {
  await destroy(
    items.value.find(i => i.contract_id === contractId)!,
  )
  await useHttp(`group-contracts`, {
    method: 'post',
    params: {
      group_id: g.id,
      contract_id: contractId,
    },
  })
}

async function loadData() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<GroupContractResource[]>>(
    `group-contracts`,
    {
      params: {
        group_id: group.id,
      },
    },
  )
  if (data.value) {
    items.value = data.value.data
  }
  loading.value = false
}

nextTick(loadData)
</script>

<template>
  <div
    class="table table--actions-on-hover"
  >
    <div v-for="item in items" :key="item.id">
      <div style="width: 300px">
        <UiAvatar :item="item.client" :size="38" class="mr-4" />
        <NuxtLink
          class="vf-1"
          :to="{
            name: 'clients-id',
            params: { id: item.client.id },
          }"
        >
          {{ formatName(item.client) }}
        </NuxtLink>
      </div>
      <div class="text-left table-actions">
        <v-menu>
          <template #activator="{ props }">
            <v-btn
              v-bind="props"
              icon="$more"
              variant="plain"
              :size="48"
              :ripple="false"
            />
          </template>
          <v-list>
            <v-list-item @click="groupSelectorDialog?.open(group.program!, group.year, item.contract_id)">
              <template #prepend>
                <v-icon :icon="mdiSwapHorizontal" />
              </template>
              переместить в группу
            </v-list-item>
            <v-list-item @click="destroy(item)">
              <template #prepend>
                <v-icon icon="$close" />
              </template>
              удалить из группы
            </v-list-item>
          </v-list>
        </v-menu>
      </div>
    </div>
    <div style="border-bottom: none;">
      <a
        class="link-icon"
        @click="groupAddStudentDialog?.open()"
      >
        добавить в текущую группу
        <v-icon
          :size="16"
          icon="$next"
        />
      </a>
    </div>
  </div>
  <GroupSelectorDialog
    ref="groupSelectorDialog"
    @select="onGroupSelected"
  />
  <GroupAddStudentDialog ref="groupAddStudentDialog" :group="group" @updated="loadData" />
</template>
