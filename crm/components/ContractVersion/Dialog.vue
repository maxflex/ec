<script setup lang="ts">
import { clone } from 'rambda'
import type { ProgramDialog } from '#build/components'

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
const programDialog = ref<null | InstanceType<typeof ProgramDialog>>()
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

function toggleCloseProgram(p: ContractProgramResource) {
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
        price: 0,
        lessons: 0,
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

function addPayment() {
  item.value.payments.push({
    id: newId(),
    date: today(),
    sum: 0,
    contract_version_id: item.value.id,
  })
  smoothScroll('dialog', 'bottom')
}

function deletePayment(p: ContractPaymentResource) {
  item.value.payments.splice(
    item.value.payments.findIndex(e => e.id === p.id),
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
        <span v-else-if="isEditMode">
          Редактирование договора
          <span class="text-gray">№{{ contractId }}–{{ version }}</span>
        </span>
        <span v-else>
          Новая версия договора
          <span class="text-gray">№{{ contractId }}–{{ version }}</span>
        </span>
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          :loading="saving"
          @click="save()"
        />
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
            :items="selectItems(CompanyTypeLabel)"
          />
        </div>
        <div class="double-input">
          <v-text-field
            v-model="item.sum"
            label="Сумма"
            type="number"
            hide-spin-buttons
          />
          <UiDateInput v-model="item.date" />
        </div>

        <div class="dialog-section">
          <!-- <div class="dialog-section__title">Программы</div> -->
          <div class="table contract-version-dialog__programs">
            <div class="table-header">
              <div>программа</div>
              <div>уроков</div>
              <div>прогр.</div>
              <div>цена</div>
            </div>
            <div
              v-for="p in item.programs"
              :key="p.id"
            >
              <div>
                <span
                  :class="{ 'text-error': p.is_closed }"
                  @click="toggleCloseProgram(p)"
                >
                  {{ ProgramLabel[p.program] }}
                </span>
              </div>
              <div>
                <v-text-field
                  v-model="p.lessons"
                  type="number"
                  hide-spin-buttons
                  density="compact"
                />
              </div>
              <div>
                <v-text-field
                  v-model="p.lessons_planned"
                  type="number"
                  hide-spin-buttons
                  density="compact"
                />
              </div>
              <div>
                <v-text-field
                  v-model="p.price"
                  type="number"
                  hide-spin-buttons
                  density="compact"
                />
              </div>
            </div>
            <div style="border-bottom: 0">
              <div>
                <a
                  class="link-icon"
                  @click="
                    programDialog?.open(item.programs.map((e) => e.program))
                  "
                >
                  выбрать программы
                  <v-icon
                    :size="16"
                    icon="$next"
                  />
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="dialog-section">
          <div class="dialog-section__title">
            График платежей
          </div>
          <div
            class="table table--actions-on-hover contract-version-dialog__payments"
          >
            <div class="table-header">
              <div style="width: 170px">
                дата
              </div>
              <div style="width: 100px">
                сумма
              </div>
              <div />
            </div>
            <div
              v-for="p in item.payments"
              :key="p.id"
            >
              <div style="width: 170px">
                <UiDateInput
                  v-model="p.date"
                  label=""
                />
              </div>
              <div style="width: 100px">
                <v-text-field
                  v-model="p.sum"
                  type="number"
                  hide-spin-buttons
                  density="compact"
                />
              </div>
              <div class="table-actions">
                <v-btn
                  icon="$close"
                  variant="plain"
                  color="red"
                  :size="48"
                  :ripple="false"
                  @click="deletePayment(p)"
                />
              </div>
            </div>
            <div style="border-bottom: 0">
              <a
                class="cursor-pointer"
                @click="addPayment()"
              >
                добавить платеж
              </a>
            </div>
          </div>
        </div>
        <div
          v-if="isEditMode"
          class="dialog-bottom"
        >
          <span v-if="item.user && item.created_at">
            договор создан
            {{ formatName(item.user) }}
            {{ formatDateTime(item.created_at) }}
          </span>
          <v-btn
            icon="$delete"
            :size="48"
            color="red"
            variant="plain"
            :loading="deleting"
            @click="destroy()"
          />
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
  &__programs {
    & > div {
      & > div {
        &:first-child {
          flex: 1;
          span {
            transition: color ease-in-out 0.15s;
            cursor: pointer;
            user-select: none;
          }
        }
        &:not(:first-child) {
          width: 80px;
          flex: none !important;
        }
      }
    }
  }
  .table {
    .v-input {
      margin-left: -16px;
    }
  }
}
</style>
