<script setup lang="ts">
import type { Program } from "~/utils/models"
import { PROGRAM, type Programs } from "~/utils/sment"

const emit = defineEmits<{
  (e: "saved", programs: Programs): void
}>()

const { dialog, width } = useDialog(500)
const selected = ref<Programs>([])

function open(preSelect: Programs = []) {
  dialog.value = true
  selected.value = [...preSelect]
}

function select(p: Program) {
  const index = selected.value.findIndex((e) => e === p)
  index !== -1 ? selected.value.splice(index, 1) : selected.value.push(p)
}

function save() {
  dialog.value = false
  emit("saved", selected.value)
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <span>
          Выбор программ
          <span class="ml-1 text-gray" v-if="selected.length">
            {{ selected.length }}
          </span>
        </span>
        <v-btn icon="$save" :size="48" @click="save()" color="#fafafa" />
      </div>
      <div class="dialog-body pt-0">
        <div class="table table--hover">
          <div
            v-for="(title, p) in PROGRAM"
            :key="p"
            @click="select(p)"
            class="cursor-pointer unselectable"
          >
            <v-icon
              v-if="selected.includes(p)"
              color="secondary"
              icon="$checkboxOn"
            />
            <v-icon v-else icon="$checkboxOff" />
            {{ title }}
          </div>
        </div>
      </div>
    </div>
  </v-dialog>
</template>
