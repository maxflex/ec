<script setup lang="ts">
import { clone } from 'rambda'

const modelDefaults: GroupResource = {
  is_archived: false,
  zoom: {
    id: '',
    password: '',
  },
}

const { dialog, width } = useDialog('default')
const loading = ref(false)
const itemId = ref<number>()
const group = ref<GroupResource>(modelDefaults)

function open(r: GroupResource) {
  group.value = clone(r)
  dialog.value = true
}

function create() {
  itemId.value = undefined
  open(modelDefaults)
}

async function edit(r: GroupListResource) {
  itemId.value = r.id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<GroupResource>(`groups/${r.id}`)
  if (data.value) {
    open(data.value)
  }
  loading.value = false
}

async function save() {
  dialog.value = false
  if (itemId.value) {
    const { data } = await useHttp<GroupListResource>(`groups/${itemId.value}`, {
      method: 'put',
      body: group.value,
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
  else {
    const { data } = await useHttp<GroupListResource>('groups', {
      method: 'post',
      body: group.value,
    })
    if (data.value) {
      emit('created', data.value)
    }
  }
  // emit('saved')
}

defineExpose({ create, edit })
const emit = defineEmits<{
  (e: 'created', r: GroupListResource): void
  (e: 'updated', r: GroupResource): void
}>()
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div
      v-if="group"
      class="dialog-wrapper"
    >
      <div class="dialog-header">
        <template v-if="itemId">
          Группа {{ itemId }}
        </template>
        <template v-else>
          Новая группа
        </template>
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          @click="save()"
        />
      </div>
      <UiLoaderr v-if="loading" />
      <div
        v-else
        class="dialog-body"
      >
        <div>
          <UiClearableSelect
            v-model="group.year"
            label="Учебный год"
            :items="selectItems(YearLabel, true)"
          />
        </div>
        <div>
          <UiClearableSelect
            v-model="group.program"
            label="Программа"
            :items="selectItems(ProgramLabel)"
          />
        </div>
        <div>
          <v-text-field
            v-model="group.duration"
            label="Длительность занятия"
            type="number"
            hide-spin-buttons
            suffix="минут"
          />
        </div>
        <div class="double-input">
          <v-text-field
            v-model="group.zoom.id"
            label="Zoom логин"
          />
          <v-text-field
            v-model="group.zoom.password"
            label="Zoom пароль"
          />
        </div>
        <div>
          <v-checkbox
            v-model="group.is_archived"
            label="Заархивирована"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
