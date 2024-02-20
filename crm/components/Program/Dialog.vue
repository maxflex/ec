<script setup lang="ts">
import type { Program } from "~/utils/models"
import { PROGRAM, type Programs } from "~/utils/sment"

const emit = defineEmits<{
  (e: "saved", programs: Programs): void
}>()

const dialog = ref(false)
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

watch(dialog, (val) => {
  const query = ".v-dialog.v-overlay--active"
  if (val) {
    // secondLayer можно определить программно:
    // if (document.documentElement.classList.classList.contains("overflow-y-hidden"))
    document.querySelector(query)?.classList.add("dialog--second-layer")
  } else {
    document.querySelector(query)?.classList.remove("dialog--second-layer")
  }
})
</script>

<template>
  <v-dialog
    fullscreen
    v-model="dialog"
    transition="dialog-right-transition"
    content-class="dialog"
    :width="500"
  >
    <div class="dialog-content">
      <div class="dialog-header mb-0">
        <span>
          Выбор программ
          <span class="ml-1 text-gray" v-if="selected.length">
            {{ selected.length }}
          </span>
        </span>
        <v-btn icon="$save" :size="48" @click="save()" color="#fafafa" />
      </div>
      <div class="dialog-body">
        <div class="table table--hoverable">
          <div
            v-for="(title, p) in PROGRAM"
            :key="p"
            @click="select(p)"
            class="cursor-pointer unselectable"
          >
            <v-icon
              v-if="selected.includes(p)"
              color="primary"
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
