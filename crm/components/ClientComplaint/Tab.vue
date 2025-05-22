<script setup lang="ts">
import type { ClientComplaintDialog } from '#components'
import type { ClientComplaintListResource } from '.'
import { apiUrl } from '.'

const { clientId, teacherId } = defineProps<{
  clientId?: number
  teacherId?: number
}>()
const dialog = ref<InstanceType<typeof ClientComplaintDialog>>()
const { items, indexPageData, reloadData } = useIndex<ClientComplaintListResource>(apiUrl, ref({}), {
  staticFilters: {
    teacher_id: teacherId,
    client_id: clientId,
  },
})
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #buttons>
      <v-btn
        color="primary"
        @click="dialog?.create({
          client_id: clientId!,
        })"
      >
        добавить жалобу
      </v-btn>
    </template>
    <ClientComplaintList :items="items" @edit="dialog?.edit" />
  </UiIndexPage>
  <ClientComplaintDialog ref="dialog" v-model="items" @updated="reloadData" />
</template>
