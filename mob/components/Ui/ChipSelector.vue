<script setup lang="ts">
const { items, clearable, label = 'выберите' } = defineProps<{
  label: string
  clearable?: boolean
  items: Array<{
    title: string
    value: any
  }>
}>()

const model = defineModel()
const selectRef = ref<HTMLSelectElement | null>(null)

function openSelector() {
  if (selectRef.value?.showPicker) {
    selectRef.value.showPicker() // Opens instantly on iOS 15+
  }
  else {
    selectRef.value?.focus() // Fallback for older versions
  }
}

const computedModel = computed({
  get: () => model.value ?? null, // Convert `undefined` to `null` for select
  set: (val) => {
    model.value = val === null ? undefined : val // Convert `null` back to `undefined`
  },
})
</script>

<template>
  <v-chip class="chip-selector" label @pointerup="openSelector">
    {{ items.find(item => item.value === model)?.title || label }}
    <select ref="selectRef" v-model="computedModel" class="hidden">
      <template v-if="clearable">
        <option :value="null">
          не установлено
        </option>
        <!-- <hr> -->
      </template>
      <option v-for="item in items" :key="item.value" :value="item.value">
        {{ item.title }}
      </option>
    </select>
  </v-chip>
</template>

<style lang="scss">
.chip-selector {
  select {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
  }
}
</style>
