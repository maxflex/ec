<script setup lang="ts">
import type { EventListResource } from '~/components/Event'
import { UiLoader } from '#components'
import { mdiArrowLeftThin } from '@mdi/js'

const route = useRoute()
const item = ref<EventListResource>()
const { id } = route.params

async function loadData() {
  const { data } = await useHttp<EventListResource>(`events/${id}`)
  item.value = data.value!
}

function setConfirmation(confirmation: EventParticipantConfirmation) {
  if (!item.value) {
    return
  }
  item.value.participant!.confirmation = confirmation
  useHttp(`events/${item.value.id}`, {
    method: 'PUT',
    body: {
      confirmation,
    },
  })
}

nextTick(loadData)
</script>

<template>
  <UiLoader v-if="!item" />
  <template v-else>
    <UiFilters>
      <RouterLink :to="{ name: 'events' }">
        <UiIconLink :icon="mdiArrowLeftThin" prepend>
          все события
        </UiIconLink>
      </RouterLink>
    </UiFilters>

    <EventItem v-if="item" :item="item" />

    <div v-if="item.participant!.confirmation === 'pending'" class="pa-4">
      <v-btn color="primary" block @click="setConfirmation('confirmed')">
        подтвердить участие
      </v-btn>
      <v-btn color="error" variant="text" block class="mt-6" @click="setConfirmation('rejected')">
        отказаться от участия
      </v-btn>
    </div>
  </template>
</template>
