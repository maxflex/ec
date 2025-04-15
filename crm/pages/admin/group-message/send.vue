<script setup lang="ts">
import { clone } from 'lodash-es'

const modelDefaults: TelegramListResource = {
  id: newId(),
  send_to: ['students', 'parents', 'teachers'],
  is_confirmable: false,
  text: '',
  status: 'scheduled',
  recipients: {
    clients: [],
    teachers: [],
  },
  result: {
    students: [],
    teachers: [],
    parents: [],
  },
}

const loading = ref(true)
const saving = ref(false)
const route = useRoute()
const router = useRouter()
const item = ref<TelegramListResource>(modelDefaults)
const event = shallowRef<EventResource>()
const selected = ref<RecipientIds>({
  clients: [],
  teachers: [],
})

const scheduledAt = reactive({
  date: '',
  time: '',
})
const timeMask = { mask: '##:##' }

const isScheduled = computed(() => scheduledAt.date && scheduledAt.time.length === 5)

const selectedTotal = computed(() => {
  const { clients, teachers } = selected.value
  return clients.length + teachers.length
})

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
  const { data } = await useHttp<TelegramListResource>(`telegram-lists`, {
    method: 'post',
    body: {
      ...item.value,
      recipients: selected.value,
    },
  })
  if (data.value) {
    await router.push({
      name: 'telegram-lists-id',
      params: { id: data.value.id },
    })
  }
}

function select(key: 'clients' | 'teachers', p: PersonResource) {
  const index = selected.value[key].findIndex(id => id === p.id)
  index === -1
    ? selected.value[key].push(p.id)
    : selected.value[key].splice(index, 1)
}

watch(item, () => {
  [scheduledAt.date, scheduledAt.time] = item.value.scheduled_at
    ? item.value.scheduled_at.split(' ')
    : ['', '']
})

nextTick(async () => {
  const eventId = route.query.event_id
  if (eventId) {
    const { data } = await useHttp<EventResource>(`events/${eventId}`)
    if (data.value) {
      const { clients, teachers } = data.value.participants!
      selected.value = {
        clients: clients.map(e => e.entity.id),
        teachers: teachers.map(e => e.entity.id),
      }
      event.value = data.value
    }
  }
  else {
    const selectedPeople = localStorage.getItem('selected-people')
    if (selectedPeople) {
      selected.value = JSON.parse(selectedPeople) as RecipientIds
    }
    else {
      await router.push({ name: 'people-selector' })
    }
  }
  const { data } = await useHttp<Recipients>(
    `telegram-lists/load-people`,
    {
      method: 'post',
      body: clone(selected.value),
    },
  )
  if (data.value) {
    item.value = {
      ...modelDefaults,
      recipients: data.value,
    }
  }
  loading.value = false
})
</script>

<template>
  <v-fade-transition>
    <UiLoader v-if="loading" />
    <div v-else class="show">
      <div class="show__content mt-0">
        <template v-for="(people, key) in item.recipients">
          <div v-if="people.length" :key="key">
            <h2 class="mb-5">
              {{ key === 'clients' ? 'Клиенты' : 'Преподаватели' }}
            </h2>
            <v-table hover>
              <tbody>
                <tr
                  v-for="p in people"
                  :key="p.id"
                  @click="select(key, p)"
                >
                  <td>
                    <div class="d-flex ga-3">
                      <UiCheckbox :value="selected[key].some(id => id === p.id)" />
                      <UiPerson :item="p" />
                    </div>
                  </td>
                </tr>
              </tbody>
            </v-table>
          </div>
        </template>
      </div>
      <div class="show__inputs mt-12">
        <div v-if="event">
          <v-select label="Событие" :model-value="event.name" disabled />
          <v-checkbox
            v-model="item.is_confirmable"
            label="Запросить подтверждение участия"
            color="secondary"
          />
        </div>
        <div>
          <v-select
            v-model="item.send_to"
            multiple
            :items="selectItems(SendToAltLabel)"
            label="Кому отправлять"
          />
        </div>
        <div>
          <v-textarea
            v-model="item.text"
            rows="3"
            no-resize
            auto-grow
            label="Текст сообщения"
          />
        </div>
        <div class="double-input">
          <UiDateInput
            v-model="scheduledAt.date"
            fullscreen
          />
          <div>
            <v-text-field
              v-model="scheduledAt.time"
              v-maska="timeMask"
              label="Время"
            />
          </div>
        </div>
        <v-btn size="x-large" color="primary" :loading="saving" :disabled="!selectedTotal" @click="save()">
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
  </v-fade-transition>
</template>

<style lang="scss">
.page-group-message-send {
  .show__content {
    gap: 50px !important;
  }
}
</style>
