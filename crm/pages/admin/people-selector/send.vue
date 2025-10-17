<script setup lang="ts">
import type { EventResource } from '~/components/Event'
import { cloneDeep } from 'lodash-es'

const modelDefaults: TelegramListResource = {
  id: newId(),
  send_to: ['students', 'representatives', 'teachers'],
  text: '',
  status: 'scheduled',
  recipients: {
    students: [],
    representatives: [],
    teachers: [],
  },
  result: {
    students: [],
    teachers: [],
    representatives: [],
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
  const { data } = await useHttp<TelegramListResource>(
    `telegram-lists`,
    {
      method: 'post',
      body: {
        ...item.value,
        recipients: selected.value,
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
    <div v-else class="show pt-4">
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
                  <div v-if="i <= item.recipients.students.length" class="control-lk__item">
                    <div class="control-lk__item-client" :class="{ 'send-to-table__hidden': !isSelected('students') }">
                      <div class="control-lk__name">
                        <UiPerson :item="item.recipients.students[i - 1]" />
                      </div>
                      <div class="control-lk__phones">
                        <div
                          v-for="phone in item.recipients.students[i - 1].phones"
                          :key="phone.id"
                          :class="{ 'text-secondary': !!phone.telegram_id }"
                        >
                          {{ formatPhone(phone.number) }}
                        </div>
                      </div>
                      <div class="control-lk__directions">
                        <ClientDirections :item="item.recipients.students[i - 1].directions" />
                      </div>
                    </div>
                    <div class="control-lk__item-representative" :class="{ 'send-to-table__hidden': !isSelected('representatives') }">
                      <div class="control-lk__name">
                        <UiPerson :item="item.recipients.representatives[i - 1]" />
                      </div>
                      <div class="control-lk__phones">
                        <div
                          v-for="phone in item.recipients.representatives[i - 1].phones"
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
                  <div v-if="i <= item.recipients.teachers.length" class="control-lk__item">
                    <div class="control-lk__item-client" :class="{ 'send-to-table__hidden': !isSelected('teachers') }">
                      <div class="control-lk__name">
                        <UiPerson :item="item.recipients.teachers[i - 1]" />
                      </div>
                      <div class="control-lk__phones">
                        <div
                          v-for="phone in item.recipients.teachers[i - 1].phones"
                          :key="phone.id"
                          :class="{ 'text-secondary': !!phone.telegram_id }"
                        >
                          {{ formatPhone(phone.number) }}
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
                <!-- <td v-for="(label, key) in SendToLabel" :key="key">
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
                </td> -->
              </tr>
            </tbody>
          </v-table>
        </div>
      </div>
      <div class="show__inputs mt-12">
        <div v-if="event">
          <v-select label="Событие" :model-value="event.name" disabled />
        </div>
        <div>
          <v-textarea
            v-model="item.text"
            rows="3"
            no-resize
            auto-grow
            label="Текст сообщения"
          />
          <a v-if="event" class="cursor-pointer date-input__today " @click="addConfirmation()">
            запросить подтверждение участия
          </a>
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
.page-people-selector-send {
  .show__content {
    gap: 50px !important;
  }
}
</style>
