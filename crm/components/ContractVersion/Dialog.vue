<script setup lang="ts">
import { mdiCheckAll } from '@mdi/js'
import { clone } from 'rambda'
import type { ProgramDialog } from '#build/components'

const emit = defineEmits<{
  updated: [m: ContractEditMode, c: ContractResource | ContractVersionListResource]
  deleted: [i: ContractVersionResource]
}>()
const modelDefaults: ContractVersionResource = {
  id: newId(),
  version: 1,
  date: today(),
  programs: [],
  payments: [],
  contract: {
    id: newId(),
    year: currentAcademicYear(),
    company: 'ooo',
  },
}
const item = ref<ContractVersionResource>(modelDefaults)
const contractId = ref<number>()
const version = ref<number>() // для отображения в заголовке
const { dialog, width } = useDialog('medium')
const programDialog = ref<InstanceType<typeof ProgramDialog>>()
const pricesInput = ref()
const programsInput = ref()
const saving = ref(false)
const deleting = ref(false)
const loading = ref(false)
const mode = ref<ContractEditMode>('edit')

function newContract() {
  mode.value = 'new-contract'
  contractId.value = undefined
  version.value = undefined
  item.value = clone(modelDefaults)
  dialog.value = true
}

async function newVersion(c: ContractResource) {
  const activeVersion = c.versions.find(e => e.is_active)
  await edit(activeVersion!, 'new-version')
}

async function edit(cv: ContractVersionListResource, m: ContractEditMode = 'edit') {
  mode.value = m
  contractId.value = cv.contract.id
  version.value = cv.version + (m === 'edit' ? 0 : 1)
  dialog.value = true
  loading.value = true
  const { data } = await useHttp<ContractVersionResource>(
    `contract-versions/${cv.id}`,
  )
  if (data.value) {
    item.value = data.value
  }
  loading.value = false
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить договор?')) {
    return
  }
  deleting.value = true
  const { status } = await useHttp(`contract-versions/${item.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    deleting.value = false
  }
  else {
    emit('deleted', item.value)
    dialog.value = false
    setTimeout(() => (deleting.value = false), 300)
  }
}

function onProgramsSaved(programs: Program[]) {
  for (const program of programs) {
    const isNewProgram = !item.value.programs.some(
      p => p.program === program,
    )
    if (isNewProgram) {
      item.value.programs.push({
        id: newId(),
        program,
        contract_version_id: item.value.contract.id,
        prices: [
          {
            id: newId(),
            lessons: '',
            price: '',
          },
        ],
        lessons_planned: '',
        is_closed: false,
      })
    }
    nextTick(() => {
      programsInput.value[programsInput.value.length - 1].focus()
    })
  }
  // Remove programs that are not in the new programs list
  item.value.programs = item.value.programs.filter(({ program }) =>
    programs.includes(program),
  )
}

function addPayment() {
  item.value.payments.push({
    id: newId(),
    date: today(),
    sum: 0,
    contract_version_id: item.value.id,
  })
  smoothScroll('dialog', 'bottom')
}

function deletePayment(p: ContractVersionPaymentResource) {
  item.value.payments.splice(
    item.value.payments.findIndex(e => e.id === p.id),
    1,
  )
}

function deleteProgram(p: ContractVersionProgramResource) {
  const index = item.value.programs.findIndex(e => e.id === p.id)
  item.value.programs.splice(index, 1)
}

async function save() {
  saving.value = true
  let responseData: ContractResource | ContractVersionListResource | null
  switch (mode.value) {
    case 'new-contract': {
      const { data } = await useHttp<ContractResource>(`contracts`, {
        method: 'post',
        body: {
          ...item.value,
          client_id: Number(useRoute().params.id), // допускаем, что client_id хранится в адресной строке
        },
      })
      responseData = data.value
      break
    }

    case 'new-version': {
      const { data } = await useHttp<ContractVersionListResource>(
        'contract-versions',
        {
          method: 'post',
          body: item.value,
        },
      )
      responseData = data.value
      break
    }

    case 'edit': {
      const { data } = await useHttp<ContractVersionListResource>(
          `contract-versions/${item.value.id}`,
          {
            method: 'put',
            body: item.value,
          },
      )
      responseData = data.value
      break
    }
  }
  emit('updated', mode.value, responseData!)
  dialog.value = false
  setTimeout(() => (saving.value = false), 300)
}

const lessonsPlannedSum = computed(() => {
  let sum = 0
  for (const p of item.value.programs) {
    sum += asInt(p.lessons_planned)
  }
  return sum
})

const lessonsSum = computed(() => {
  let sum = 0
  for (const p of item.value.programs) {
    sum += p.prices.reduce((carry, x) => asInt(x.lessons) + carry, 0)
  }
  return sum
})

const paymentSum = computed((): number =>
  item.value.payments.reduce((carry, x) => (asInt(x.sum) || 0) + carry, 0),
)

const lessonsMultipliedByPriceSum = computed((): number => {
  let sum = 0
  for (const p of item.value.programs) {
    for (const x of p.prices) {
      sum += asInt(x.lessons) * (asInt(x.price) || 1)
    }
  }
  return sum
})

// все 3 типа платежей сходятся (график, сумма в договоре и в программах)
const isPaymentSumValid = computed(() => {
  if (!item.value) {
    return false
  }
  const contractSum = asInt(item.value.sum)
  return contractSum > 0 && contractSum === lessonsMultipliedByPriceSum.value && lessonsMultipliedByPriceSum.value === paymentSum.value
})

function addPrices(p: ContractVersionProgramResource) {
  const index = item.value.programs.findIndex(e => e.id === p.id)
  item.value.programs[index].prices.push({
    id: newId(),
    price: '',
    lessons: '',
  })
  nextTick(() => {
    pricesInput.value[pricesInput.value.length - 1].focus()
  })
}

function removePrice(p: ContractVersionProgramResource) {
  const index = item.value.programs.findIndex(e => e.id === p.id)
  const pricesLength = item.value.programs[index].prices.length
  if (pricesLength === 1) {
    deleteProgram(p)
    return
  }
  item.value.programs[index].prices.splice(pricesLength - 1, 1)
}

defineExpose({ edit, newContract, newVersion })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div class="dialog-wrapper contract-version-dialog">
      <div class="dialog-header">
        <span v-if="mode === 'new-contract'"> Новый договор </span>
        <div v-else-if="mode === 'edit'">
          Редактирование договора
          <span>№{{ contractId }}–{{ version }}</span>
          <div class="dialog-subheader">
            <template v-if="item.user && item.created_at">
              {{ formatName(item.user) }}
              {{ formatDateTime(item.created_at) }}
            </template>
          </div>
        </div>
        <span v-else>
          Новая версия договора
          <span>№{{ contractId }}–{{ version }}</span>
        </span>
        <div>
          <v-btn
            v-if="mode === 'edit'"
            icon="$delete"
            :size="48"
            variant="text"
            :loading="deleting"
            class="remove-btn"
            @click="destroy()"
          />
          <v-btn
            icon="$save"
            :size="48"
            variant="text"
            :loading="saving"
            @click="save()"
          />
        </div>
      </div>
      <UiLoader v-if="loading" />
      <div
        v-else
        class="dialog-body"
      >
        <div class="double-input">
          <v-select
            v-model="item.contract.year"
            label="Учебный год"
            :items="selectItems(YearLabel)"
            :disabled="mode !== 'new-contract'"
          />
          <v-select
            v-model="item.contract.company"
            label="Компания"
            :disabled="mode !== 'new-contract'"
            :items="selectItems(CompanyLabel)"
          />
        </div>
        <div class="double-input">
          <div class="contract-version-dialog__sum">
            <v-text-field
              v-model="item.sum"
              label="Сумма, руб."
              type="number"
              hide-spin-buttons
            />
            <v-icon v-if="isPaymentSumValid" color="success" :icon="mdiCheckAll" />
          </div>
          <UiDateInput v-model="item.date" today-btn />
        </div>

        <div class="dialog-section">
          <!-- <div class="dialog-section__title">Программы</div> -->

          <table class="dialog-table contract-version-dialog__programs">
            <thead v-if="item.programs.length">
              <tr>
                <th>
                  программа
                </th>
                <th width="118">
                  прогр.
                </th>
                <th width="117">
                  занятий
                </th>
                <th width="117">
                  цена
                </th>
                <th width="48" />
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in item.programs" :key="p.id">
                <td>
                  {{ ProgramLabel[p.program] }}
                </td>
                <td>
                  <v-text-field
                    ref="programsInput"
                    v-model="p.lessons_planned"
                    type="number"
                    hide-spin-buttons
                    density="compact"
                  />
                </td>
                <td>
                  <div v-for="price in p.prices" :key="price.id">
                    <v-text-field
                      ref="pricesInput"
                      v-model="price.lessons"
                      type="number"
                      hide-spin-buttons
                      density="compact"
                    />
                  </div>
                </td>
                <td>
                  <div v-for="price in p.prices" :key="price.id">
                    <v-text-field
                      v-model="price.price"
                      type="number"
                      hide-spin-buttons
                      density="compact"
                    />
                  </div>
                </td>
                <td class="text-right">
                  <v-menu>
                    <template #activator="{ props }">
                      <v-btn
                        v-bind="props"
                        icon="$more"
                        variant="plain"
                        :size="48"
                        :ripple="false"
                      />
                    </template>
                    <v-list>
                      <v-list-item @click="addPrices(p)">
                        добавить цену
                      </v-list-item>
                      <v-list-item @click="removePrice(p)">
                        <template v-if="p.prices.length < 2">
                          удалить программу
                        </template>
                        <template v-else>
                          удалить цену
                        </template>
                      </v-list-item>
                    </v-list>
                  </v-menu>
                </td>
              </tr>
              <tr>
                <td>
                  <UiIconLink @click="() => programDialog?.open(item.programs.map((e) => e.program))">
                    добавить программы
                  </UiIconLink>
                </td>
                <td class="cursor-default">
                  {{ lessonsPlannedSum || '' }}
                </td>
                <td class="cursor-default">
                  {{ lessonsSum || '' }}
                </td>
                <td class="cursor-default">
                  {{ lessonsMultipliedByPriceSum || '' }}
                </td>
                <td />
              </tr>
            </tbody>
          </table>
        </div>

        <div class="dialog-section">
          <table class="dialog-table contract-version-dialog__payments">
            <thead v-if="item.payments.length">
              <tr>
                <th width="410">
                  номер
                </th>
                <th width="180">
                  дата
                </th>
                <th width="180">
                  сумма, руб.
                </th>
                <th />
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(p, index) in item.payments"
                :key="p.id"
              >
                <td>
                  платеж {{ index + 1 }}
                </td>
                <td>
                  <UiDateInput
                    v-model="p.date"
                    label=""
                    density="compact"
                  />
                </td>
                <td>
                  <v-text-field
                    v-model="p.sum"
                    type="number"
                    hide-spin-buttons
                    density="compact"
                  />
                </td>
                <td class="text-right">
                  <v-btn
                    icon="$close"
                    variant="plain"
                    color="red"
                    :size="48"
                    :ripple="false"
                    @click="deletePayment(p)"
                  />
                </td>
              </tr>
              <tr>
                <td>
                  <UiIconLink @click=" addPayment() ">
                    добавить платеж
                  </UiIconLink>
                </td>
                <td />
                <td>
                  {{ paymentSum || '' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </v-dialog>
  <ProgramDialog
    ref="programDialog"
    @saved="onProgramsSaved"
  />
</template>

<style lang="scss">
.contract-version-dialog {
  &__payments {
    tr:not(:last-child) {
      td:first-child {
        input {
          padding-left: 0 !important;
        }
      }
    }
  }
  &__programs {
    tbody {
      tr {
        &:last-child {
          td {
            padding-top: 13px;
          }
        }
        &:not(:last-child) {
          td:first-child {
            padding-top: 13px;
          }
        }
        td {
          vertical-align: top;
          &:nth-child(3),
          &:nth-child(4) {
            & > div {
              &:not(:last-child) {
                border-bottom: thin solid
                  rgba(var(--v-border-color), var(--v-border-opacity));
              }
            }
          }
        }
      }
    }
  }
  &__sum {
    position: relative;
    .v-icon {
      position: absolute;
      right: 12px;
      top: 18px;
      font-size: 20px;
    }
  }
}
</style>
