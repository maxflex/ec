<script setup lang="ts">
import type { CompanyType, Contract, ContractVersion } from "~/utils/models"
import { PROGRAM, YEARS, COMPANY_TYPE } from "~/utils/sment"

const dialog = ref(false)
const contract = ref<Contract>()
const version = ref<ContractVersion>()

function open(c: Contract, v: ContractVersion) {
  contract.value = { ...c }
  version.value = { ...v }
  dialog.value = true
}

function addProgram() {
  console.log("add")
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
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </div>
      <div class="dialog-body" v-if="contract && version">
        <v-select
          label="Учебный год"
          :items="
            YEARS.map((value) => ({
              value,
              title: formatYear(value),
            }))
          "
          v-model="contract.year"
          hide-details
        />
        <v-select
          label="Компания"
          :items="
          Object.keys(COMPANY_TYPE).map((value) => ({
            value,
            title: COMPANY_TYPE[value as CompanyType],
          }))
        "
          v-model="contract.company"
          hide-details
        />
        <UiDateInput v-model="version.date" />
        <v-text-field
          v-model="version.sum"
          label="Сумма"
          type="number"
          hide-spin-buttons
          hide-details
        />
        <!-- <div class="dialog-section">Программы</div> -->
        <div class="table">
          <div class="table-header">
            <div style="width: 240px"></div>
            <div style="width: 70px">уроков</div>
            <div style="width: 70px">прогр.</div>
            <div>цена</div>
          </div>
          <div v-for="p in version.programs" :key="p.id">
            <div style="width: 240px">
              {{ PROGRAM[p.program] }}
            </div>
            <div style="width: 70px">
              {{ p.lessons }}
            </div>
            <div style="width: 70px">
              {{ p.lessons_planned }}
            </div>
            <div>
              {{ p.price }}
            </div>
            <div class="table-actions">
              <v-btn icon :size="48">
                <v-icon>mdi-dots-horizontal</v-icon>
              </v-btn>
            </div>
          </div>
          <div style="border-bottom: 0">
            <div>
              <a
                class="cursor-pointer"
                @click="addProgram()"
                style="cursor: pointer"
              >
                добавить программу
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
</template>
