<script setup lang="ts">
import type { Client, Phone } from "~/utils/models"
import { cloneDeep, uniqueId } from "lodash"
import { ENTITY_TYPE, BRANCHES } from "~/utils/sment"

const phoneMask = { mask: "+7 (###) ###-##-##" }
const { dialog, width } = useDialog(700)
const client = ref<Client>()

function open(c: Client) {
  client.value = cloneDeep(c)
  dialog.value = true
}

function addPhone(isParent: boolean) {
  if (!client.value) {
    return
  }
  // @ts-expect-error
  client.value?.phones.push({
    id: parseInt(uniqueId()) * -1,
    number: "",
    comment: "",
    is_parent: isParent,
    is_verified: false,
    telegram_id: null,
    entity_id: client.value.id,
    entity_type: ENTITY_TYPE.client,
  })
}

function removePhone(p: Phone) {
  if (!client.value) {
    return
  }
  client.value.phones.splice(
    client.value.phones.findIndex(({ id }) => id === p.id),
    1,
  )
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper" v-if="client">
      <div class="dialog-header">
        <span v-if="client.id > 0"> Редактирование клиента </span>
        <span v-else> Добавить платеж </span>
        <v-btn icon="$save" :size="48" variant="text" @click="dialog = false" />
      </div>
      <div class="dialog-body">
        <div class="double-input">
          <v-text-field v-model="client.last_name" label="Фамилия" />
          <v-text-field v-model="client.first_name" label="Имя" />
          <v-text-field v-model="client.middle_name" label="Отчество" />
        </div>
        <div class="double-input">
          <v-select
            label="Филиалы"
            v-model="client.branches"
            multiple
            :items="
              Object.keys(BRANCHES).map((value) => ({
                value,
                title: BRANCHES[value],
              }))
            "
          />

          <TeacherSelector label="Куратор" v-model="client.head_teacher_id" />
        </div>
        <div class="double-input">
          <div style="position: relative">
            <UiDateInput v-model="client.birthdate" label="Дата рождения" />
            <div
              style="position: absolute; right: 12px; top: 16px"
              class="text-gray"
            >
              {{ filterAge(client.birthdate) }}
            </div>
          </div>
          <UiDateInput
            v-model="client.passport_issued_date"
            label="Дата выдачи паспорта"
          />
        </div>
        <div
          class="double-input"
          v-for="p in client.phones.filter((x) => !x.is_parent)"
          :key="p.id"
        >
          <div>
            <v-text-field
              v-model="p.number"
              v-maska:[phoneMask]
              label="Телефон"
            />
            <v-icon icon="$close" @click="removePhone(p)"></v-icon>
          </div>
          <v-text-field v-model="p.comment" label="Комментарий" />
        </div>
        <div style="position: relative; top: -10px">
          <a class="cursor-pointer" @click="addPhone(false)">
            добавить номер
          </a>
        </div>

        <div class="dialog-section__title">Представитель</div>
        <div class="double-input">
          <v-text-field v-model="client.parent_last_name" label="Фамилия" />
          <v-text-field v-model="client.parent_first_name" label="Имя" />
          <v-text-field v-model="client.parent_middle_name" label="Отчество" />
        </div>
        <div class="double-input">
          <v-text-field
            label="Серия паспорта"
            v-model="client.passport_series"
          />
          <v-text-field
            label="Номер паспорта"
            v-model="client.passport_number"
          />
          <v-text-field
            label="Код подразделения"
            v-model="client.passport_code"
          />
        </div>
        <v-textarea
          v-model="client.passport_issued_by"
          label="Паспорт выдан"
          no-resize
          rows="3"
        />
        <v-textarea
          v-model="client.passport_address"
          label="Адрес регистрации"
          no-resize
          rows="3"
        />
        <v-textarea
          v-model="client.fact_address"
          label="Фактический адрес"
          no-resize
          rows="3"
        />
        <div
          class="double-input"
          v-for="p in client.phones.filter((x) => x.is_parent)"
          :key="p.id"
        >
          <div>
            <v-text-field
              v-model="p.number"
              v-maska:[phoneMask]
              label="Номер"
            />
            <v-icon icon="$close" @click="removePhone(p)"></v-icon>
          </div>
          <v-text-field v-model="p.comment" label="Комментарий" />
        </div>
        <div style="position: relative; top: -10px">
          <a class="cursor-pointer" @click="addPhone(true)"> добавить номер </a>
        </div>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.client-dialog {
  &__phones {
    & > div {
      & > div {
        &:first-child {
          min-width: 150px;
          max-width: 150px;
          .v-input {
            margin-left: -11px;
            input {
              padding: 0 10px !important;
            }
          }
        }
        &:nth-child(2) {
          flex: 1;
        }
        &:last-child {
          min-width: 50px;
          max-width: 50px;
        }
      }
    }
  }
}

.double-input {
  & > div {
    &:first-child {
      position: relative;
    }
  }
  .v-icon {
    position: absolute;
    right: 10px;
    top: 19px;
    opacity: 0.25;
    z-index: 1;
    // transition: all ease-in-out 0.1s;
    cursor: pointer;
    font-size: 20px !important;
    &:hover {
      opacity: 1;
      color: rgb(var(--v-theme-error));
    }
  }
}
</style>
