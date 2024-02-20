<script setup lang="ts">
import type { ProgramDialog } from "#build/components"
import type {
  CompanyType,
  Contract,
  ContractPayment,
  ContractProgram,
  ContractVersion,
} from "~/utils/models"
import { PROGRAM, YEARS, COMPANY_TYPE, type Programs } from "~/utils/sment"
import { uniqueId, cloneDeep } from "lodash"

const { dialog, width } = useDialog(600)
const contract = ref<Contract>()
const version = ref<ContractVersion>()
const programDialog = ref<null | InstanceType<typeof ProgramDialog>>()

function open(c: Contract, v: ContractVersion) {
  contract.value = cloneDeep(c)
  version.value = cloneDeep(v)
  dialog.value = true
}

function toggleCloseProgram(p: ContractProgram) {
  p.is_closed = !p.is_closed
}

function onProgramsSaved(programs: Programs) {
  // typescript угомонить
  if (!contract.value || !version.value) {
    return
  }
  for (const program of programs) {
    const index = version.value?.programs.findIndex(
      (e) => e.program === program,
    )
    if (index === -1) {
      version.value.programs.push({
        id: parseInt(uniqueId()) * -1,
        program,
        contract_version_id: contract.value?.id as number,
        price: 0,
        lessons: 0,
        lessons_planned: 0,
        is_closed: false,
      })
    }
  }
  for (const cp of version.value.programs) {
    const index = programs.findIndex((p) => p === cp.program)
    if (index === -1) {
      version.value?.programs.splice(index, 1)
    }
  }
}

function addPayment() {
  version.value?.payments.push({
    id: parseInt(uniqueId()) * -1,
    date: today(),
    sum: 0,
    contract_version_id: version.value.id,
  })
  nextTick(() =>
    document
      .querySelector(".dialog-content")
      ?.scrollTo({ top: 9999, behavior: "smooth" }),
  )
}

function deletePayment(p: ContractPayment) {
  version.value?.payments.splice(
    version.value.payments.findIndex((e) => e.id === p.id),
    1,
  )
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-content">
      <div class="dialog-header">
        <span> Редактирование договора </span>
        <v-btn
          icon="$save"
          :size="48"
          @click="dialog = false"
          color="#fafafa"
        />
      </div>
      <div class="dialog-body" v-if="contract && version">
        <div class="double-input">
          <v-select
            label="Учебный год"
            :items="
              YEARS.map((value) => ({
                value,
                title: formatYear(value),
              }))
            "
            v-model="contract.year"
            disabled
          />
          <v-select
            label="Компания"
            disabled
            :items="
          Object.keys(COMPANY_TYPE).map((value) => ({
            value,
            title: COMPANY_TYPE[value as CompanyType],
          }))
        "
            v-model="contract.company"
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
              <div style="width: 70px">уроков</div>
              <div style="width: 70px">прогр.</div>
              <div style="width: 70px; flex: none">цена</div>
            </div>
            <div v-for="p in version.programs" :key="p.id">
              <div>
                <span
                  @click="toggleCloseProgram(p)"
                  :class="{ 'text-error': p.is_closed }"
                >
                  {{ PROGRAM[p.program] }}
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
                  @click="
                    () =>
                      programDialog?.open(
                        version?.programs.map((e) => e.program),
                      )
                  "
                >
                  выбрать программы
                  <v-icon :size="16" icon="$next"></v-icon>
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="dialog-section">
          <div class="dialog-section__title">График платежей</div>
          <div class="table table--actions-on-hover contract-dialog__payments">
            <div class="table-header">
              <div style="width: 170px">дата</div>
              <div style="width: 100px">сумма</div>
              <div></div>
            </div>
            <div v-for="p in version.payments" :key="p.id">
              <div style="width: 170px">
                {{ formatDate(p.date) }}
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
                >
                </v-btn>
              </div>
            </div>
            <div style="border-bottom: 0">
              <div>
                <a class="link-icon" @click="addPayment()">
                  добавить платеж
                  <v-icon :size="16" icon="$next"></v-icon>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </v-dialog>
  <ProgramDialog ref="programDialog" @saved="onProgramsSaved" />
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
}
</style>
