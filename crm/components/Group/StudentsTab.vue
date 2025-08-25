<script setup lang="ts">
import { mdiSwapHorizontal } from '@mdi/js'

const { group } = defineProps<{ group: GroupResource }>()
const emit = defineEmits(['updated'])
const loading = ref(true)
const items = ref<ClientGroupResource[]>([])
const isEditable = useAuthStore().user?.entity_type === EntityTypeValue.user
const isSwampSelector = ref(false)

async function loadData() {
  isSwampSelector.value = false
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
  <SwampAddToGroupList
    v-if="isSwampSelector"
    :group="group"
    @updated="loadData(); emit('updated')"
    @back="isSwampSelector = false"
  />
  <UiIndexPage v-else :data="{ loading, noData: false }">
    <div class="table table--actions-on-hover">
      <div v-for="item in items" :key="item.id" :class="{ changed: item.draft_id }">
        <div style="width: 280px">
          <UiAvatar :item="item.client" :size="38" class="mr-4" />
          <UiPerson :item="item.client" />
        </div>
        <div>
          <TeethBar :items="item.teeth" :current="group.teeth" />
        </div>
        <div style="width: 240px" class="pl-6">
          <template v-if="item.draft_id">
            {{ item.is_removed ? 'удален' : 'добавлен' }} в
            <RouterLink :to="{ name: 'schedule-drafts-editor', query: { id: item.draft_id } }">
              проекте №{{ item.draft_id }}
            </RouterLink>
          </template>
        </div>
        <div v-if="isEditable && !item.draft_id" class="table-actionss">
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
      </div>
      <div v-if="isEditable" style="border-bottom: none; background: white">
        <UiIconLink @click="isSwampSelector = true">
          добавить ученика в текущую группу
        </UiIconLink>
      </div>
    </div>
  </UiIndexPage>
</template>
