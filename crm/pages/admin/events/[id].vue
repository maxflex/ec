<script setup lang="ts">
import type { EventDialog } from '#components'
import { mdiCheckAll } from '@mdi/js'

const eventDialog = ref<InstanceType<typeof EventDialog>>()
const { $addSseListener, $removeSseListener } = useNuxtApp()
const route = useRoute()

const item = ref<EventResource>()

async function loadData() {
  const { data } = await useHttp<EventResource>(
    `common/events/${route.params.id}`,
  )
  if (data.value) {
    item.value = data.value
  }
}

function setParticipantConfirmation(p: EventParticipant, confirmation: EventParticipantConfirmation) {
  p.confirmation = confirmation
  useHttp(`event-participants/${p.id}`, {
    method: 'put',
    body: { confirmation },
  })
}

$addSseListener('ParticipantConfirmationEvent', (data: any) => {
  console.log('ParticipantConfirmationEvent', data)
  loadData()
})

onUnmounted(() => $removeSseListener('ParticipantConfirmationEvent'))

nextTick(loadData)
</script>

<template>
  <v-fade-transition>
    <UiLoader v-if="item === undefined" />
    <div v-else class="show">
      <div class="show__title">
        <h2>
          {{ item.name }}
        </h2>
        <v-btn
          variant="plain"
          icon="$edit"
          :size="48"
          @click="eventDialog?.edit(item.id)"
        />
      </div>
      <div class="show__content">
        <div>
          <div>
            Описание
          </div>
          <div style="width: 900px">
            {{ item.description }}
          </div>
        </div>
        <div>
          <div>Дата и время</div>
          <div>
            {{ formatDate(item.date) }}
            <template v-if="item.time">
              {{ formatTime(item.time) }}
            </template>
          </div>
        </div>
        <div>
          <div>Тип</div>
          <div>
            {{ item.is_afterclass ? 'внеклассное' : 'учебное' }} событие
          </div>
        </div>
        <template v-for="(participants, key) in item.participants">
          <div v-if="participants.length" :key="key">
            <div class="mb-1">
              {{ key === 'clients' ? 'Клиенты' : 'Преподаватели' }}
            </div>
            <v-table>
              <tbody>
                <tr v-for="p in participants" :key="p.id">
                  <td style="width: 400px" class="pl-5">
                    <UiPerson :item="p.entity" />
                  </td>
                  <td>
                    <v-menu>
                      <template #activator="{ props }">
                        <span v-bind="props" class="cursor-pointer">
                          <span
                            :class="{
                              'text-success': p.confirmation === 'confirmed',
                              'text-error': p.confirmation === 'rejected',
                              'text-gray': p.confirmation === 'pending',
                            }"
                          >
                            <v-icon
                              :icon="p.confirmation === 'confirmed' ? mdiCheckAll : (p.confirmation === 'rejected' ? '$close' : '$complete')"
                              size="16"
                              class="vfn-1 mr-1"
                            />
                            {{ EventParticipantConfirmationLabel[p.confirmation] }}
                          </span>
                        </span>
                      </template>
                      <v-list>
                        <v-list-item
                          v-for="(label, confirmation) in EventParticipantConfirmationLabel"
                          :key="confirmation"
                          :class="{ 'text-gray': confirmation === 'pending' }"
                          @click="setParticipantConfirmation(p, confirmation)"
                        >
                          {{ label }}
                        </v-list-item>
                      </v-list>
                    </v-menu>
                  </td>
                </tr>
              </tbody>
            </v-table>
          </div>
        </template>
      </div>
      <v-btn
        v-if="item?.participants?.clients.length || item?.participants?.teachers.length"
        color="secondary"
        class="mt-8"
        :to="{
          name: 'people-selector-send',
          query: {
            event_id: item.id,
          },
        }"
      >
        сообщение участникам
        <template #append>
          <v-icon icon="$next" />
        </template>
      </v-btn>
    </div>
  </v-fade-transition>
  <EventDialog ref="eventDialog" @updated="loadData()" />
</template>
