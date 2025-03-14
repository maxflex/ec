<script setup lang="ts">
import type { ClientMarkSheetDialog } from '#build/components'
import { mdiTableEdit } from '@mdi/js'
import { clone } from 'rambda'
import { type ClientListResource, type ClientResource, modelDefaults } from '.'
import ClearableSelect from '../Ui/ClearableSelect.vue'

const emit = defineEmits<{
  created: [c: ClientListResource, requestId?: number]
  updated: [c: ClientResource]
}>()

const markSheetDialog = ref<InstanceType<typeof ClientMarkSheetDialog>>()
const { dialog, width } = useDialog('medium')
const item = ref<ClientResource>(clone(modelDefaults))
const itemId = ref<number>()
const loading = ref(false)
const saving = ref(false)
const requestId = ref<number>()
const errors = ref(new Set<string>())

function open(c: ClientResource) {
  item.value = clone(c)
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
  saving.value = true
  errors.value.clear()

  if (itemId.value) {
    const { data, error } = await useHttp<ClientResource>(`clients/${itemId.value}`, {
      method: 'put',
      body: item.value,
    })
    if (data.value) {
      emit('updated', data.value)
      dialog.value = false
    }
    if (error.value) {
      errors.value = new Set(Object.keys(error.value.data.errors))
    }
  }
  else {
    const { data, error } = await useHttp<ClientListResource>('clients', {
      method: 'post',
      body: {
        ...item.value,
        request_id: requestId.value,
      },
    })
    if (data.value) {
      emit('created', data.value, requestId.value)
      dialog.value = false
    }
    if (error.value) {
      errors.value = new Set(Object.keys(error.value.data.errors))
    }
  }
  saving.value = false
}

function onDeleted() {
  dialog.value = false
}

defineExpose({ create, edit })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div
      v-if="item"
      class="dialog-wrapper"
    >
      <div class="dialog-header">
        <div v-if="item.id > 0">
          Редактирование клиента
          <div class="dialog-subheader">
            {{ item.user ? formatName(item.user) : 'неизвестно' }}
            <template v-if="item.created_at">
              {{ formatDateTime(item.created_at) }}
            </template>
          </div>
        </div>
        <span v-else>Добавить клиента
          <template v-if="requestId">
            к заявке {{ requestId }}
          </template>
        </span>
        <div>
          <DialogDeleteBtn
            v-if="itemId"
            :id="itemId"
            confirm-text="Вы уверены, что хотите удалить клиента?"
            api-url="clients"
            @deleted="onDeleted()"
          />
          <v-btn
            :size="48"
            variant="text"
            @click="markSheetDialog?.open(item.mark_sheet)"
          >
            <v-icon :size="24" :icon="mdiTableEdit" class="vf-1"></v-icon>
          </v-btn>
          <v-btn
            icon="$save"
            :size="48"
            :loading="saving"
            variant="text"
            @click="save()"
          />
        </div>
      </div>
      <div class="dialog-body">
        <!-- <div class="dialog-section__title">
            Ученик
          </div> -->
        <div style="margin-bottom: 49px;">
          <AvatarLoader :key="item.id" :item="item" />
        </div>
        <div class="double-input">
          <v-text-field
            v-model="item.last_name"
            label="Фамилия"
          />
          <v-text-field
            v-model="item.first_name"
            label="Имя"
          />
          <v-text-field
            v-model="item.middle_name"
            label="Отчество"
          />
        </div>
        <div class="double-input">
          <v-text-field
            v-model="item.passport.series"
            label="Серия паспорта"
          />
          <v-text-field
            v-model="item.passport.number"
            label="Номер паспорта"
          />
          <UiDateInput
            v-model="item.passport.birthdate"
            label="Дата рождения"
            manual
            :error="errors.has('passport.birthdate')"
          />
        </div>
        <div class="double-input">
          <v-select
            v-model="item.branches"
            label="Филиалы"
            multiple
            :items="selectItems(BranchLabel)"
          />
          <TeacherSelector
            v-model="item.head_teacher_id"
            label="Куратор"
            head-teachers
          />
          <v-text-field
            v-model="item.email"
            label="E-mail"
          />
        </div>

        <PhoneEditor v-model="item.phones" edit-telegram />

        <div>
          <v-checkbox
            v-model="item.is_remote"
            label="Учится удалённо"
          />
        </div>

        <div class="dialog-section__title">
          Представитель
        </div>
        <div class="double-input">
          <v-text-field
            v-model="item.parent.last_name"
            label="Фамилия"
          />
          <v-text-field
            v-model="item.parent.first_name"
            label="Имя"
          />
          <v-text-field
            v-model="item.parent.middle_name"
            label="Отчество"
          />
        </div>
        <div class="double-input">
          <v-text-field
            v-model="item.parent.passport.series"
            label="Серия паспорта"
          />
          <v-text-field
            v-model="item.parent.passport.number"
            label="Номер паспорта"
          />
          <v-text-field
            v-model="item.parent.passport.code"
            label="Код подразделения"
          />
        </div>
        <div class="double-input">
          <UiDateInput
            v-model="item.parent.passport.issued_date"
            label="Дата выдачи паспорта"
            manual
            :error="errors.has('parent.passport.issued_date')"
          />
          <v-text-field
            v-model="item.parent.email"
            label="E-mail"
          />
        </div>

        <v-textarea
          v-model="item.parent.passport.issued_by"
          label="Кем выдан"
          no-resize
          rows="3"
        />
        <v-textarea
          v-model="item.parent.passport.address"
          label="Адрес регистрации"
          no-resize
          rows="3"
        />
        <v-textarea
          v-model="item.parent.passport.fact_address"
          label="Фактический адрес"
          no-resize
          rows="3"
        />
        <div class="double-input">
          <ClearableSelect
            v-model="item.heard_about_us"
            nullify
            label="Откуда вы о нас узнали?"
            :items="selectItems(HeardAboutUsLabel)"
          />
          <ClearableSelect
            v-model="item.grade"
            nullify
            label="Класс"
            :items="selectItems(GradeLabel)"
          />
        </div>
        <PhoneEditor v-model="item.parent.phones" edit-telegram />
      </div>
    </div>
  </v-dialog>
  <LazyClientMarkSheetDialog ref="markSheetDialog" @save="markSheet => (item.mark_sheet = markSheet)" />
</template>
