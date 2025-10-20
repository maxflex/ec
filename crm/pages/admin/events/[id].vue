<script setup lang="ts">
import type { EventDialog } from '#components'
import type { EventParticipant, EventResource } from '~/components/Event'
import { mdiPlus } from '@mdi/js'

const eventDialog = ref<InstanceType<typeof EventDialog>>()
const { $addSseListener, $removeSseListener } = useNuxtApp()
const route = useRoute()

const item = ref<EventResource>()

async function loadData() {
  const { data } = await useHttp<EventResource>(
    `events/${route.params.id}`,
  )
  if (data.value) {
    item.value = data.value
  }
}

const maxRows = 3

const expanded = ref({
  clients: false,
  teachers: false,
})

const displayedParticipants = computed(() => {
  if (!item.value) {
    return {
      teachers: [],
      clients: [],
    }
  }
  const { teachers, clients } = item.value.participants!

  return {
    teachers: teachers.slice(0, expanded.value.teachers ? 999 : maxRows),
    clients: clients.slice(0, expanded.value.clients ? 999 : maxRows),
  }
})

function deleteParticipant(key: 'clients' | 'teachers', p: EventParticipant) {
  const index = item.value!.participants![key].findIndex(e => e.id === p.id)
  item.value!.participants![key].splice(index, 1)
  useHttp(`event-participants/${p.id}`, {
    method: 'delete',
  })
}

function updateParticipant(
  p: EventParticipant,
  body: Partial<EventParticipant>,
) {
  for (const key in body) {
    // @ts-ignore
    p[key] = body[key]
  }
  useHttp(`event-participants/${p.id}`, {
    method: 'put',
    body,
  })
}

nextTick(loadData)
</script>

<template>
  <v-fade-transition>
    <UiLoader v-if="item === undefined" />
    <div v-else class="show">
      <EventHeader :item="item" @edit="eventDialog?.edit" />

      <div v-for="tl in item.telegram_lists" :key="tl.id">
        <RouterLink :to="{ name: 'telegram-lists-id', params: { id: tl.id } }">
          рассылка от {{ formatDateTime(tl.created_at!) }}
        </RouterLink>
      </div>

      <div class="show__content">
        <div>
          <div>
          </div>
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

        <template v-for="(participants, key) in item.participants">
          <div v-if="participants.length" :key="key">
            <h2 class="mb-5">
              {{ key === 'clients' ? 'Клиенты' : 'Преподаватели' }}
            </h2>
            <v-table class="event-participants">
              <tbody>
                <tr v-for="p in displayedParticipants[key]" :key="p.id">
                  <td width="350" class="pl-5">
                    <UiPerson :item="p.entity" />
                  </td>
                  <td width="180" class="pr-0">
                    <v-menu>
                      <template #activator="{ props }">
                        <span v-bind="props" class="cursor-pointer unselectable">
                          <span
                            :class="{
                              'text-success': p.confirmation === 'confirmed',
                              'text-error': p.confirmation === 'rejected',
                              'text-gray': p.confirmation === 'pending',
                            }"
                          >
                            {{ EventParticipantConfirmationLabel[p.confirmation] }}
                            <v-icon
                              icon="$expand"
                              size="16"
                              class="vfn-1 ml-1"
                            />
                          </span>
                        </span>
                      </template>
                      <v-list>
                        <v-list-item
                          v-for="(label, confirmation) in EventParticipantConfirmationLabel"
                          :key="confirmation"
                          :class="{ 'text-gray': confirmation === 'pending' }"
                          @click="updateParticipant(p, { confirmation })"
                        >
                          {{ label }}
                        </v-list-item>
                      </v-list>
                    </v-menu>
                  </td>
                  <td width="180" class="pr-0">
                    <v-menu>
                      <template #activator="{ props }">
                        <span v-bind="props" class="cursor-pointer unselectable">
                          <span :class="p.is_visited ? 'text-success' : 'text-gray'">
                            {{ p.is_visited ? 'приходил' : 'не приходил' }}
                            <v-icon
                              icon="$expand"
                              size="16"
                              class="vfn-1 ml-1"
                            />
                          </span>
                        </span>
                      </template>
                      <v-list>
                        <v-list-item
                          v-for="({ value, title }) in yesNo('приходил', 'не приходил')"
                          :key="value"
                          :class="{ 'text-gray': value === 0 }"
                          @click="updateParticipant(p, { is_visited: !!value })"
                        >
                          {{ title }}
                        </v-list-item>
                      </v-list>
                    </v-menu>
                  </td>
                  <td class="pl-0">
                    <v-icon
                      :icon="mdiPlus"
                      color="gray"
                      class="event-participants__remove"
                      @click.stop="deleteParticipant(key, p)"
                    />
                  </td>
                </tr>
                <tr v-if="participants.length !== displayedParticipants[key].length" class="cursor-pointer" @click="expanded[key] = true">
                  <td>
                    <a>
                      показать всех {{ participants.length }}
                    </a>
                  </td>
                </tr>
              </tbody>
            </v-table>
          </div>
        </template>
      </div>
      <div class="mt-8 d-flex align-center ga-6">
        <v-btn
          color="primary"
          :to="{
            name: 'people-selector',
            query: {
              participants: item.id,
            },
          }"
        >
          редактировать участников
        </v-btn>
        <v-btn
          v-if="item?.participants?.clients.length || item?.participants?.teachers.length"
          color="primary"
          :to="{
            name: 'people-selector',
            query: {
              event_id: item.id,
            },
          }"
        >
          отправить сообщение
        </v-btn>
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
        opacity: 1;
      }
    }
  }
  &__remove {
    transform: rotate(45deg);
    transition: opacity ease-in-out 0.2s;
    &:hover {
      color: rgb(var(--v-theme-error)) !important;
    }
  }
}
</style>
