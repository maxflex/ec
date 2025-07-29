<script setup lang="ts">
import { mdiAccountArrowRightOutline, mdiSwapHorizontal } from '@mdi/js'

const { group } = defineProps<{ group: GroupResource }>()

const tab = ref<'SwampSelector' | 'GroupSelector' | 'StudentsTab'>('StudentsTab')
const loading = ref(true)
const items = ref<ClientGroupResource[]>([])
const isEditable = useAuthStore().user?.entity_type === EntityTypeValue.user

// contract_version_program_id ученика, которого переносим в другую группу
const moveCvpId = ref<number>()

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

async function groupSelectorTab(cvpId: number) {
  moveCvpId.value = cvpId
  tab.value = 'GroupSelector'
}

// переместить в другую группу
function moveToAnotherGroup(g: GroupListResource) {
  tab.value = 'StudentsTab'
  removeFromGroup(
    items.value.find(i => i.contract_version_program_id === moveCvpId.value)!,
  )
  useHttp(`client-groups`, {
    method: 'post',
    params: {
      group_id: g.id,
      contract_version_program_id: moveCvpId.value,
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
  <SwampSelector
    v-if="tab === 'SwampSelector'"
    :group="group"
    @back="tab = 'StudentsTab'"
    @selected="loadData()"
  />
  <GroupSelector
    v-else-if="tab === 'GroupSelector'"
    :group="group"
    @back="tab = 'StudentsTab'"
    @selected="moveToAnotherGroup"
  />
  <UiIndexPage v-else :data="{ loading, noData: false }">
    <div class="table table--hover table--actions-on-hover">
      <div v-for="item in items" :key="item.id">
        <div style="width: 280px">
          <UiAvatar :item="item.client" :size="38" class="mr-4" />
          <UiPerson :item="item.client" />
        </div>
        <div>
          <TeethBar :items="item.teeth" :current="group.teeth!" />
        </div>
        <div v-if="isEditable" class="table-actionss">
          <v-btn
            target="_blank"
            :icon="mdiSwapHorizontal"
            :size="48"
            color="gray"
            variant="text"
            :to="{
              name: 'schedule-drafts-editor',
              query: {
                client_id: item.client.id,
                year: group.year,
              },
            }"
          />
        </div>
        <!-- <div v-if="isEditable" class="text-left table-actions">

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
              <v-list-item @click="groupSelectorTab(item.contract_version_program_id)">
                <template #prepend>
                  <v-icon :icon="mdiAccountArrowRightOutline" />
                </template>
                переместить в другую группу
              </v-list-item>
              <v-list-item class="text-error" @click="removeFromGroup(item)">
                <template #prepend>
                  <v-icon icon="$delete" />
                </template>
                удалить из текущей группы
              </v-list-item>
            </v-list>
          </v-menu>
        </div> -->
      </div>
      <div v-if="isEditable" style="border-bottom: none; background: white">
        <UiIconLink @click="tab = 'SwampSelector'">
          добавить ученика в текущую группу
        </UiIconLink>
      </div>
    </div>
  </UiIndexPage>
</template>
