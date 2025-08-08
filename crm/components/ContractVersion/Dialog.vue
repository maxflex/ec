<script setup lang="ts">
import type { PrintDialog } from '#build/components'
import type { ContractResource, ContractVersionListResource, ContractVersionPaymentResource, ContractVersionProgramPrice, ContractVersionProgramResource, ContractVersionResource } from '.'
import type { SavedScheduleDraftResource } from '../ScheduleDraft'
import { mdiFlipVertical, mdiTextBoxCheckOutline, mdiTextBoxOutline } from '@mdi/js'
import { addMonths, format } from 'date-fns'
import { cloneDeep } from 'lodash-es'
import { modelDefaults } from '.'

const emit = defineEmits<{
  updated: [m: ContractEditMode, c: ContractResource | ContractVersionListResource]
  deleted: [i: ContractVersionResource]
}>()

const route = useRoute()
const router = useRouter()
const selectedDraft = ref<SavedScheduleDraftResource>() // хранит ID загруженного проекта
const applyMoveGroups = ref(false) // применить изменения в группах (для подгруженного проекта договора)

const { user } = useAuthStore()
const item = ref<ContractVersionResource>(modelDefaults)
const contractId = ref<number>()
// только для отображения в заголовке
const seq = ref<number>()
const { dialog, width } = useDialog('medium')
const pricesInput = ref()
const programsInput = ref()
const saving = ref(false)
const loading = ref(false)
const mode = ref<ContractEditMode>('edit')

const printDialog = ref<InstanceType<typeof PrintDialog>>()
const printOptions: PrintOption[] = [
  { id: 1, label: 'договор' },
  { id: 2, label: 'допсоглашение' },
  { id: 3, label: 'договор школа-родитель' },
  { id: 16, label: 'договор школа-родитель 9кл' },
  { id: 4, label: 'допсоглашение маткапитал' },
  { id: 5, label: 'соглашение о расторжении' },
  { id: 6, label: 'согласие на обработку данных' },
  { id: 8, label: 'заявление на очное обучение' },
  { id: 7, label: 'акт оказанных услуг' },
]

function newContract(clientId: number) {
  mode.value = 'new-contract'
  contractId.value = undefined
  seq.value = undefined
  item.value = cloneDeep(modelDefaults)
  item.value.contract.client_id = clientId
  dialog.value = true
}

/**
 * @param savedDraft загрузить из ранее сохраненного драфта
 * @param contractId если подгружаем draft из RAM, то обязательно указать по какому договору
 */
async function fromDraft({ savedDraft, contractId: cId }: {
  savedDraft?: SavedScheduleDraftResource
  contractId?: number
}) {
  loading.value = true
  const contractIdFromDraft = cId !== undefined ? (cId < 0 ? undefined : cId) : (savedDraft!.contract_id || undefined)
  // @ts-expect-error
  selectedDraft.value = savedDraft || { id: -1 }
  mode.value = contractIdFromDraft ? 'new-version' : 'new-contract'
  if (mode.value === 'new-contract') {
    contractId.value = undefined
    seq.value = undefined
  }
  else {
    contractId.value = contractIdFromDraft
  }

  dialog.value = true

  const { data } = await useHttp<ContractVersionResource>(
    `schedule-drafts/fill-contract`,
    {
      method: 'POST',
      body: {
        id: savedDraft?.id,
        contract_id: cId,
      },
    },
  )

  item.value = data.value!

  if (mode.value === 'new-version') {
    seq.value = item.value.seq + 1
  }

  loading.value = false
}

async function newVersion(c: ContractResource) {
  const activeVersion = c.versions.find(e => e.is_active)!
  await edit(activeVersion, 'new-version')
}

async function edit(cv: ContractVersionListResource, m: ContractEditMode = 'edit') {
  loading.value = true
  mode.value = m
  contractId.value = cv.contract.id
  selectedDraft.value = undefined
  seq.value = cv.seq + (m === 'edit' ? 0 : 1)
  dialog.value = true
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
      lessons_suggest: 0,
      lessons_planned: '',
      group_id: null,
      client_lesson_prices: [],
    })
  }
  nextTick(() => programsInput.value[programsInput.value.length - 1].focus())
}

function addPayment() {
  const lastPayment = item.value.payments[item.value.payments.length - 1]

  item.value.payments.push({
    id: newId(),
    date: lastPayment ? format(addMonths(lastPayment.date, 1), 'yyyy-MM-dd') : today(),
    sum: lastPayment ? lastPayment.sum : 0,
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
  let newContractId: number
  const clonedItem = cloneDeep(item.value)

  switch (mode.value) {
    case 'new-contract': {
      const { data, error } = await useHttp<ContractResource>(
        `contracts`,
        {
          method: 'post',
          body: {
            ...clonedItem,
            // применить перемещения в группе
            apply_move_groups: applyMoveGroups.value,
          },
        },
      )

      if (error.value) {
        useGlobalMessage(error.value.data?.message, 'error')
        saving.value = false

        return
      }

      responseData = data.value
      newContractId = data.value!.id
      useGlobalMessage(`Создан новый договор №${newContractId}`, 'success')
      break
    }

    case 'new-version': {
      const { data, error } = await useHttp<ContractVersionListResource>(
        'contract-versions',
        {
          method: 'post',
          body: {
            ...clonedItem,
            // применить перемещения в группе
            apply_move_groups: applyMoveGroups.value,
          },
        },
      )

      if (error.value) {
        useGlobalMessage(error.value.data?.message, 'error')
        saving.value = false

        return
      }
      responseData = data.value
      newContractId = data.value!.contract.id
      useGlobalMessage(`Создана версия ${data.value!.seq} к договору №${newContractId}`, 'success')
      break
    }

    case 'edit': {
      const { data, error } = await useHttp<ContractVersionListResource>(
        `contract-versions/${item.value.id}`,
        {
          method: 'put',
          body: clonedItem,
        },
      )

      if (error.value) {
        useGlobalMessage(error.value.data?.message, 'error')
        saving.value = false

        return
      }

      responseData = data.value
      newContractId = data.value!.contract.id

      break
    }
  }

  // если находимся не на странице клиента, то редиректим туда
  if (route.name !== 'clients-id') {
    router.push({
      name: 'clients-id',
      params: {
        id: item.value.contract.client_id,
      },
      query: {
        contract_id: newContractId,
      },
    })

    return
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
      sum += asInt(x.lessons) * asInt(x.price)
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

    const lastPriceId = program.prices[program.prices.length - 1].id

    for (const price of program.prices) {
      const isLast = price.id === lastPriceId
      const lessons = asInt(price.lessons)
      const lessonsConducted = (lessonsConductedLeft - lessons) < 0
        ? lessonsConductedLeft
        : (isLast ? lessonsConductedLeft : lessons)

      let lessonsToBeConducted = 0

      if (lessonsToBeConductedLeft) {
        const x = lessons - lessonsConducted
        lessonsToBeConducted = isLast ? lessonsToBeConductedLeft : Math.min(x, lessonsToBeConductedLeft)
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

function splitPrices() {
  for (const p of item.value.programs) {
    const clientLessonPrices = p.client_lesson_prices
    const prices = []
    let used = 0

    if (clientLessonPrices.length > 0) {
      let currentPrice = clientLessonPrices[0]
      let count = 1

      for (let i = 1; i < clientLessonPrices.length; i++) {
        const price = clientLessonPrices[i]
        if (price === currentPrice) {
          count++
        }
        else {
          prices.push({
            id: newId(),
            lessons: count,
            price: currentPrice,
          })
          currentPrice = price
          used += count
          count = 1
        }
      }

      // Последний блок
      prices.push({
        id: newId(),
        lessons: count,
        price: currentPrice,
      })
      used += count
    }

    // Плейсхолдер для ввода новой цены
    // Если осталось что добавлять
    const lessons = p.lessons_suggest - used
    if (lessons > 0) {
      prices.push({
        id: newId(),
        lessons,
        price: '',
      })
    }

    p.prices = prices
  }
}

function getCorrectedLessons(program: ContractVersionProgramResource) {
  // все cvpp, кроме последней
  const allButLast = program.prices.slice(0, program.prices.length - 1)
  const lessonsFromPricesSum = allButLast.reduce((carry, x) => asInt(x.lessons) + carry, 0)
  return program.lessons_suggest - lessonsFromPricesSum
}

function isLessonsError(price: ContractVersionProgramPrice, program: ContractVersionProgramResource) {
  // нет группы и проведено 0 занятий
  if ((!program.group_id && !program.lessons_conducted)) {
    return false
  }
  if (program.prices.some(p => !p.lessons)) {
    return false
  }
  const lessonsSum = program.prices.reduce((carry, x) => asInt(x.lessons) + carry, 0)
  return price.id === program.prices[program.prices.length - 1].id
    && lessonsSum !== program.lessons_suggest
}

function isPriceError(price: ContractVersionProgramPrice, program: ContractVersionProgramResource) {
  // if (program.prices.some(p => p.price === '')) {
  //   return false
  // }
  let skip = 0
  for (const p of program.prices) {
    const lessons = asInt(p.lessons)
    const currentPrices = [...new Set(program.client_lesson_prices.slice(skip, lessons))]
    if (currentPrices.length === 0) {
      return false
    }
    if (p.id === price.id) {
      const isCorrectPrice = currentPrices.length === 1 && currentPrices[0] === asInt(price.price)
      return !isCorrectPrice
    }
    skip += lessons
  }
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

function isChanged(p: ContractVersionProgramResource, field: keyof ContractVersionProgramResource): boolean {
  if (!selectedDraft.value) {
    return false
  }

  const originalProgram = item.value.programs_original.find(e => e.program === p.program)

  if (field === 'group_id') {
    if (!applyMoveGroups.value) {
      return false
    }

    return (originalProgram?.group_id || undefined) !== (p.group_id || undefined)
  }

  if (field === 'program') {
    if (!originalProgram) {
      // программа добавлена
      return true
    }
    return false
  }

  if (field === 'lessons_planned') {
    if (!originalProgram) {
      return true
    }
    const value = Number.parseInt(p.lessons_planned as unknown as string)
    return originalProgram.lessons_planned !== value
  }

  // prices.lessons
  if (field === 'prices') {
    if (!originalProgram) {
      return true
    }
    const value = Number.parseInt(p.prices[p.prices.length - 1].lessons as unknown as string)
    return originalProgram.prices[originalProgram.prices.length - 1].lessons !== value
  }

  return false
}

defineExpose({ edit, newContract, newVersion, fromDraft })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div class="dialog-wrapper contract-version-dialog">
      <div class="dialog-header">
        <div>
          <template v-if="mode === 'new-contract'">
            Новый договор
          </template>
          <template v-else-if="mode === 'edit'">
            Редактирование договора
            <span>№{{ contractId }}–{{ seq }}</span>
            <div class="dialog-subheader">
              <template v-if="item.user && item.created_at">
                {{ formatName(item.user) }}
                {{ formatDateTime(item.created_at) }}
              </template>
            </div>
          </template>
          <template v-else>
            Новая версия договора
            <span>№{{ contractId }}–{{ seq }}</span>
          </template>
          <div v-if="selectedDraft" class="dialog-subheader">
            <template v-if="selectedDraft.id > 0">
              На основе
              <RouterLink
                target="_blank"
                :to="{
                  name: 'schedule-drafts-editor',
                  query: { id: selectedDraft.id },
                }"
              >
                проекта №{{ selectedDraft.id }}
              </RouterLink>
              от {{ formatDateTime(selectedDraft.created_at) }}
            </template>
            <template v-else>
              На основе текущего проекта
            </template>
          </div>
        </div>

        <div>
          <CrudDeleteBtn
            v-if="mode === 'edit'"
            :id="item.id"
            api-url="contract-versions"
            confirm-text="Вы уверены, что хотите удалить договор?"
            @deleted="dialog = false"
          />

          <v-btn v-if="mode !== 'new-contract'" :icon="mdiFlipVertical" variant="text" :size="48" @click="splitPrices()" />
          <v-menu v-if="mode === 'edit'">
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
          <v-btn
            icon="$save"
            :size="48"
            variant="text"
            :loading="saving"
            :disabled="![1, 5].includes(user!.id) && !isPaymentSumValid"
            @click="save()"
          />
        </div>
      </div>
      <div class="dialog-body">
        <v-fade-transition>
          <UiLoader v-if="loading" fixed :offset="64" />
        </v-fade-transition>
        <div class="double-input">
          <v-select
            v-model="item.contract.year"
            label="Учебный год"
            :items="selectItems(YearLabel)"
            :disabled="mode !== 'new-contract'"
          />
          <UiClearableSelect
            v-model="item.contract.company"
            label="Компания"
            :disabled="mode !== 'new-contract'"
            :items="selectItems(CompanyLabel)"
          />

          <div class="contract-version-dialog__sum">
            <v-text-field
              v-model="item.sum"
              label="Сумма, руб."
              type="number"
              hide-spin-buttons
            />
            <a v-if="lessonsMultipliedByPriceSum" class="date-input__today cursor-default">
              <span class="text-black">
                рекоменд.:
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
                  группа
                </th>
                <th width="117">
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
                <td :class="{ changed: isChanged(p, 'program') }">
                  <div class="d-flex align-center ga-1">
                    <span>
                      {{ ProgramLabel[p.program] }}
                    </span>
                  </div>
                </td>
                <td :class="{ changed: isChanged(p, 'group_id') }">
                  <!-- если подгрузили проект, то показываем оригинальную программу unless отмечено applyMoveGroups -->
                  <template v-if="selectedDraft">
                    <template v-if="applyMoveGroups">
                      <span v-if="p.group_id" class="pl-4">
                        ГР-{{ p.group_id }}
                      </span>
                    </template>
                    <span v-else-if="item.programs_original.some(e => e.program === p.program && e.group_id)" class="pl-4">
                      ГР-{{ item.programs_original.find(e => e.program === p.program)?.group_id }}
                    </span>
                  </template>
                  <span v-else-if="p.group_id" class="pl-4">
                    ГР-{{ p.group_id }}
                  </span>
                </td>
                <td :class="{ changed: isChanged(p, 'lessons_planned') }">
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
                      :class="{
                        'changed': price.id === p.prices[p.prices.length - 1].id && isChanged(p, 'prices'),
                        'text-error': isLessonsError(price, p),
                      }"
                    >
                      <template v-if="isLessonsError(price, p)" #append>
                        <div class="pr-4 text-gray">
                          {{ getCorrectedLessons(p) }}
                        </div>
                      </template>
                    </v-text-field>
                  </div>
                </td>
                <td>
                  <div v-for="price in p.prices" :key="price.id">
                    <span v-if="lessonsConducted[p.id][price.id].lessons_conducted > 0">
                      {{ lessonsConducted[p.id][price.id].lessons_conducted }}
                    </span>
                    <!-- <span v-if="lessonsConducted[p.id][price.id].lessons_to_be_conducted" class="text-gray pl-1">
                      + {{ lessonsConducted[p.id][price.id].lessons_to_be_conducted }}
                    </span> -->
                    <!--                    <template v-if="lessonsConducted[p.id][price.id].lessons_suggest > 0"> -->
                    <!--                      <v-icon :icon="mdiArrowRightThin" color="gray" :size="18" /> -->
                    <!--                      {{ lessonsConducted[p.id][price.id].lessons_suggest }} -->
                    <!--                    </template> -->
                  </div>
                </td>
                <td>
                  <div v-for="price in p.prices" :key="price.id">
                    <v-text-field
                      v-model="price.price"
                      type="number"
                      hide-spin-buttons
                      density="compact"
                      :class="{ 'text-error': isPriceError(price, p) }"
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
                  >
                    <template #activator="{ props }">
                      <a class="d-flex align-center ga-1" v-bind="props">
                        добавить
                        <v-icon icon="$expand" :size="16" />
                      </a>
                    </template>
                  </ProgramSelectorMenu>
                </td>
                <td></td>
                <td class="cursor-default">
                  {{ lessonsPlannedSum || '' }}
                </td>
                <td class="cursor-default">
                  {{ lessonsSum || '' }}
                </td>
                <td class="cursor-default text-gray">
                  {{ lessonsConductedSum || '' }}
                </td>
                <td class="cursor-default">
                  {{ lessonsMultipliedByPriceSum || '' }}
                </td>
                <td />
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="selectedDraft">
          <v-checkbox v-model="applyMoveGroups" label="Применить изменения в группах" />
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
  thead {
    th:last-child {
      border-right: none !important;
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
          td:first-child,
          td:nth-child(2) {
            padding-top: 13px;
          }
        }
        td {
          vertical-align: top;
          &:nth-child(3),
          &:nth-child(4),
          &:nth-child(5),
          &:nth-child(6) {
            & > div {
              &:not(:last-child) {
                border-bottom: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
              }
            }
          }
          &:nth-child(5) {
            & > div {
              cursor: default;
              display: flex;
              height: 51px;
              align-items: center;
              padding-left: 16px;
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
