<script setup lang="ts">
import type { ClientReviewDialog } from '#components'
import type { ClientReviewListResource } from '.'
import { apiUrl } from '.'

const { clientId, teacherId } = defineProps<{
  clientId?: number
  teacherId?: number
}>()
const dialog = ref<InstanceType<typeof ClientReviewDialog>>()
const { items, indexPageData, reloadData } = useIndex<ClientReviewListResource>(apiUrl, ref({}), {
  staticFilters: {
    teacher_id: teacherId,
    client_id: clientId,
  },
})
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template v-if="clientId" #buttons>
      <v-btn
        color="primary"
        @click="dialog?.create({
          client_id: clientId,
        })"
      >
        добавить отзыв
      </v-btn>
    </template>
    <ClientReviewList :items="items" @edit="dialog?.edit" />
  </UiIndexPage>
  <ClientReviewDialog ref="dialog" v-model="items" @updated="reloadData" />
</template>
