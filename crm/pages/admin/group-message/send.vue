<script setup lang="ts">
import type { EventResource } from '~/components/Event'
import type { PeopleSelectorResource } from '~/components/PeopleSelector'
import type { TelegramListResource } from '~/components/TelegramList'
import { format, getMonth } from 'date-fns'
import { cloneDeep } from 'lodash-es'
import { modelDefaults } from '~/components/TelegramList'

const people = ref<PeopleSelectorResource[]>()
const route = useRoute()
const router = useRouter()
const eventId = route.query.event_id
const saving = ref(false)
const item = ref<TelegramListResource>(cloneDeep(modelDefaults))
const event = shallowRef<EventResource>()

const scheduledAt = reactive({
  date: '',
  time: '',
})
const timeMask = { mask: '##:##' }

const isScheduled = computed(() => scheduledAt.date && scheduledAt.time.length === 5)

const clients = computed<PeopleSelectorResource[]>(() => {
  if (!people.value) {
    return []
  }

  return people.value.filter(p => p.entity_type === EntityTypeValue.client)
})

const teachers = computed<PeopleSelectorResource[]>(() => {
  if (!people.value) {
    return []
  }

  return people.value.filter(p => p.entity_type === EntityTypeValue.teacher)
})

const maxRows = computed(() => Math.max(clients.value.length, teachers.value.length))

async function save() {
  saving.value = true
  if (isScheduled.value) {
    item.value.scheduled_at = [scheduledAt.date, scheduledAt.time].join(' ')
  }
  if (event.value) {
    item.value.event_id = event.value.id
  }
  else {
  // очищаем selected-people только если это свободная отправка
    localStorage.removeItem('selected-people')
  }
  const { data } = await useHttp<TelegramListResource>(
    `telegram-lists`,
    {
      method: 'POST',
      body: {
        ...cloneDeep(item.value),
        recipients: {
          clients: clients.value.map(e => e.id),
          teachers: teachers.value.map(e => e.id),
        },
      },
    },
  )
  if (data.value) {
    await router.push({
      name: 'telegram-lists-id',
      params: { id: data.value.id },
    })
  }
}

function select(key: SendTo) {
  const index = item.value.send_to.findIndex(e => e === key)
  index === -1
    ? item.value.send_to.push(key)
    : item.value.send_to.splice(index, 1)
}

function isSelected(key: SendTo): boolean {
  return item.value.send_to.includes(key)
}

function addConfirmation() {
  item.value.text += `\n<a>Подтвердить участие</a>`
}

function addDateTime() {
  if (!event.value) {
    return
  }

  const month = getMonth(event.value.date) + 1
  const monthLabel = MonthLabelDative[month as Month]
  const dateTime = format(event.value.date, `d ${monthLabel} yyyy`)

  item.value.text += `\nКогда: ${dateTime} в ${event.value.time ? formatTime(event.value.time) : ''}`
}

watch(item, () => {
  [scheduledAt.date, scheduledAt.time] = item.value.scheduled_at
    ? item.value.scheduled_at.split(' ')
    : ['', '']
})

async function unpackEvent() {
  const { data } = await useHttp<{
    event: EventResource
    people: PeopleSelectorResource[]
  }>(`people-selector/unpack-event/${eventId}`)

  people.value = data.value!.people
  event.value = data.value!.event
}

/**
 * Подгрузить людей из localStorage
 * (выбранных ранее на /group-message)
 */
async function unpackLocalStorage() {
  const selectedPeople = localStorage.getItem('selected-people')
  if (selectedPeople) {
    const { data } = await useHttp<PeopleSelectorResource[]>(
      `people-selector/unpack-localstorage`,
      {
        method: 'POST',
        body: {
          selected: JSON.parse(selectedPeople),
        },
      },
    )
    people.value = data.value!
  }
}

nextTick(async () => {
  eventId
    ? await unpackEvent()
    : await unpackLocalStorage()
})
</script>

<template>
  <v-fade-transition>
    <UiLoader v-if="!people" />
  </v-fade-transition>
  <div v-if="people && event" class="back-to-event">
    <RouterLink v-if="event" :to="{ name: 'events-id', params: { id: event.id } }">
      <UiIconLink prepend icon="$back" class="vf-1">
        вернуться в событие {{ event.name }}
      </UiIconLink>
    </RouterLink>
  </div>
  <div v-if="people" class="show pt-4">
    <div class="show__content mt-0">
      <div>
        <div></div>
        <v-table class="send-to-table">
          <thead>
            <tr>
              <th>
                <div class="d-flex flex-column pb-4">
                  <div class="cursor-pointer" @click="select('students')">
                    <UiCheckbox :value="isSelected('students')" />
                    <span>
                      {{ SendToLabel.students }}
                    </span>
                  </div>
                  <div class="cursor-pointer" @click="select('representatives')">
                    <UiCheckbox :value="isSelected('representatives')" />
                    <span>
                      {{ SendToLabel.representatives }}
                    </span>
                  </div>
                </div>
              </th>
              <th>
                <div class="cursor-pointer" @click="select('teachers')">
                  <UiCheckbox :value="isSelected('teachers')" />
                  <span>
                    {{ SendToLabel.teachers }}
                  </span>
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="i in maxRows" :key="i">
              <!-- students & representatives -->
              <td>
                <div v-if="i <= clients.length" class="control-lk__item">
                  <div class="control-lk__item-client" :class="{ 'send-to-table__hidden': !isSelected('students') }">
                    <div class="control-lk__name">
                      <UiPerson :item="clients[i - 1]" />
                    </div>
                    <div class="control-lk__phones">
                      <div
                        v-for="phone in clients[i - 1].phones"
                        :key="phone.id"
                        :class="{ 'text-secondary': !!phone.telegram_id }"
                      >
                        {{ formatPhone(phone.number) }}
                      </div>
                    </div>
                    <div class="control-lk__directions">
                      <ClientDirections :items="clients[i - 1].directions!" />
                    </div>
                  </div>
                  <div class="control-lk__item-representative" :class="{ 'send-to-table__hidden': !isSelected('representatives') }">
                    <div class="control-lk__name">
                      <UiPerson :item="clients[i - 1].representative!" />
                    </div>
                    <div class="control-lk__phones">
                      <div
                        v-for="phone in clients[i - 1].representative!.phones"
                        :key="phone.id"
                        :class="{ 'text-secondary': !!phone.telegram_id }"
                      >
                        {{ formatPhone(phone.number) }}
                      </div>
                    </div>
                  </div>
                </div>
              </td>

              <!-- teacher -->
              <td>
                <div v-if="i <= teachers.length" class="control-lk__item">
                  <div class="control-lk__item-client" :class="{ 'send-to-table__hidden': !isSelected('teachers') }">
                    <div class="control-lk__name">
                      <UiPerson :item="teachers[i - 1]" />
                    </div>
                    <div class="control-lk__phones">
                      <div
                        v-for="phone in teachers[i - 1].phones"
                        :key="phone.id"
                        :class="{ 'text-secondary': !!phone.telegram_id }"
                      >
                        {{ formatPhone(phone.number) }}
                      </div>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </v-table>
      </div>
    </div>
    <div class="show__inputs mt-12">
      <div v-if="event">
        <v-select label="Событие" :model-value="event.name" disabled />
      </div>
      <div style="position: relative;">
        <v-textarea
          v-model="item.text"
          rows="3"
          no-resize
          auto-grow
          label="Текст сообщения"
        />
        <div v-if="event" class="d-flex">
          <a class="cursor-pointer date-input__today " @click="addConfirmation()">
            добавить ссылку на событие
          </a>
          <a class="cursor-pointer date-input__today " @click="addDateTime()">
            добавить время события
          </a>
        </div>
        <TelegramMessagePreview :text="item.text" />
      </div>
      <div class="double-input">
        <UiDateInput v-model="scheduledAt.date" />
        <div>
          <v-text-field
            v-model="scheduledAt.time"
            v-maska="timeMask"
            label="Время"
          />
        </div>
      </div>
      <v-btn size="x-large" :color="item.send_to.length ? 'primary' : undefined" :loading="saving" :disabled="!item.send_to.length" @click="save()">
        отправить
        <span v-if="isScheduled" class="ml-1">
          {{ formatDate(scheduledAt.date) }} в {{ scheduledAt.time }}
        </span>
        <span v-else>
          сейчас
        </span>
      </v-btn>
    </div>
  </div>
</template>

<style lang="scss">
.page-group-message-send {
  $backToEventHeight: 36px;

  .back-to-event {
    position: sticky;
    top: 0;
    padding: 0 var(--padding);
    height: $backToEventHeight;
    min-height: $backToEventHeight;
    background-color: rgb(var(--v-theme-bg));
    font-size: 14px;
    border-bottom: 1px solid rgb(var(--v-theme-border));
    z-index: 1;
    display: flex;
    align-items: center;
    // justify-content: flex-end;
  }

  .show__content {
    gap: 50px !important;
  }
}
</style>
