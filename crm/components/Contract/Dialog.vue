<script setup lang="ts">
import type { ProgramDialog } from "#build/components"
import type { CompanyType, Contract, ContractVersion } from "~/utils/models"
import { PROGRAM, YEARS, COMPANY_TYPE, type Programs } from "~/utils/sment"
import { uniqueId } from "lodash"

const dialog = ref(false)
const contract = ref<Contract>()
const version = ref<ContractVersion>()
const programDialog = ref<null | InstanceType<typeof ProgramDialog>>()

function open(c: Contract, v: ContractVersion) {
  contract.value = { ...c }
  version.value = { ...v }
  dialog.value = true
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

defineExpose({ open })
</script>

<template>
  <v-dialog
    fullscreen
    v-model="dialog"
    transition="dialog-transition"
    content-class="dialog"
    :width="600"
  >
    <div class="dialog-content">
      <div class="dialog-header">
        <span> Редактирование договора </span>
        <v-btn icon :size="48" @click="dialog = false" color="#fafafa">
          <v-icon icon="$save"></v-icon>
        </v-btn>
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
        <!-- <div class="dialog-section">Программы</div> -->
        <div class="table contract-dialog__programs">
          <div class="table-header">
            <div class="flex-1-1"></div>
            <div style="width: 70px">уроков</div>
            <div style="width: 70px">прогр.</div>
            <div style="width: 70px; flex: none">цена</div>
          </div>
          <div v-for="p in version.programs" :key="p.id">
            <div class="flex-1-1">
              {{ PROGRAM[p.program] }}
            </div>
            <div style="width: 70px">
              <v-text-field
                v-model="p.lessons"
                type="number"
                hide-spin-buttons
                density="compact"
                :min="0"
                :max="99"
                pattern="[0-9]{2}"
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
                    programDialog?.open(version?.programs.map((e) => e.program))
                "
              >
                выбрать программы
                <v-icon :size="16" icon="$next"></v-icon>
              </a>
            </div>
          </div>
          <!-- <div>
            <div>
              <v-btn variant="text"> добавить </v-btn>
            </div>
          </div> -->
        </div>
        <!-- <v-btn color="secondary"> добавить программу </v-btn> -->
        <!-- <pre>{{ version.programs }}</pre> -->
      </div>
    </div>
  </v-dialog>
  <ProgramDialog ref="programDialog" @saved="onProgramsSaved" />
</template>

<style lang="scss">
.contract-dialog {
  &__programs {
    .v-input {
      margin-left: -16px;
    }
    .v-field__outline {
      --v-field-border-opacity: 0;
    }
  }
}
</style>
