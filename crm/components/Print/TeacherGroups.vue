<script setup lang="ts">
import type { PrintDialog } from '#build/components'
import type { PrintOption } from '.'

const { dialog, width, transition } = useDialog('default')
const printDialog = ref<InstanceType<typeof PrintDialog>>()

interface Params {
  teacher_id: number
  date: string | null
}

const validated = ref(false)

const printOption: PrintOption = {
  id: 21,
  label: 'Договор на преподавателя',
  company: 'ano',
}

const params = ref<Params>({
  teacher_id: -1,
  date: null,
})

const input = ref()

function open(teacherId: number) {
  params.value = {
    teacher_id: teacherId,
    date: null,
  }
  dialog.value = true
  nextTick(() => input.value?.focus())
}

function onSubmit() {
  setTimeout(() => {
    if (validated.value) {
      printDialog.value?.open(printOption, params.value, true)
    }
  }, 50)
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width" :transition="transition">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <div>
          {{ printOption.label }}
        </div>
        <v-btn
          :size="48"
          icon="$print"
          variant="text"
          type="submit"
          form="form"
        />
      </div>
      <v-form id="form" v-model="validated" validate-on="submit" class="dialog-body" @submit.prevent="onSubmit()">
        <div>
          <UiDateInput
            v-model="params.date"
            label="Дата"
          />
        </div>
      </v-form>
    </div>
  </v-dialog>
  <LazyPrintDialog ref="printDialog" />
</template>
