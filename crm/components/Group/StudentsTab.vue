<script setup lang="ts">
import { mdiSwapHorizontal } from '@mdi/js'

const { group } = defineProps<{ group: GroupResource }>()

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
  <SwampSelector
    v-if="isSwampSelector"
    :group="group"
    @updated="loadData()"
    @back="isSwampSelector = false"
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
      </div>
      <div v-if="isEditable" style="border-bottom: none; background: white">
        <UiIconLink @click="isSwampSelector = true">
          добавить ученика в текущую группу
        </UiIconLink>
      </div>
    </div>
  </UiIndexPage>
</template>
