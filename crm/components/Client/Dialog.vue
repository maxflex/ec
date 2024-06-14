<script setup lang="ts">
import { clone } from 'rambda'
import { BRANCHES } from '~/utils/sment'

const modelDefaults: ClientResource = {
  id: -1,
  first_name: null,
  last_name: null,
  middle_name: null,
  branches: [],
  birthdate: null,
  user_id: null,
  head_teacher_id: null,
  head_teacher: null,
  phones: [],
  photo_url: null,
  parent: {
    id: -1,
    first_name: null,
    last_name: null,
    middle_name: null,
    passport_series: null,
    passport_number: null,
    passport_address: null,
    passport_code: null,
    passport_issued_date: null,
    passport_issued_by: null,
    fact_address: null,
    phones: [],
  },
}

const { dialog, width } = useDialog('x-large')
const client = ref<ClientResource>(clone(modelDefaults))
const loading = ref(false)
const itemId = ref<number>()

function open(c: ClientResource) {
  client.value = clone(c)
  dialog.value = true
}

function create() {
  itemId.value = undefined
  open(modelDefaults)
}
async function edit(c: ClientResource) {
  itemId.value = c.id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<ClientResource>(`clients/${c.id}`)
  if (data.value) {
    open(data.value)
  }
  loading.value = false
}

async function save() {
  dialog.value = false
  if (itemId.value) {
    const { data } = await useHttp<ClientResource>(`clients/${itemId.value}`, {
      method: 'put',
      body: client.value,
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
  else {
    const { data } = await useHttp<ClientResource>('clients', {
      method: 'post',
      body: client.value,
    })
    if (data.value) {
      emit('created', data.value)
    }
  }

  // emit('saved')
}

defineExpose({ create, edit })
const emit = defineEmits<{
  (e: 'created' | 'updated', c: ClientResource): void
}>()
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div
      v-if="client"
      class="dialog-wrapper"
    >
      <div class="dialog-header">
        <span v-if="client.id > 0"> Редактирование клиента </span>
        <span v-else> Добавить платеж </span>
        <v-btn
          icon="$save"
          :size="48"
          variant="text"
          @click="save()"
        />
      </div>
      <div class="dialog-body-2-col">
        <div class="dialog-body">
          <!-- <div class="dialog-section__title">
            Ученик
          </div> -->
          <div class="mb-10">
            <AvatarLoader
              :key="client.id"
              :entity="'client'"
              :item="client"
            />
          </div>
          <div class="double-input">
            <v-text-field
              v-model="client.last_name"
              label="Фамилия"
            />
            <v-text-field
              v-model="client.first_name"
              label="Имя"
            />
            <v-text-field
              v-model="client.middle_name"
              label="Отчество"
            />
          </div>
          <div class="double-input">
            <v-select
              v-model="client.branches"
              label="Филиалы"
              multiple
              :items="
                Object.keys(BRANCHES).map((value) => ({
                  value,
                  title: BRANCHES[value],
                }))
              "
            />

            <TeacherSelector
              v-model="client.head_teacher_id"
              label="Куратор"
            />
          </div>
          <PhoneEditor v-model="client.phones" />
        </div>
        <div class="dialog-body">
          <div class="dialog-section__title">
            Представитель
          </div>
          <div class="double-input">
            <v-text-field
              v-model="client.parent.last_name"
              label="Фамилия"
            />
            <v-text-field
              v-model="client.parent.first_name"
              label="Имя"
            />
            <v-text-field
              v-model="client.parent.middle_name"
              label="Отчество"
            />
          </div>
          <div class="double-input">
            <v-text-field
              v-model="client.parent.passport_series"
              label="Серия паспорта"
            />
            <v-text-field
              v-model="client.parent.passport_number"
              label="Номер паспорта"
            />
            <v-text-field
              v-model="client.parent.passport_code"
              label="Код подразделения"
            />
          </div>
          <v-textarea
            v-model="client.parent.passport_issued_by"
            label="Паспорт выдан"
            no-resize
            rows="3"
          />
          <v-textarea
            v-model="client.parent.passport_address"
            label="Адрес регистрации"
            no-resize
            rows="3"
          />
          <v-textarea
            v-model="client.parent.fact_address"
            label="Фактический адрес"
            no-resize
            rows="3"
          />
          <PhoneEditor v-model="client.parent.phones" />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
