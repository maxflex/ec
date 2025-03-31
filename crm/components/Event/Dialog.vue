<script lang="ts" setup>
import { clone } from 'rambda'

const emit = defineEmits<{
  updated: [e: EventListResource]
}>()
const { width, dialog } = useDialog('default')
const timeMask = { mask: '##:##' }
const deleting = ref(false)
const saving = ref(false)
const loading = ref(false)
const itemId = ref<number>()
const modelDefaults: EventResource = {
  id: newId(),
  year: currentAcademicYear(),
  date: today(),
  name: '',
  description: null,
  duration: null,
  is_afterclass: false,
  is_private: false,
}
const item = ref<EventResource>(modelDefaults)
const route = useRoute()
const router = useRouter()
const { showGlobalMessage } = useGlobalMessage()

function create(year: Year) {
  itemId.value = undefined
  item.value = clone(modelDefaults)
  item.value.year = year
  dialog.value = true
}

async function edit(id: number) {
  itemId.value = id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<EventResource>(`common/events/${id}`)
  if (data.value) {
    item.value = data.value
  }
  loading.value = false
}

async function save() {
  saving.value = true
  const method = itemId.value ? `put` : `post`
  const url = itemId.value ? `common/events/${itemId.value}` : `common/events`
  const { data } = await useHttp<EventListResource>(url, {
    method,
    body: item.value,
  })
  if (data.value) {
    emit('updated', data.value)
  }
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить событие?')) {
    return
  }
  deleting.value = true
  const { error } = await useHttp(
    `common/events/${item.value.id}`,
    {
      method: 'delete',
    },
  )
  if (error.value) {
    deleting.value = false
    return
  }
  dialog.value = false
  if (route.name === 'events-id') {
    showGlobalMessage('Событие удалено', 'success')
    await router.push({ name: 'events' })
  }
  setTimeout(() => deleting.value = false, 300)
}

defineExpose({ create, edit })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <div v-if="itemId">
          Редактировать событие
          <div class="dialog-subheader">
            <template v-if="item.user && item.created_at">
              {{ formatName(item.user) }}
              {{ formatDateTime(item.created_at) }}
            </template>
          </div>
        </div>
        <template v-else>
          Новое событие
        </template>
        <div>
          <v-btn
            v-if="itemId"
            :loading="deleting"
            :size="48"
            class="remove-btn"
            icon="$delete"
            variant="text"
            @click="destroy()"
          />
          <v-btn
            :size="48"
            icon="$save"
            variant="text"
            @click="save()"
          />
        </div>
      </div>
      <UiLoader v-if="loading" />
      <div v-else class="dialog-body">
        <div>
          <v-select
            v-model="item.year"
            :items="selectItems(YearLabel)"
            label="Учебный год"
          />
        </div>
        <div class="double-input">
          <UiDateInput :key="item.year" v-model="item.date" :year="item.year" />
          <div>
            <v-text-field
              v-model="item.time"
              v-maska:[timeMask]
              label="Время"
            />
          </div>
        </div>
        <div>
          <v-text-field
            v-model="item.duration"
            hide-spin-buttons
            label="Длительность"
            suffix="минут"
            type="number"
          />
        </div>
        <div>
          <v-text-field v-model="item.name" label="Название" />
        </div>
        <div>
          <v-textarea v-model="item.description" label="Описание" no-resize />
        </div>
        <div>
          <v-checkbox
            v-model="item.is_afterclass"
            label="Внеклассное"
          />
          <v-checkbox
            v-model="item.is_private"
            label="Конфиденциальное"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
