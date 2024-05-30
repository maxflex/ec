<script setup lang="ts">
import { clone } from 'rambda'

const modelDefaults: TeacherResource = {
  id: -1,
  first_name: null,
  last_name: null,
  middle_name: null,
  phones: [],
}

const { dialog, width } = useDialog('default')
const teacher = ref<TeacherResource>(modelDefaults)
const loading = ref(false)
const itemId = ref<number>()

function open(c: TeacherResource) {
  teacher.value = clone(c)
  dialog.value = true
}

function create() {
  itemId.value = undefined
  open(modelDefaults)
}
async function edit(c: TeacherResource) {
  itemId.value = c.id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<TeacherResource>(`teachers/${c.id}`)
  if (data.value) {
    open(data.value)
  }
  loading.value = false
}

async function save() {
  dialog.value = false
  if (itemId.value) {
    const { data } = await useHttp<TeacherResource>(`teachers/${itemId.value}`, {
      method: 'put',
      body: teacher.value,
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
  else {
    const { data } = await useHttp<TeacherResource>('teachers', {
      method: 'post',
      body: teacher.value,
    })
    if (data.value) {
      emit('created', data.value)
    }
  }

  // emit('saved')
}

defineExpose({ create, edit })
const emit = defineEmits<{
  (e: 'created' | 'updated', c: TeacherResource): void
}>()
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div
      v-if="teacher"
      class="dialog-wrapper"
    >
      <div class="dialog-header">
        <span v-if="teacher.id > 0"> Редактирование преподавателя </span>
        <span v-else> Добавить преподавателя </span>
        <v-btn
          icon="$save"
          :size="48"
          variant="text"
          @click="save()"
        />
      </div>
      <div class="dialog-body">
        <div>
          <v-text-field
            v-model="teacher.last_name"
            label="Фамилия"
          />
        </div>
        <div>
          <v-text-field
            v-model="teacher.first_name"
            label="Имя"
          />
        </div>
        <div>
          <v-text-field
            v-model="teacher.middle_name"
            label="Отчество"
          />
        </div>
        <div>
          <v-select
            v-model="teacher.status"
            label="Статус"
            :items="selectItems(TeacherStatusLabel)"
          />
        </div>
        <UiPhoneEditor v-model="teacher.phones" />
      </div>
    </div>
  </v-dialog>
</template>
