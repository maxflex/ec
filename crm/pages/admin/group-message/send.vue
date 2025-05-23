<script setup lang="ts">
import { cloneDeep } from 'lodash-es'

const modelDefaults: TelegramListResource = {
  id: newId(),
  send_to: ['students', 'parents', 'teachers'],
  is_confirmable: false,
  text: '',
  status: 'scheduled',
  recipients: {
    students: [],
    parents: [],
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

function select(key: SendTo) {
  const index = item.value.send_to.findIndex(e => e === key)
  index === -1
    ? item.value.send_to.push(key)
    : item.value.send_to.splice(index, 1)
}

function isSelected(key: SendTo): boolean {
  return item.value.send_to.includes(key)
}

const maxRows = computed<number>(() =>
  Math.max(...Object.values(item.value.recipients).map(e => e.length)),
)

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
      body: cloneDeep(selected.value),
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
        <div>
          <div></div>
          <v-table class="send-to-table">
            <thead>
              <tr>
                <th v-for="(label, key) in SendToLabel" :key="key">
                  <div class="cursor-pointer" @click="select(key)">
                    <UiCheckbox :value="isSelected(key)" />
                    <span>
                      {{ label }}
                    </span>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="i in maxRows" :key="i">
                <td v-for="(label, key) in SendToLabel" :key="key">
                  <div
                    v-if="item.recipients[key] && i <= item.recipients[key].length"
                    class="send-to-table__content"
                    :class="{ hidden: !isSelected(key) }"
                  >
                    <UiPerson :item="item.recipients[key][i - 1]" />
                    <div class="send-to-table__phones">
                      <div
                        v-for="phone in item.recipients[key][i - 1].phones"
                        :key="phone.id"
                        :class="{ 'text-secondary': !!phone.telegram_id }"
                      >
                        {{ formatPhone(phone.number) }}
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
          <v-checkbox
            v-model="item.is_confirmable"
            label="Запросить подтверждение участия"
            color="secondary"
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
  </v-fade-transition>
</template>

<style lang="scss">
.page-group-message-send {
  .show__content {
    gap: 50px !important;
  }
}
</style>
