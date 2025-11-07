<script setup lang="ts">
import type { EventDialog } from '#components'
import type { EventParticipant, EventResource } from '~/components/Event'
import { mdiPlus } from '@mdi/js'

const eventDialog = ref<InstanceType<typeof EventDialog>>()
const route = useRoute()

const item = ref<EventResource>()

const filters = ref<{
  recepient: Recepient
  confirmation?: EventParticipantConfirmation
  is_visited?: number
}>({
  recepient: 'clients',
})

async function loadData() {
  const { data } = await useHttp<EventResource>(
    `events/${route.params.id}`,
  )
  if (data.value) {
    item.value = data.value
  }
}

const displayedParticipants = computed<EventParticipant[]>(() => {
  if (!item.value) {
    return []
  }

  return item.value.participants![filters.value.recepient].filter((e) => {
    if (filters.value.confirmation !== undefined && e.confirmation !== filters.value.confirmation) {
      return false
    }
    if (filters.value.is_visited !== undefined && e.is_visited !== !!filters.value.is_visited) {
      return false
    }
    return true
  })
})

function deleteParticipant(p: EventParticipant) {
  const index = item.value!.participants![filters.value.recepient].findIndex(e => e.id === p.id)
  item.value!.participants![filters.value.recepient].splice(index, 1)
  useHttp(`event-participants/${p.id}`, {
    method: 'delete',
  })
}

function updateParticipant(p: EventParticipant) {
  nextTick(() => {
    useHttp(`event-participants/${p.id}`, {
      method: 'put',
      body: p,
    })
  })
}

nextTick(loadData)
</script>

<template>
  <v-fade-transition>
    <UiLoader v-if="item === undefined" />
    <div v-else class="show">
      <EventHeader :item="item">
        <div class="mt-4">
          <div v-for="tl in item.telegram_lists" :key="tl.id">
            <RouterLink :to="{ name: 'telegram-lists-id', params: { id: tl.id } }">
              рассылка от {{ formatDateTime(tl.created_at!) }}
            </RouterLink>
          </div>
        </div>

        <div class="mt-6">
          <div class="d-flex ga-6 align-center">
            <UiAvatar :item="item.user" :size="80" />
            <div>
              <div>
                Ответственный
              </div>
              {{ formatName(item.user!) }}
            </div>
          </div>
        </div>
        <div class="mt-12">
          <v-btn color="primary" @click="eventDialog?.edit(item.id)">
            редактировать событие
          </v-btn>
        </div>
      </EventHeader>

      <div>
        <div class="filters event-participants__filters">
          <div class="filters__inputs">
            <v-select v-model="filters.recepient" density="comfortable" :items="selectItems(RecepientLabel)"></v-select>
            <UiClearableSelect
              v-model="filters.confirmation"
              density="comfortable"
              :items="selectItems(EventParticipantConfirmationLabel)"
              label="Подтверждение"
            />
            <UiClearableSelect
              v-model="filters.is_visited"
              density="comfortable"
              :items="yesNo('приходил', 'не приходил')"
              label="Посещение"
            />
            <div class="text-gray">
              всего: {{ displayedParticipants.length }}
            </div>
          </div>
          <div class="d-flex ga-4 align-center">
            <v-tooltip location="bottom">
              <template #activator="{ props }">
                <v-btn
                  v-bind="props"
                  :size="48"
                  icon="$edit"
                  color="primary"
                  :to="{
                    name: 'people-selector',
                    query: {
                      participants: item.id,
                    },
                  }"
                />
              </template>
              редактировать участников
            </v-tooltip>
            <v-tooltip v-if="item?.participants?.clients.length || item?.participants?.teachers.length" location="bottom">
              <template #activator="{ props }">
                <v-btn
                  v-bind="props"
                  icon="$send"
                  :size="48"
                  color="primary"
                  :to="{
                    name: 'people-selector',
                    query: {
                      event_id: item.id,
                    },
                  }"
                />
              </template>
              сообщение участникам
            </v-tooltip>
          </div>
        </div>
        <v-table class="event-participants table-padding">
          <tbody>
            <tr v-for="p in displayedParticipants" :key="p.id">
              <td width="320" class="pl-5">
                <UiPerson :item="p.entity" />
              </td>
              <td width="240">
                <ClientDirections v-if="p.directions" :items="p.directions" />
              </td>
              <td width="220" class="pr-0">
                <UiToggler
                  v-model="p.confirmation"
                  :class="{
                    'text-gray': p.confirmation === 'pending',
                    'text-success': p.confirmation === 'confirmed',
                    'text-error': p.confirmation === 'rejected',
                  }"
                  :items="selectItems(EventParticipantConfirmationLabel)"
                  @click="updateParticipant(p)"
                />
              </td>
              <td width="220" class="pr-0">
                <a
                  class="cursor-pointer unselectable"
                  :class="{
                    'text-gray': !p.is_visited,
                    'text-success': p.is_visited,
                  }"
                  @click="p.is_visited = !p.is_visited; updateParticipant(p)"
                >
                  {{ p.is_visited ? 'приходил' : 'не приходил' }}
                </a>
              </td>
              <td class="pl-0">
                <v-icon
                  :icon="mdiPlus"
                  color="gray"
                  class="event-participants__remove"
                />
              </td>
            </tr>
          </tbody>
        </v-table>
      </div>
    </div>
  </v-fade-transition>
  <EventDialog ref="eventDialog" @updated="loadData()" />
</template>

<style lang="scss">
.event-participants {
  tr {
    .event-participants__remove {
      opacity: 0;
    }
    &:hover {
      .event-participants__remove {
        opacity: 0.5;
      }
    }
  }
  &__remove {
    transform: rotate(45deg);
    transition: opacity ease-in-out 0.2s;
    &:hover {
      color: rgb(var(--v-theme-error)) !important;
      opacity: 1 !important;
    }
  }

  &__filters {
    margin-top: 40px;
    margin-left: -20px;
    width: calc(100% + 40px);
    min-width: calc(100% + 40px);
    // padding: 20px;
    border-top: 1px solid rgb(var(--v-theme-border));
  }

  .v-table__wrapper {
    margin-left: -20px;
    width: calc(100% + 40px);
    min-width: calc(100% + 40px);
  }
}
</style>
