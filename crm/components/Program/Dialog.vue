<script setup lang="ts">
const emit = defineEmits<{ (e: 'saved', programs: Program[]): void }>()

const { dialog, width, transition } = useDialog('default')
const programs = ref<Program[]>([])
const selected = ref<Program[]>([])
const preSelected = ref<Program[]>([])

function open(preSelect: Program[] = []) {
  selected.value = [...preSelect]
  preSelected.value = [...preSelect]
  programs.value = Object.keys(ProgramLabel) as Program[]
  programs.value = programs.value.sort((a, b) => Number(preSelect.includes(b)) - Number(preSelect.includes(a)))
  dialog.value = true
}

function select(p: Program) {
  const index = selected.value.findIndex(e => e === p)
  index !== -1 ? selected.value.splice(index, 1) : selected.value.push(p)
}

function save() {
  dialog.value = false
  emit('saved', selected.value)
}

defineExpose({ open })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
    :transition="transition"
  >
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <span>
          Добавить программы
          <span
            v-if="selected.length"
            class="ml-1 text-gray"
          >
            {{ selected.length }}
          </span>
        </span>
        <v-btn
          icon="$save"
          :size="48"
          variant="text"
          @click="save()"
        />
      </div>
      <div class="dialog-body pt-0">
        <div class="table table--hover">
          <div
            v-for="p in programs"
            :key="p"
            class="cursor-pointer unselectable"
            :class="{ 'program-selector--disabled': preSelected.includes(p) }"
            @click="select(p)"
          >
            <v-icon
              v-if="selected.includes(p)"
              color="secondary"
              icon="$checkboxOn"
            />
            <v-icon
              v-else
              icon="$checkboxOff"
            />
            <span>
              {{ ProgramLabel[p] }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.program-selector {
  &--disabled {
    pointer-events: none;
    & > * {
      opacity: 0.3;
    }
  }
}
</style>
