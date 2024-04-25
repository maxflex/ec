<script setup lang="ts">
import type { Client, Phone } from "~/utils/models"
import { cloneDeep, uniqueId } from "lodash"
import { ENTITY_TYPE, BRANCHES } from "~/utils/sment"

const phoneMask = { mask: "+7 (###) ###-##-##" }
const { dialog, width } = useDialog()
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
    <div class="dialog-content" v-if="client">
      <div class="dialog-header">
        <span v-if="client.id > 0"> Редактирование клиента </span>
        <span v-else> Добавить платеж </span>
        <v-btn icon="$save" :size="48" variant="text" @click="dialog = false" />
      </div>
      <div class="dialog-body">
        <div>
          <v-text-field v-model="client.last_name" label="Фамилия" />
        </div>
        <div>
          <v-text-field v-model="client.first_name" label="Имя" />
        </div>
        <div>
          <v-text-field v-model="client.middle_name" label="Отчество" />
        </div>
        <div>
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
        </div>
        <div>
          <TeacherSelector label="Куратор" v-model="client.head_teacher_id" />
        </div>

        <div class="dialog-section">
          <!-- <div class="dialog-section__title">Программы</div> -->
          <div class="table table--actions-on-hover client-dialog__phones">
            <div
              v-for="p in client.phones.filter((x) => !x.is_parent)"
              :key="p.id"
            >
              <div>
                <v-text-field
                  v-model="p.number"
                  density="compact"
                  v-maska:[phoneMask]
                />
              </div>
              <div>
                <v-text-field v-model="p.comment" density="compact" />
              </div>
              <div class="actions">
                <v-btn
                  icon="$close"
                  variant="plain"
                  color="red"
                  :size="48"
                  :ripple="false"
                  @click="removePhone(p)"
                >
                </v-btn>
              </div>
            </div>
            <div style="border-bottom: 0">
              <a class="cursor-pointer" @click="addPhone(false)">
                добавить номер
              </a>
            </div>
          </div>
        </div>

        <div class="dialog-section__title">Представитель</div>
        <div>
          <v-text-field v-model="client.parent_last_name" label="Фамилия" />
        </div>
        <div>
          <v-text-field v-model="client.parent_first_name" label="Имя" />
        </div>
        <div>
          <v-text-field v-model="client.parent_middle_name" label="Отчество" />
        </div>

        <div class="dialog-section">
          <!-- <div class="dialog-section__title">Программы</div> -->
          <div class="table table--actions-on-hover client-dialog__phones">
            <div
              v-for="p in client.phones.filter((x) => x.is_parent)"
              :key="p.id"
            >
              <div>
                <v-text-field
                  v-model="p.number"
                  density="compact"
                  v-maska:[phoneMask]
                />
              </div>
              <div>
                <v-text-field v-model="p.comment" density="compact" />
              </div>
              <div class="actions">
                <v-btn
                  icon="$close"
                  variant="plain"
                  color="red"
                  :size="48"
                  :ripple="false"
                  @click="removePhone(p)"
                >
                </v-btn>
              </div>
            </div>
            <div style="border-bottom: 0">
              <a class="cursor-pointer" @click="addPhone(true)">
                добавить номер
              </a>
            </div>
          </div>
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
</style>
