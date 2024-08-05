<script lang="ts" setup>
import type { PersonSelectorDialog } from '#components'

const { width, dialog } = useDialog('medium')
const saving = ref(false)
const item = ref<EventWithParticipantsResource>()
const personSelectorDialog = ref<InstanceType<typeof PersonSelectorDialog>>()

function open(e: EventListResource) {
  dialog.value = true
  loadData(e)
}

async function loadData(e: EventListResource) {
  const { data } = await useHttp<EventWithParticipantsResource>(
      `event-participants/${e.id}`,
  )
  if (data.value) {
    item.value = data.value
  }
}

async function save() {
  saving.value = true
  await useHttp(`event-participants`, {
    method: 'post',
    body: item.value,
  })
  dialog.value = false
  setTimeout(() => saving.value = false)
}

function deleteParticipant(p: EventParticipant) {
  if (!item.value) {
    return
  }
  const index = item.value.participants.findIndex(e => e.id === p.id)
  if (index === -1) {
    return
  }
  item.value.participants.splice(index, 1)
}

function onParticipantsSelected(participants: PersonListResource[]) {
  if (!item.value) {
    return
  }
  for (const p of participants) {
    const exists = item.value.participants.some(e => e.entity_id === p.entity_id && e.entity_type === p.entity_type)
    if (exists) {
      return
    }
    const id = newId()
    item.value.participants.push({
      id,
      entity_id: p.entity_id,
      entity_type: p.entity_type,
      is_confirmed: false,
      entity: p,
    })
    itemUpdated('participant', id)
  }
  // item.value.participants = participants
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <span>
          Участники <span class="ml-1 text-gray">{{ item?.participants.length }}</span>
        </span>
        <div>
          <v-btn :size="48" icon="$plus" variant="text" @click="personSelectorDialog?.open()" />
          <v-btn :size="48" icon="$save" variant="text" @click="save()" />
        </div>
      </div>
      <div v-if="item" class="dialog-body pt-0">
        <table class="dialog-table participants-table">
          <tbody>
            <tr v-for="p in item.participants" :id="`participant-${p.id}`" :key="p.id">
              <td>
                <UiAvatar :item="p.entity" :size="48" class="mr-2" />
                {{ formatName(p.entity) }}
              </td>
              <td>
                <v-checkbox v-model="p.is_confirmed" label="подтверждено" />
              </td>
              <td>
                <v-btn
                  :ripple="false"
                  :size="48"
                  color="red"
                  icon="$close"
                  variant="text"
                  @click="deleteParticipant(p)"
                />
              </td>
            </tr>
            <tr>
              <td colspan="100" />
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </v-dialog>
  <PersonSelectorDialog ref="personSelectorDialog" @selected="onParticipantsSelected" />
</template>

<style lang="scss" scoped>
.participants-table {
  td {
    &:first-child {
      width: 350px;
      padding-top: 8px;
      padding-bottom: 8px;
    }

    &:nth-child(2) {
      padding: 0 16px;
    }

    &:last-child {
      width: 66px;
      padding-left: 8px;
    }
  }
}
</style>
