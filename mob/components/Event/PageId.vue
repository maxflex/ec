<script setup lang="ts">
import type { EventListResource, EventParticipantResource } from '~/components/Event'
import { UiLoader } from '#components'
import { mdiArrowLeftThin } from '@mdi/js'
import { format, getMonth } from 'date-fns'
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

const isConfirmationRequired = computed<boolean>(() => {
  const me = displayedParticipants.value.find(p => p.is_me)

  return !isRepresentative && !!me && me.confirmation === 'pending'
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

function formatDateLocal(date: string) {
  const month = getMonth(date) + 1
  const monthLabel = MonthLabelDative[month as Month]

  return format(date, `d ${monthLabel} yyyy`)
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
  <UiLoader v-if="!item || !participants" />
  <template v-else>
    <UiFilters>
      <RouterLink :to="{ name: 'events' }">
        <UiIconLink :icon="mdiArrowLeftThin" prepend>
          все события
        </UiIconLink>
      </RouterLink>
    </UiFilters>
    <UiContainer class="pt-0">
      <div class="event__img">
        <div v-if="item.file" :style="{ backgroundImage: `url(${item.file.url})` }" />
      </div>
    </UiContainer>
    <UiPageTitle class="pt-0">
      {{ item.name }}
    </UiPageTitle>
    <UiContainer class="pt-0">
      {{ item.description }}

      <div class="mt-3 font-weight-bold">
        {{ formatDateLocal(item.date) }}
        <span v-if="item.time">
          в {{ formatTime(item.time) }}
        </span>
      </div>
    </UiContainer>

    <div class="table event__table pb-12">
      <div class="font-weight-bold">
        Участники
      </div>
      <div v-for="p in displayedParticipants" :key="p.id" :style="p.is_me ? { backgroundColor: '#f6f8fb' } : {}">
        <div style="width: 160px; line-height: 16px;">
          <div>
            <UiPerson :item="p.entity" />
            <span v-if="p.is_me" class="text-gray nowrap">
              – это вы
            </span>
          </div>
          <div v-if="p.entity.entity_type === 'App\\Models\\Teacher'">
            {{ EntityTypeLabel[p.entity.entity_type] }}
          </div>
          <div v-if="p.directions">
            <ClientDirections :items="p.directions" :year="item?.year" />
          </div>
        </div>
        <div
          :class="p.confirmation === 'confirmed' ? 'text-success' : (p.confirmation === 'rejected' ? 'text-error' : 'text-gray')"
        >
          {{ EventParticipantConfirmationLabel[p.confirmation] }}
        </div>
        <div v-if="p.is_me && isConfirmationRequired" class="event__actions">
          <v-btn color="primary" size="default" @click="setConfirmation('confirmed')">
            подтвердить
          </v-btn>
          <v-btn color="error" size="default" @click="setConfirmation('rejected')">
            отказаться
          </v-btn>
        </div>
      </div>
    </div>
  </template>
</template>

<style lang="scss">
.event {
  &__img {
    & > div {
      width: 100%;
      border-radius: 8px;
      height: 120px;
      background-size: cover;
      background-position: center center;
    }
  }

  &__table {
    & > div:last-child {
      border-bottom: 1px solid rgb(var(--v-theme-border)) !important;
    }

    & > div {
      align-items: flex-start !important;
      padding: 12px 20px !important;
    }
  }

  &__actions {
    display: flex;
    // flex-direction: column;
    justify-content: space-between;
    align-items: flex-start;
    & > button {
      width: 134px;
    }
  }
}
</style>
