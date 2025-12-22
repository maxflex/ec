<script setup lang="ts">
import type { EventListResource, EventParticipantResource } from '.'
import { UiLoader } from '#components'
import { orderBy } from 'lodash-es'

const route = useRoute()
const item = ref<EventListResource>()
const participants = ref<EventParticipantResource[]>()
const { id } = route.params
const { isRepresentative } = useAuthStore()

// всегда включает "самого себя"
const displayedParticipants = computed<EventParticipantResource[]>(() => {
  if (!participants.value) {
    return []
  }

  const result = participants.value.filter(e => e.confirmation === 'confirmed' || e.is_me)

  return orderBy(result, 'is_me', 'desc')
})

async function loadEvent() {
  const { data } = await useHttp<EventListResource>(`events/${id}`)
  item.value = data.value!
}

async function loadParticipants() {
  const { data } = await useHttp<ApiResponse<EventParticipantResource>>(
    `event-participants`,
    {
      params: {
        event_id: id,
      },
    },
  )
  participants.value = data.value!.data
}

function setConfirmation(confirmation: EventParticipantConfirmation) {
  const index = participants.value!.findIndex(p => p.is_me)
  participants.value![index].confirmation = confirmation
  useHttp(
    `event-participants/${participants.value![index].id}`,
    {
      method: 'PUT',
      body: {
        confirmation,
      },
    },
  )
}

nextTick(async () => {
  await loadEvent()
  await loadParticipants()
})
</script>

<template>
  <v-fade-transition>
    <UiLoader v-if="!item || !participants" />
  </v-fade-transition>
  <div v-if="item" class="show">
    <EventHeader :item="item" />
  </div>
  <Table v-if="participants">
    <TableRow>
      <TableCol class="font-weight-bold">
        Участники
      </TableCol>
    </TableRow>
    <TableRow v-for="p in displayedParticipants" :key="p.id" :background="p.is_me ? 'blue' : undefined">
      <TableCol :width="300">
        <UiPerson :item="p.entity" />
        <span v-if="p.is_me" class="text-gray">
          – это вы
        </span>
      </TableCol>
      <TableCol class="text-lowercase" :width="140">
        {{ EntityTypeLabel[p.entity.entity_type] }}
      </TableCol>
      <TableCol :width="180">
        <ClientDirections v-if="p.directions" :items="p.directions" :year="item?.year" />
      </TableCol>
      <TableCol
        :width="160"
        :class="p.confirmation === 'confirmed' ? 'text-success' : (p.confirmation === 'rejected' ? 'text-error' : 'text-gray')"
      >
        {{ EventParticipantConfirmationLabel[p.confirmation] }}
      </TableCol>
      <TableCol>
        <TableButtons v-if="p.is_me && p.confirmation === 'pending' && !isRepresentative">
          <v-btn color="primary" density="comfortable" :width="166" @click="setConfirmation('confirmed')">
            подтвердить
          </v-btn>
          <v-btn color="error" density="comfortable" :width="166" @click="setConfirmation('rejected')">
            отказаться
          </v-btn>
        </TableButtons>
      </TableCol>
    </TableRow>
  </Table>
</template>
