<script setup lang="ts">
import { cloneDeep } from 'lodash-es'

const emit = defineEmits<{
  updated: [e: GroupActResource]
}>()
const apiUrl = 'group-acts'

const modelDefaults: GroupActResource = {
  id: newId(),
  date: '',
  date_from: '',
  date_to: '',
}

const { dialog, width } = useDialog('default')
const item = ref<GroupActResource>(modelDefaults)
const saving = ref(false)

function edit(groupAct: GroupActResource) {
  item.value = cloneDeep(groupAct)
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
    const { data } = await useHttp<GroupActResource>(`${apiUrl}/${item.value.id}`, {
      method: 'put',
      body: { ...item.value },
    })
    emit('updated', data.value!)
  }
  else {
    const { data } = await useHttp<GroupActResource>(apiUrl, {
      method: 'post',
      body: { ...item.value },
    })
    emit('updated', data.value!)
  }
  dialog.value = false
  saving.value = false
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
          <template v-if="item.id > 0">
            <CrudDeleteBtn
              :id="item.id"
              :api-url="apiUrl"
              confirm-text="Вы уверены, что хотите удалить акт?"
              @deleted="dialog = false"
            />
            <PrintBtn
              :items="[
                { 12: 'ooo' },
                { 18: 'ooo' },
                { 12: 'ip' },
                { 18: 'ip' },
              ]"
              :extra="{ act_id: item.id }"
            />
          </template>
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
