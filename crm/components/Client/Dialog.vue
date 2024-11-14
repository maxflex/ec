<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  created: [c: ClientListResource, requestId?: number]
  updated: [c: ClientResource]
}>()

const modelDefaults: ClientResource = {
  id: newId(),
  first_name: null,
  last_name: null,
  middle_name: null,
  head_teacher_id: null,
  branches: [],
  phones: [],
  photo_url: null,
  is_remote: false,
  entity_type: EntityTypeValue.client,
  passport: {
    series: null,
    number: null,
    birthdate: null,
  },
  parent: {
    id: newId(),
    first_name: null,
    last_name: null,
    middle_name: null,
    phones: [],
    passport: {
      series: null,
      number: null,
      address: null,
      code: null,
      issued_date: null,
      issued_by: null,
      fact_address: null,
    },
  },
}

const { dialog, width } = useDialog('medium')
const client = ref<ClientResource>(clone(modelDefaults))
const loading = ref(false)
const itemId = ref<number>()
const requestId = ref<number>()

function open(c: ClientResource) {
  client.value = clone(c)
  dialog.value = true
}

function create(reqId?: number) {
  itemId.value = undefined
  requestId.value = reqId
  open(modelDefaults)
}

async function edit(id: number) {
  itemId.value = id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<ClientResource>(`clients/${id}`)
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
    const { data } = await useHttp<ClientListResource>('clients', {
      method: 'post',
      body: {
        ...client.value,
        request_id: requestId.value,
      },
    })
    if (data.value) {
      emit('created', data.value, requestId.value)
    }
  }

  // emit('saved')
}

defineExpose({ create, edit })
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
        <div v-if="client.id > 0">
          Редактирование клиента
          <div class="dialog-subheader">
            {{ client.user ? formatName(client.user) : 'неизвестно' }}
            <template v-if="client.created_at">
              {{ formatDateTime(client.created_at) }}
            </template>
          </div>
        </div>
        <span v-else>Добавить клиента
          <template v-if="requestId">
            к заявке {{ requestId }}
          </template>
        </span>
        <v-btn
          icon="$save"
          :size="48"
          variant="text"
          @click="save()"
        />
      </div>
      <div class="dialog-body">
        <!-- <div class="dialog-section__title">
            Ученик
          </div> -->
        <div style="margin-bottom: 49px;">
          <AvatarLoader :key="client.id" :item="client" />
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
          <v-text-field
            v-model="client.passport.series"
            label="Серия паспорта"
          />
          <v-text-field
            v-model="client.passport.number"
            label="Номер паспорта"
          />
          <UiDateInput
            v-model="client.passport.birthdate"
            label="Дата рождения"
          />
        </div>
        <div class="double-input">
          <v-select
            v-model="client.branches"
            label="Филиалы"
            multiple
            :items="selectItems(BranchLabel)"
          />

          <TeacherSelector
            v-model="client.head_teacher_id"
            label="Куратор"
          />
        </div>

        <PhoneEditor v-model="client.phones" />

        <div>
          <v-checkbox
            v-model="client.is_remote"
            label="Учится удалённо"
          />
        </div>

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
            v-model="client.parent.passport.series"
            label="Серия паспорта"
          />
          <v-text-field
            v-model="client.parent.passport.number"
            label="Номер паспорта"
          />
          <v-text-field
            v-model="client.parent.passport.code"
            label="Код подразделения"
          />
        </div>
        <UiDateInput
          v-model="client.parent.passport.issued_date"
          label="Дата выдачи паспорта"
        />
        <v-textarea
          v-model="client.parent.passport.issued_by"
          label="Кем выдан"
          no-resize
          rows="3"
        />
        <v-textarea
          v-model="client.parent.passport.address"
          label="Адрес регистрации"
          no-resize
          rows="3"
        />
        <v-textarea
          v-model="client.parent.passport.fact_address"
          label="Фактический адрес"
          no-resize
          rows="3"
        />
        <PhoneEditor v-model="client.parent.phones" />
      </div>
    </div>
  </v-dialog>
</template>
