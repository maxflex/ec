<script setup lang="ts">
import { clone } from 'rambda'
import { BRANCHES } from '~/utils/sment'

const { dialog, width } = useDialog('x-large')
const client = ref<ClientResource>()

function open(c: ClientResource) {
  client.value = clone(c)
  dialog.value = true
}

defineExpose({ open })
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
          @click="dialog = false"
        />
      </div>
      <div class="dialog-body-2-col">
        <div class="dialog-body">
          <div class="dialog-section__title">
            Ученик
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
          <UiPhoneEditor v-model="client.phones" />
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
          <UiPhoneEditor v-model="client.parent.phones" />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
