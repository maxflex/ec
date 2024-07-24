<script setup lang="ts">
import { mdiSwapHorizontal } from '@mdi/js'
import type { GroupAddStudentDialog, GroupSelectorDialog } from '#build/components'

const { group, contracts } = defineProps<{
  group: GroupResource
  contracts: ContractResource[]
}>()
const emit = defineEmits(['updated'])
const groupSelectorDialog = ref<InstanceType<typeof GroupSelectorDialog>>()
const groupAddStudentDialog = ref<InstanceType<typeof GroupAddStudentDialog>>()

async function removeFromGroupAndUpdate(c: ContractResource) {
  await removeFromGroup(c)
  emit('updated')
}

async function removeFromGroup(c: ContractResource) {
  await useHttp(`groups/remove-contract`, {
    method: 'post',
    params: {
      group_id: group.id,
      contract_id: c.id,
    },
  })
}

async function onGroupSelected(g: GroupListResource, c?: ContractResource) {
  await removeFromGroup(c!)
  await useHttp(`groups/add-client`, {
    method: 'post',
    params: {
      group_id: g.id,
      client_id: c!.client_id,
    },
  })
  emit('updated')
}
</script>

<template>
  <div
    class="table table--actions-on-hover"
  >
    <div
      v-for="contract in contracts"
      :key="contract.id"
    >
      <div style="width: 300px">
        <NuxtLink
          :to="{
            name: 'clients-id',
            params: { id: contract.client!.id },
          }"
        >
          {{ formatName(contract.client!) }}
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
            <v-list-item @click="groupSelectorDialog?.open(group.program!, contract)">
              <template #prepend>
                <v-icon :icon="mdiSwapHorizontal" />
              </template>
              переместить в группу
            </v-list-item>
            <v-list-item @click="removeFromGroupAndUpdate(contract)">
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
  <GroupAddStudentDialog ref="groupAddStudentDialog" :group="group" @updated="emit('updated')" />
</template>
