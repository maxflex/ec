<script setup lang="ts">
import { mdiCheckAll } from '@mdi/js'
import { clone } from 'rambda'
import type { ContractVersionPricesEditorDialog, ProgramDialog } from '#build/components'

const emit = defineEmits<{
  created: [i: ContractResource]
  updated: [i: ContractVersionListResource]
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
const itemId = ref<number>()
const contractId = ref<number>()
const version = ref<number>() // для отображения в заголовке
const { dialog, width } = useDialog('medium')
const programDialog = ref<InstanceType<typeof ProgramDialog>>()
const pricesEditorDialog = ref<InstanceType<typeof ContractVersionPricesEditorDialog>>()
const saving = ref(false)
const deleting = ref(false)
const loading = ref(false)
const isEditMode = computed(() => itemId.value !== undefined)
const isNewContract = computed(() => contractId.value === undefined)

function createContract() {
  itemId.value = undefined
  contractId.value = undefined
  version.value = undefined
  item.value = clone(modelDefaults)
  dialog.value = true
}

async function addVersion(c: ContractResource) {
  itemId.value = undefined
  contractId.value = c.id
  version.value = c.versions[0].version + 1
  loading.value = true
  dialog.value = true
  const { id: lastVersionId } = c.versions[0]
  const { data } = await useHttp<ContractVersionResource>(
    `contract-versions/${lastVersionId}`,
  )
  if (data.value) {
    const { sum, programs, payments, contract } = data.value
    item.value = {
      contract,
      sum,
      id: newId(),
      date: today(),
      version: version.value,
      programs: programs.map(e => ({
        ...e,
        id: newId(),
      })),
      payments: payments.map(e => ({
        ...e,
        id: newId(),
      })),
    }
  }
  loading.value = false
}

async function edit(cv: ContractVersionListResource) {
  itemId.value = cv.id
  contractId.value = cv.contract.id
  version.value = cv.version
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

function toggleCloseProgram(p: ContractVersionProgramResource) {
  p.is_closed = !p.is_closed
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
        prices: [],
        lessons_planned: 0,
        is_closed: false,
      })
    }
  }
  // Remove programs that are not in the new programs list
  item.value.programs = item.value.programs.filter(({ program }) =>
    programs.includes(program),
  )
}

function onPricesSaved(p: ContractVersionProgramResource) {
  const index = item.value.programs.findIndex(e => e.program === p.program)
  if (index === -1) {
    return
  }
  item.value.programs.splice(index, 1, p)
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
  item.value.programs.splice(
    item.value.programs.findIndex(e => e.id === p.id),
    1,
  )
}

async function save() {
  saving.value = true
  if (isNewContract.value) {
    const { data } = await useHttp<ContractResource>(`contracts`, {
      method: 'post',
      body: {
        ...item.value,
        client_id: Number(useRoute().params.id), // допускаем, что client_id хранится в адресной строке
      },
    })
    if (data.value) {
      emit('created', data.value)
    }
  }
  else if (isEditMode.value) {
    const { data } = await useHttp<ContractVersionListResource>(
      `contract-versions/${item.value.id}`,
      {
        method: 'put',
        body: item.value,
      },
    )

    if (data.value) {
      emit('updated', data.value)
    }
    // if (data.value) {
    //   item.value = data.value
    // }
  }
  else {
    const { data } = await useHttp<ContractVersionListResource>(
      'contract-versions',
      {
        method: 'post',
        body: item.value,
      },
    )
    if (data.value) {
      emit('updated', data.value)
    }
  }
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
    for (const x of p.prices) {
      sum += asInt(x[0])
    }
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
      sum += asInt(x[1]) * (asInt(x[0]) || 1)
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

/**
 * начиная с X занятия
 */
// function getFromLessonLabel(p: ContractVersionProgramResource, index: number): number {
//   return p.prices
//     .filter((x, i) => i < index)
//     .reduce((carry, x) => carry + Number.parseInt(x[0]), 0)
// }

// defineExpose({ create, editVersion, addVersion })
defineExpose({ edit, createContract, addVersion })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div class="dialog-wrapper contract-version-dialog">
      <div class="dialog-header">
        <span v-if="isNewContract"> Новый договор </span>
        <div v-else-if="isEditMode">
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
            v-if="isEditMode"
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
      <UiLoaderr v-if="loading" />
      <div
        v-else
        class="dialog-body"
      >
        <div class="double-input">
          <v-select
            v-model="item.contract.year"
            label="Учебный год"
            :items="selectItems(YearLabel)"
            :disabled="!isNewContract"
          />
          <v-select
            v-model="item.contract.company"
            label="Компания"
            :disabled="!isNewContract"
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
                <th width="120">
                  занятий
                </th>
                <th width="120">
                  цена
                </th>
                <th width="100">
                  прогр.
                </th>
                <th width="48" />
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="p in item.programs"
                :key="p.id"
              >
                <td>
                  <span
                    :class="{ 'text-error': p.is_closed }"
                    @click="toggleCloseProgram(p)"
                  >
                    {{ ProgramLabel[p.program] }}
                  </span>
                </td>
                <td @click="pricesEditorDialog?.open(p)">
                  <div v-for="(prices, index) in p.prices" :key="index">
                    {{ prices[0] }}
                  </div>
                </td>
                <td @click="pricesEditorDialog?.open(p)">
                  <div v-for="(prices, index) in p.prices" :key="index">
                    {{ prices[1] }}
                  </div>
                </td>
                <td>
                  <v-text-field
                    v-model="p.lessons_planned"
                    type="number"
                    hide-spin-buttons
                    density="compact"
                  />
                </td>
                <td class="text-right">
                  <v-btn
                    class="vf-1"
                    icon="$close"
                    variant="plain"
                    color="red"
                    :size="48"
                    :ripple="false"
                    @click="deleteProgram(p)"
                  />
                </td>
              </tr>
              <tr>
                <td>
                  <a
                    class="link-icon"
                    @click="
                      programDialog?.open(item.programs.map((e) => e.program))
                    "
                  >
                    добавить программы
                    <v-icon
                      :size="16"
                      icon="$next"
                    />
                  </a>
                </td>
                <td class="cursor-default">
                  {{ lessonsSum || '' }}
                </td>
                <td class="cursor-default">
                  {{ lessonsMultipliedByPriceSum || '' }}
                </td>
                <td style="padding-top: 13px" class="cursor-default">
                  {{ lessonsPlannedSum || '' }}
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
                <th width="400">
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
                  <a
                    class="link-icon"
                    @click=" addPayment() "
                  >
                    добавить платеж
                    <v-icon
                      :size="16"
                      icon="$next"
                    />
                  </a>
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
  <ContractVersionPricesEditorDialog
    ref="pricesEditorDialog"
    @saved="onPricesSaved"
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
        td {
          vertical-align: top;
          &:first-child {
            span {
              cursor: pointer;
              user-select: none;
            }
          }
          &:nth-child(1),
          &:nth-child(2),
          &:nth-child(3) {
            padding-top: 13px;
            padding-bottom: 13px;
          }
          &:nth-child(2),
          &:nth-child(3) {
            padding-left: 16px;
            cursor: pointer;
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
