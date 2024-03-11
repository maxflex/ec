<script setup lang="ts">
import { cloneDeep } from "lodash"
import type { Program, Test } from "~/utils/models"
import { PROGRAM } from "~/utils/sment"

const { dialog, width } = useDialog()
const item = ref<Test>()
const input = ref()
const loading = ref(false)
const programs = Object.keys(PROGRAM).map((value) => ({
  value,
  title: PROGRAM[value as Program],
}))
const emit = defineEmits<{
  (e: "added"): void
}>()

function open(t: Test) {
  item.value = cloneDeep(t)
  dialog.value = true
}

function create() {
  item.value = {
    id: -1,
    minutes: 30,
    name: "",
    file: "",
    program: null,
    answers: null,
    results: null,
    created_at: null,
    updated_at: null,
  }
  dialog.value = true
  nextTick(() => {
    input.value.reset()
    input.value.focus()
  })
}

async function storeOrUpdate() {
  console.log("SMENT?")
  loading.value = true
  await useHttp("tests", {
    method: "POST",
    body: item.value,
  })
  dialog.value = false
  loading.value = false
  emit("added")
}

defineExpose({ open, create })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-content" v-if="item">
      <div class="dialog-header">
        <span v-if="item.id > 0"> Редактирование теста </span>
        <span v-else> Добавить тест </span>
        <v-btn
          icon="$save"
          :size="48"
          :loading="loading"
          variant="text"
          @click="storeOrUpdate()"
        />
      </div>
      <div class="dialog-body">
        <div>
          <v-text-field v-model="item.name" label="Название" ref="input" />
        </div>
        <div>
          <v-select
            :items="programs"
            v-model="item.program"
            label="Программа"
          />
        </div>
        <div>
          <v-text-field
            v-model="item.minutes"
            label="Минут на выполнение"
            type="number"
            hide-spin-buttons
          />
        </div>
        <div>
          <a class="link-icon" @click="() => console.log('sment')"
            >редактировать ответы
            <v-icon :size="16" icon="$next"></v-icon>
          </a>
        </div>
        <!-- <div class="dialog-section">
          <div class="dialog-section__title">График платежей</div>
        </div> -->
      </div>
    </div>
  </v-dialog>
</template>
