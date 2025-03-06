<script setup lang="ts">
import { clone } from 'rambda'

const router = useRouter()
const { dialog, width } = useDialog('default')

const modelDefaults: TelegramListResource = {
  id: newId(),
  send_to: 'studentsAndParents',
  is_confirmable: false,
  text: '',
  status: 'scheduled',
  recipients: {
    clients: [],
    teachers: [],
  },
}
const loading = ref(true)
const saving = ref(false)

const item = ref<TelegramListResource>(modelDefaults)
const event = shallowRef<EventResource>()

const scheduledAt = reactive({
  date: '',
  time: '',
})
const timeMask = { mask: '##:##' }

const isScheduled = computed(() => scheduledAt.date && scheduledAt.time.length === 5)

const selected = ref<SelectedPeople>({
  clients: [],
  teachers: [],
})

const selectedTotal = computed(() => selected.value.clients.length + selected.value.teachers.length)

async function open(sp: SelectedPeople, e: EventResource | undefined = undefined) {
  loading.value = true
  dialog.value = true
  selected.value = clone(sp)
  event.value = e
  const { data } = await useHttp<PeopleResource>(
    `telegram-lists/load-people`,
    {
      method: 'post',
      body: selected.value,
    },
  )
  if (data.value) {
    item.value = {
      ...modelDefaults,
      recipients: data.value,
    }
  }
  loading.value = false
}

function select(key: 'clients' | 'teachers', p: PersonResource) {
  const index = selected.value[key].findIndex(id => id === p.id)
  index === -1
    ? selected.value[key].push(p.id)
    : selected.value[key].splice(index, 1)
}

async function save() {
  saving.value = true
  if (isScheduled.value) {
    item.value.scheduled_at = [scheduledAt.date, scheduledAt.time].join(' ')
  }
  if (event.value) {
    item.value.event_id = event.value.id
  }
  const { data } = await useHttp<TelegramListResource>(`telegram-lists`, {
    method: 'post',
    body: {
      ...item.value,
      recipients: selected.value,
    },
  })
  dialog.value = false
  saving.value = false
  if (data.value) {
    await router.push({
      name: 'telegram-lists-id',
      params: { id: data.value.id },
    })
  }
}

watch(item, () => {
  [scheduledAt.date, scheduledAt.time] = item.value.scheduled_at
    ? item.value.scheduled_at.split(' ')
    : ['', '']
})

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <UiLoader v-if="loading" />
      <div v-else class="dialog-body dialog-body--bottom">
        <div v-if="event">
          <v-select label="Событие" :model-value="event.name" disabled />
          <v-checkbox
            v-model="item.is_confirmable"
            label="Запросить подтверждение"
            color="secondary"
          />
        </div>

        <div>
          <v-select
            v-model="item.send_to"
            :items="selectItems(SendToLabel)"
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
          />
          <div>
            <v-text-field
              v-model="scheduledAt.time"
              v-maska:[timeMask]
              label="Время"
            />
          </div>
        </div>

        <template v-for="(people, key) in item.recipients">
          <div v-if="people.length" :key="key" class="dialog-section">
            <h4 class="mb-2">
              {{ key === 'clients' ? 'Клиенты' : 'Преподаватели' }}
            </h4>
            <v-table class="people-selector-table" hover>
              <tbody>
                <tr v-for="p in people" :key="p.id" @click="select(key, p)">
                  <td style="margin-left: -2px">
                    <UiCheckbox :value="selected[key].some(id => id === p.id)" />
                    <UiPerson :item="p" />
                  </td>
                </tr>
              </tbody>
            </v-table>
          </div>
        </template>
        <div>
          <v-btn
            block size="x-large" color="primary"
            :disabled="selectedTotal === 0"
            @click="save()"
          >
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
    </div>
  </v-dialog>
</template>
