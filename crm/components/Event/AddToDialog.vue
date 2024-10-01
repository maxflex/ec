<script setup lang="ts">
import { mdiAccountGroup } from '@mdi/js'
import { clone } from 'rambda'

const router = useRouter()
const { dialog, width } = useDialog('medium')
const loading = ref(true)
const events = ref<EventListResource[]>([])
const selected = ref<SelectedPeople>({
  clients: [],
  teachers: [],
})

// const selectedTotal = computed(() => selected.value.clients.length + selected.value.teachers.length)

async function open(sp: SelectedPeople, year: Year) {
  dialog.value = true
  loading.value = true
  selected.value = clone(sp)
  const { data } = await useHttp<ApiResponse<EventListResource[]>>(
      `common/events`,
      { params: { year } },
  )
  if (data.value) {
    events.value = data.value.data
    console.log(data.value.data)
  }
  loading.value = false
}

async function select(e: EventListResource) {
  if (!confirm(`Установить участников для события ${e.name}?`)) {
    console.log(e)
  }
  await useHttp(`event-participants`, {
    method: 'post',
    body: {
      id: e.id,
      selected: selected.value,
    },
  })
  await router.push({ name: 'events-id', params: { id: e.id } })
  dialog.value = false
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <UiLoader v-if="loading" />
      <div v-else class="dialog-body pt-0">
        <div class="table table--hover table--padding">
          <div v-for="e in events" :key="e.id" class="event-add-to" @click="select(e)">
            <div>
              <div class="font-weight-bold">
                {{ e.name }}
              </div>
              <div
                v-if="e.description" class="text-truncate"
                :style="{ width: `${width - 60}px` }"
              >
                {{ e.description }}
              </div>
              <div class="d-flex">
                <div style="width: 250px" class="text-gray">
                  {{ formatDate(e.date) }}
                  {{ formatTime(e.time!) }}
                  <template v-if="e.time_end">
                    – {{ e.time_end }}
                  </template>
                </div>
                <div style="width: 120px">
                  <v-icon :icon="mdiAccountGroup" class="mr-2 vfn-1" />
                  {{ e.participants_count }}
                </div>
                <div>
                  <EventStatus :item="e" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.event-add-to {
  & > div {
    display: flex;
    flex-direction: column;
    gap: 12px;
    cursor: pointer;
  }
}
</style>
