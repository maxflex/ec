<script setup lang="ts">
import { mdiShuffleVariant } from '@mdi/js'
import type { GroupAddStudentDialog, GroupSelectorDialog } from '#build/components'

const { group } = defineProps<{ group: GroupResource }>()

const tab = ref<'SwampSelector' | 'StudentsTab'>('StudentsTab')
const loading = ref(true)
const items = ref<ClientGroupResource[]>([])
const groupSelectorDialog = ref<InstanceType<typeof GroupSelectorDialog>>()
const groupAddStudentDialog = ref<InstanceType<typeof GroupAddStudentDialog>>()
const isEditable = useAuthStore().user?.entity_type === EntityTypeValue.user

// удалить из группы
async function removeFromGroup(gc: ClientGroupResource) {
  const index = items.value.findIndex(i => i.id === gc.id)
  if (index === -1) {
    return
  }
  items.value.splice(index, 1)
  await useHttp(`client-groups/${gc.id}`, {
    method: 'delete',
  })
}

// переместить в другую группу
async function moveToAnotherGroup(g: GroupListResource, contractId: number) {
  await removeFromGroup(
    items.value.find(i => i.contract_id === contractId)!,
  )
  await useHttp(`client-groups`, {
    method: 'post',
    params: {
      group_id: g.id,
      contract_id: contractId,
    },
  })
}

async function loadData() {
  tab.value = 'StudentsTab'
  loading.value = true
  const { data } = await useHttp<ApiResponse<ClientGroupResource>>(
    `client-groups`,
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
  <v-fade-transition>
    <SwampSelector
      v-if="tab === 'SwampSelector'"
      :group="group"
      @back="tab = 'StudentsTab'"
      @selected="loadData()"
    />
    <UiIndexPage v-else :data="{ loading, noData: false }">
      <div class="table table--actions-on-hover">
        <div v-for="item in items" :key="item.id">
          <div style="width: 280px">
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
          <div>
            <TeethBar :items="item.teeth" :current="group.teeth!" />
          </div>
          <div v-if="isEditable" class="text-left table-actions">
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
                    <v-icon :icon="mdiShuffleVariant" />
                  </template>
                  переместить в другую группу
                </v-list-item>
                <v-list-item @click="removeFromGroup(item)">
                  <template #prepend>
                    <v-icon icon="$delete" />
                  </template>
                  удалить из этой группы
                </v-list-item>
              </v-list>
            </v-menu>
          </div>
        </div>
        <div v-if="isEditable" style="border-bottom: none;">
          <UiIconLink @click="tab = 'SwampSelector'">
            добавить в текущую группу
          </UiIconLink>
        </div>
      </div>
    </UiIndexPage>
  </v-fade-transition>

  <GroupSelectorDialog
    ref="groupSelectorDialog"
    @select="moveToAnotherGroup"
  />
  <GroupAddStudentDialog
    ref="groupAddStudentDialog"
    :group="group"
    @added="loadData"
  />
</template>
