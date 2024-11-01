<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  updated: [e: GroupActResource]
  deleted: [e: GroupActResource]
}>()

const modelDefaults: GroupActResource = {
  id: newId(),
  date: '',
  date_from: '',
  date_to: '',
}

const printOptions: PrintOption[] = [
  { id: 11, label: 'Печать ООО' },
  { id: 12, label: 'Печать ИП' },
]

const { dialog, width } = useDialog('default')
const item = ref<GroupActResource>(modelDefaults)
const saving = ref(false)
const deleting = ref(false)

function edit(groupAct: GroupActResource) {
  item.value = clone(groupAct)
  dialog.value = true
}

function create(groupId: number) {
  item.value = {
    ...modelDefaults,
    group_id: groupId,
  }
  dialog.value = true
}

async function save() {
  saving.value = true
  if (item.value.id > 0) {
    const { data } = await useHttp<GroupActResource>(`group-acts/${item.value.id}`, {
      method: 'put',
      body: { ...item.value },
    })
    emit('updated', data.value!)
  }
  else {
    const { data } = await useHttp<GroupActResource>(`group-acts`, {
      method: 'post',
      body: { ...item.value },
    })
    emit('updated', data.value!)
  }
  dialog.value = false
  saving.value = false
}

async function destroy() {
  if (!confirm(`Удалить акт №${item.value.id}?`)) {
    return
  }
  deleting.value = true
  await useHttp(`group-acts/${item.value.id}`, {
    method: 'delete',
  })
  dialog.value = false
  deleting.value = false
  emit('deleted', item.value)
}
defineExpose({ edit, create })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div v-if="item" class="dialog-wrapper">
      <div class="dialog-header">
        <div v-if="item.id > 0">
          Редактирование акта
          <div class="dialog-subheader">
            {{ formatName(item.user!) }}
            {{ formatDateTime(item.created_at!) }}
          </div>
        </div>
        <div v-else>
          Добавить акт
        </div>
        <div>
          <v-btn
            v-if="item.id > 0"
            :loading="deleting"
            :size="48"
            class="remove-btn"
            icon="$delete"
            variant="text"
            @click="destroy()"
          />
          <v-menu>
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                icon="$print"
                :size="48"
                variant="text"
              />
            </template>
            <v-list>
              <v-list-item
                v-for="p in printOptions" :key="p.id"
                @click="print(p, { contract_version_id: item.id })"
              >
                {{ p.label }}
              </v-list-item>
            </v-list>
          </v-menu>
          <v-btn
            :loading="saving"
            :size="48"
            icon="$save"
            variant="text"
            @click="save()"
          />
        </div>
      </div>
      <div class="dialog-body">
        <div class="double-input">
          <v-text-field v-model="item.lessons" label="Количество занятий" />
          <v-text-field v-model="item.sum" label="Сумма" />
        </div>
        <div class="double-input">
          <UiDateInput v-model="item.date_from" label="Дата от" />
          <UiDateInput v-model="item.date_to" label="Дата до" />
        </div>
        <div>
          <UiDateInput v-model="item.date" label="Дата в акте" />
        </div>
        <div>
          <TeacherSelector v-model="item.teacher_id" />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
