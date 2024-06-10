<script setup lang="ts">
import { clone } from 'rambda'
import type { ProgramDialog } from '#build/components'

const emit = defineEmits<{ (e: 'updated', c: ContractResource): void }>()
const defaultContract: ContractResource = {
  id: newId(),
  client_id: 0,
  year: currentStudyYear(),
  company: 'ooo',
  versions: [],
  payments: [],
}
const defaultContractVersion: ContractVersionResource = {
  id: newId(),
  version: 1,
  contract_id: -1,
  date: today(),
  programs: [],
  payments: [],
}
const { dialog, width } = useDialog('medium')
const contract = ref<ContractResource>(defaultContract)
const i = ref(0) // versionIndex: selected ContractVersion index
const programDialog = ref<null | InstanceType<typeof ProgramDialog>>()
const saving = ref(false)
const destroying = ref(false)
const version = computed({
  get: () => contract.value.versions[i.value],
  set: v => contract.value.versions[i.value] = v,
})
const isEditMode = computed(() => version.value.id > 0)
const isNewContract = computed(() => contract.value.id < 0)

function create() {
  contract.value = {
    ...defaultContract,
    client_id: Number(useRoute().params.id), // допускаем, что client_id хранится в адресной строке
    versions: [{ ...defaultContractVersion }],
  }
  i.value = 0
  dialog.value = true
}

function editVersion(c: ContractResource, versionIndex: number) {
  i.value = versionIndex
  contract.value = clone(c)
  dialog.value = true
}

function addVersion(c: ContractResource) {
  contract.value = clone(c)
  const { sum, version, programs, payments } = c.versions[0]
  i.value = contract.value.versions.push({
    id: newId(),
    version,
    sum,
    programs: programs.map(e => ({
      ...e,
      id: newId(),
    })),
    payments: payments.map(e => ({
      ...e,
      id: newId(),
    })),
    contract_id: c.id,
    date: today(),
  }) - 1
  dialog.value = true
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить договор?')) {
    return
  }
  destroying.value = true
  const { status } = await useHttp(`contract-versions/${version.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    destroying.value = false
  }
  else {
    contract.value.versions.splice(i.value, 1)
    emit('updated', contract.value)
    dialog.value = false
    setTimeout(() => destroying.value = false, 300)
  }
}

function toggleCloseProgram(p: ContractProgramResource) {
  p.is_closed = !p.is_closed
}

function onProgramsSaved(programs: Program[]) {
  for (const program of programs) {
    const isNewProgram = !version.value.programs.some(p => p.program === program)
    if (isNewProgram) {
      contract.value.versions[i.value].programs.push({
        id: newId(),
        program,
        contract_version_id: contract.value.id,
        price: 0,
        lessons: 0,
        lessons_planned: 0,
        is_closed: false,
      })
    }
  }
  // Remove programs that are not in the new programs list
  contract.value.versions[i.value].programs = version.value.programs.filter(({ program }) =>
    programs.some(p => p === program),
  )
}

function addPayment() {
  contract.value.versions[i.value].payments.push({
    id: newId(),
    date: today(),
    sum: 0,
    contract_version_id: version.value.id,
  })
  smoothScroll('dialog', 'bottom')
}

function deletePayment(p: ContractPaymentResource) {
  contract.value.versions[i.value].payments.splice(
    version.value.payments.findIndex(e => e.id === p.id),
    1,
  )
}

async function storeOrUpdate() {
  saving.value = true
  if (isNewContract.value) {
    const { data } = await useHttp<ContractResource>(`contracts`, {
      method: 'post',
      body: contract.value,
    })
    if (data.value) {
      contract.value = data.value
    }
  }
  else if (isEditMode.value) {
    const { data } = await useHttp<ContractVersionResource>(`contract-versions/${version.value.id}`, {
      method: 'put',
      body: version.value,
    })
    if (data.value) {
      version.value = data.value
    }
  }
  else {
    const { data } = await useHttp<ContractVersionResource>('contract-versions', {
      method: 'post',
      body: version.value,
    })
    if (data.value) {
      contract.value?.versions.unshift(data.value)
    }
  }
  nextTick(() => emit('updated', contract.value))
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
}

defineExpose({ create, editVersion, addVersion })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div
      v-if="contract && version"
      class="dialog-wrapper contract-dialog"
    >
      <div class="dialog-header">
        <span v-if="isNewContract"> Новый договор </span>
        <span v-else-if="isEditMode"> Редактирование договора </span>
        <span v-else>Добавить версию</span>
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          :loading="saving"
          @click="storeOrUpdate()"
        />
      </div>
      <div class="dialog-body">
        <div class="double-input">
          <v-select
            v-model="contract.year"
            label="Учебный год"
            :items="selectItems(YearLabel)"
            :disabled="!isNewContract"
          />
          <v-select
            v-model="contract.company"
            label="Компания"
            :disabled="!isNewContract"
            :items="selectItems(CompanyTypeLabel)"
          />
        </div>
        <div class="double-input">
          <v-text-field
            v-model="version.sum"
            label="Сумма"
            type="number"
            hide-spin-buttons
          />
          <UiDateInput v-model="version.date" />
        </div>

        <div class="dialog-section">
          <!-- <div class="dialog-section__title">Программы</div> -->
          <div class="table contract-dialog__programs">
            <div class="table-header">
              <div>программа</div>
              <div style="width: 70px">
                уроков
              </div>
              <div style="width: 70px">
                прогр.
              </div>
              <div style="width: 70px; flex: none">
                цена
              </div>
            </div>
            <div
              v-for="p in version.programs"
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
              <div style="width: 70px">
                <v-text-field
                  v-model="p.lessons"
                  type="number"
                  hide-spin-buttons
                  density="compact"
                />
              </div>
              <div style="width: 70px">
                <v-text-field
                  v-model="p.lessons_planned"
                  type="number"
                  hide-spin-buttons
                  density="compact"
                />
              </div>
              <div style="width: 70px; flex: none">
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
                  @click="programDialog?.open(version.programs.map((e) => e.program))"
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
          <div class="table table--actions-on-hover contract-dialog__payments">
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
              v-for="p in version.payments"
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
          <span v-if="version.user && version.created_at">
            договор создан
            {{ formatName(version.user) }}
            {{ formatDateTime(version.created_at) }}
          </span>
          <v-btn
            icon="$delete"
            :size="48"
            color="red"
            variant="plain"
            :loading="destroying"
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
.contract-dialog {
  &__programs {
    & > div > div:first-child {
      flex: 1;
      span {
        transition: color ease-in-out 0.15s;
        cursor: pointer;
        user-select: none;
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
