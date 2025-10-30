<script setup lang="ts">
import type { EventListResource } from '~/components/Event'
import { UiLoader } from '#components'

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
  <v-fade-transition>
    <UiLoader v-if="item === undefined" />
    <div v-else class="show">
      <EventHeader :item="item" />
      <div v-if="item.participant!.confirmation === 'pending'" class="d-flex mt-12 ga-4">
        <v-btn color="primary" size="x-large" :width="400" @click="setConfirmation('confirmed')">
          подтвердить участие
        </v-btn>
        <v-btn color="error" variant="text" size="x-large" :width="400" @click="setConfirmation('rejected')">
          отказаться от участия
        </v-btn>
      </div>
    </div>
  </v-fade-transition>
</template>
