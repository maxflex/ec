<script setup lang="ts">
import { clone } from 'rambda'
import type { PersonSelectorDialog } from '#build/components'

const emit = defineEmits<{
  updated: [e: EventListResource]
  deleted: [e: EventResource]
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
  participants: [],
  is_afterclass: false,
}
const item = ref<EventResource>(modelDefaults)
const personSelectorDialog = ref<InstanceType<typeof PersonSelectorDialog>>()

function create(year: Year) {
  itemId.value = undefined
  item.value = clone(modelDefaults)
  item.value.year = year
  dialog.value = true
}

async function edit(e: EventListResource) {
  const { id } = e
  itemId.value = id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<EventResource>(`events/${id}`)
  if (data.value) {
    item.value = data.value
  }
  loading.value = false
}

async function save() {
  saving.value = true
  const method = itemId.value ? `put` : `post`
  const url = itemId.value ? `events/${itemId.value}` : `events`
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

function onParticipantsSelected(participants: PersonListResource[]) {
  item.value.participants = participants
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить событие?')) {
    return
  }
  deleting.value = true
  const { data, status } = await useHttp(`events/${item.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    deleting.value = false
  }
  else if (data.value) {
    emit('deleted', item.value)
    dialog.value = false
    setTimeout(() => deleting.value = false, 300)
  }
}

defineExpose({ create, edit })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <template v-if="itemId">
          Редактировать событие
        </template>
        <template v-else>
          Новое событие
        </template>
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          @click="save()"
        />
      </div>
      <UiLoaderr v-if="loading" />
      <div v-else class="dialog-body">
        <div>
          <v-select
            v-model="item.year"
            label="Учебный год"
            :items="selectItems(YearLabel)"
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
            label="Длительность"
            type="number"
            suffix="минут"
            hide-spin-buttons
          />
        </div>
        <div>
          <v-text-field v-model="item.name" label="Название" />
        </div>
        <div>
          <v-textarea v-model="item.description" label="Описание" />
        </div>
        <div>
          <v-checkbox
            v-model="item.is_afterclass"
            label="Внеучебное"
          />
        </div>

        <div>
          <a
            class="link-icon"
            @click="personSelectorDialog?.open()"
          >
            участники ({{ item.participants.length }})
            <v-icon
              :size="16"
              icon="$next"
            />
          </a>
        </div>
        <div
          v-if="itemId"
          class="dialog-bottom"
        >
          <span>
            событие создано
            {{ formatName(item.user!) }}
            {{ formatDateTime(item.created_at!) }}
          </span>
          <v-btn
            icon="$delete"
            :size="48"
            color="red"
            variant="plain"
            :loading="deleting"
            @click="destroy()"
          />
        </div>
      </div>
    </div>
  </v-dialog>
  <PersonSelectorDialog ref="personSelectorDialog" @selected="onParticipantsSelected" />
</template>
