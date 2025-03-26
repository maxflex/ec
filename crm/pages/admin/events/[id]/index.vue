<script setup lang="ts">
import type { EventDialog } from '#components'
import { mdiAccountGroup, mdiCheckAll, mdiPlus } from '@mdi/js'

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

function setParticipantConfirmation(
  p: EventParticipant,
  confirmation: EventParticipantConfirmation,
) {
  p.confirmation = confirmation
  useHttp(`event-participants/${p.id}`, {
    method: 'put',
    body: { confirmation },
  })
}

function deleteParticipant(key: 'clients' | 'teachers', p: EventParticipant) {
  const index = item.value!.participants![key].findIndex(e => e.id === p.id)
  item.value!.participants![key].splice(index, 1)
  useHttp(`event-participants/${p.id}`, {
    method: 'delete',
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
        <div>
          <v-btn
            variant="plain"
            icon="$edit"
            :size="48"
            @click="eventDialog?.edit(item.id)"
          />
        </div>
      </div>
      <div class="show__content">
        <div>
          <div>
            Создал
          </div>
          <div>
            {{ formatName(item.user!) }}
            {{ formatDateTime(item.created_at!) }}
          </div>
        </div>
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
        <div>
          <div>Видимость</div>
          <div>
            {{ item.is_private ? 'конфиденциальное' : 'публичное' }}
          </div>
        </div>
        <div>
          <div>Рассылки</div>
          <div v-for="tl in item.telegram_lists" :key="tl.id">
            <RouterLink :to="{ name: 'telegram-lists-id', params: { id: tl.id } }">
              рассылка от {{ formatDateTime(tl.created_at!) }}
            </RouterLink>
          </div>
        </div>
        <div class="mb-8">
          <v-btn color="primary" :width="300" :to="{ name: 'events-id-participants', params: { id: item.id } }">
            добавить участников
          </v-btn>
        </div>
        <template v-for="(participants, key) in item.participants">
          <div v-if="participants.length" :key="key">
            <div class="mb-1">
              {{ key === 'clients' ? 'Клиенты' : 'Преподаватели' }}
            </div>
            <v-table class="event-participants">
              <tbody>
                <tr v-for="p in participants" :key="p.id">
                  <td width="400" class="pl-5">
                    <UiPerson :item="p.entity" />
                  </td>
                  <td width="230" class="pr-0">
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
                  <td class="pl-0">
                    <v-icon
                      :icon="mdiPlus"
                      color="gray"
                      class="event-participants__remove"
                      @click.stop="deleteParticipant(key, p)"
                    />
                  </td>
                </tr>
              </tbody>
            </v-table>
          </div>
        </template>
      </div>
      <v-btn
        v-if="item?.participants?.clients.length || item?.participants?.teachers.length"
        class="mt-8"
        color="primary"
        :width="300"
        :to="{
          name: 'people-selector-send',
          query: {
            event_id: item.id,
          },
        }"
      >
        отправить сообщение
      </v-btn>
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
