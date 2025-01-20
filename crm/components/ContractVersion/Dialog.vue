<script setup lang="ts">
import type { PrintDialog } from '#build/components'
import { clone } from 'rambda'

const emit = defineEmits<{
  updated: [m: ContractEditMode, c: ContractResource | ContractVersionListResource]
  deleted: [i: ContractVersionResource]
}>()
const modelDefaults: ContractVersionResource = {
  id: newId(),
  seq: 1,
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
// только для отображения в заголовке
const seq = ref<number>()
const { dialog, width } = useDialog('medium')
const pricesInput = ref()
const programsInput = ref()
const saving = ref(false)
const deleting = ref(false)
const loading = ref(false)
const mode = ref<ContractEditMode>('edit')

const printDialog = ref<InstanceType<typeof PrintDialog>>()
const printOptions: PrintOption[] = [
  { id: 1, label: 'договор' },
  { id: 2, label: 'допсоглашение' },
  { id: 3, label: 'договор школа-родитель' },
  { id: 4, label: 'допсоглашение маткапитал' },
  { id: 5, label: 'соглашение о расторжении' },
  { id: 6, label: 'согласие на обработку данных' },
  { id: 8, label: 'заявление на очное обучение' },
  { id: 7, label: 'акт оказанных услуг' },
]

function newContract() {
  mode.value = 'new-contract'
  contractId.value = undefined
  seq.value = undefined
  item.value = clone(modelDefaults)
  dialog.value = true
}

async function newVersion(c: ContractResource) {
  const activeVersion = c.versions.find(e => e.is_active)!
  await edit(activeVersion, 'new-version')
}

async function edit(cv: ContractVersionListResource, m: ContractEditMode = 'edit') {
  mode.value = m
  contractId.value = cv.contract.id
  seq.value = cv.seq + (m === 'edit' ? 0 : 1)
  dialog.value = true
  loading.value = true
  const { data } = await useHttp<ContractVersionResource>(
    `contract-versions/${cv.id}`,
  )
  if (data.value) {
    if (m === 'edit') {
      item.value = data.value
    }
    else {
      const { programs, payments } = data.value
      item.value = {
        ...data.value,
        id: newId(),
        date: today(),
        programs: programs.map(p => ({
          ...p,
          id: newId(),
          prices: p.prices.map(e => ({
            ...e,
            id: newId(),
          })),
        })),
        payments: payments.map(p => ({
          ...p,
          id: newId(),
        })),
      }
    }
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
      lessons_conducted: 0,
      lessons_to_be_conducted: 0,
      lessons_planned: '',
    })
  }
  nextTick(() => programsInput.value[programsInput.value.length - 1].focus())
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

const lessonsConductedSum = computed(() => item.value.programs.reduce((carry, p) => carry + p.lessons_conducted, 0))
const lessonsToBeConductedSum = computed(() => item.value.programs.reduce((carry, p) => carry + p.lessons_to_be_conducted, 0))

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
  // в закрытых договорах нет графика платежей
  if (!paymentSum.value) {
    return true
  }
  const contractSum = asInt(item.value.sum)
  return contractSum > 0
    && contractSum === lessonsMultipliedByPriceSum.value
    && lessonsMultipliedByPriceSum.value === paymentSum.value
})

const preSelected = computed(() => item.value.programs.map(e => e.program))

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

interface LessonsConducted {
  [index: number]: { // program_id
    [index: number]: { // price_id
      lessons_conducted: number
      lessons_to_be_conducted: number
    }
  }
}

const lessonsConducted = computed<LessonsConducted>(() => {
  const result: LessonsConducted = {}
  for (const program of item.value.programs) {
    result[program.id] = {}
    let lessonsConductedLeft = program.lessons_conducted
    let lessonsToBeConductedLeft = program.lessons_to_be_conducted

    for (const price of program.prices) {
      const lessons = asInt(price.lessons)
      const lessonsConducted = (lessonsConductedLeft - lessons) < 0 ? lessonsConductedLeft : lessons
      let lessonsToBeConducted = 0

      if (lessonsConducted < lessons && lessonsToBeConductedLeft) {
        const x = lessons - lessonsConducted
        lessonsToBeConducted = Math.min(x, lessonsToBeConductedLeft)
        lessonsToBeConductedLeft -= lessonsToBeConducted
      }

      result[program.id][price.id] = {
        lessons_conducted: lessonsConducted,
        lessons_to_be_conducted: lessonsToBeConducted,
      }

      lessonsConductedLeft -= lessons
    }
  }
  return result
})

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
          <span>№{{ contractId }}–{{ seq }}</span>
          <div class="dialog-subheader">
            <template v-if="item.user && item.created_at">
              {{ formatName(item.user) }}
              {{ formatDateTime(item.created_at) }}
            </template>
          </div>
        </div>
        <span v-else>
          Новая версия договора
          <span>№{{ contractId }}–{{ seq }}</span>
        </span>
        <div>
          <template v-if="mode === 'edit'">
            <v-btn
              icon="$delete"
              :size="48"
              variant="text"
              :loading="deleting"
              class="remove-btn"
              @click="destroy()"
            />
            <v-menu>
              <template #activator="{ props }">
                <v-btn
                  v-bind="props"
                  icon="$print"
                  :size="48"
                  variant="text"
                />
              </template>
              <v-list>
                <v-list-item
                  v-for="p in printOptions" :key="p.id"
                  @click="printDialog?.open(p, { contract_version_id: item.id })"
                >
                  {{ p.label }}
                </v-list-item>
              </v-list>
            </v-menu>
          </template>
          <v-btn
            icon="$save"
            :size="48"
            variant="text"
            :loading="saving"
            :disabled="!isPaymentSumValid"
            @click="save()"
          />
        </div>
      </div>
      <UiLoader v-if="loading" />
      <div v-else class="dialog-body">
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
            <a v-if="lessonsMultipliedByPriceSum" class="date-input__today cursor-default">
              <span class="text-black">
                рекомендуемая сумма:
              </span>
              <span class="cursor-pointer" @click="item.sum = lessonsMultipliedByPriceSum">
                {{ formatPrice(lessonsMultipliedByPriceSum) }}
              </span>
            </a>
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
                  проведено
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
                    <span v-if="lessonsConducted[p.id][price.id].lessons_conducted > 0">
                      {{ lessonsConducted[p.id][price.id].lessons_conducted }}
                    </span>
                    <span v-if="lessonsConducted[p.id][price.id].lessons_to_be_conducted" class="text-gray pl-1">
                      + {{ lessonsConducted[p.id][price.id].lessons_to_be_conducted }}
                    </span>
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
                  <ProgramSelectorMenu
                    :pre-selected="preSelected"
                    @saved="onProgramsSaved"
                  />
                </td>
                <td class="cursor-default">
                  {{ lessonsPlannedSum || '' }}
                </td>
                <td class="cursor-default">
                  {{ lessonsSum || '' }}
                </td>
                <td class="cursor-default">
                  {{ lessonsConductedSum || '' }}
                  <span v-if="lessonsToBeConductedSum" class="mr-1">
                    + {{ lessonsToBeConductedSum }}
                  </span>
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
                    :year="item.contract.year"
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
                  <UiIconLink @click="addPayment() ">
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
  <LazyPrintDialog ref="printDialog" />
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
          &:nth-child(4),
          &:nth-child(5) {
            & > div {
              &:not(:last-child) {
                border-bottom: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
              }
            }
          }
          &:nth-child(4) {
            & > div {
              cursor: default;
              display: flex;
              height: 51px;
              align-items: center;
              padding-left: 20px;
              color: rgb(var(--v-theme-gray));
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
