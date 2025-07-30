<script setup lang="ts">
/**
 * TODO: переделать на v-model
 */
const { preSelected, noDisable, includePreSelected } = defineProps<{
  preSelected: Program[]
  noDisable?: boolean
  /**
   * Включить предвыбранные программы в emit (иначе отправляются только ново-выбранные программы)
   */
  includePreSelected?: boolean
}>()

const emit = defineEmits<{
  saved: [programs: Program[]]
}>()

const selected = ref<Program[]>([])
const menu = ref(false)

function select(p: Program) {
  const index = selected.value.findIndex(e => e === p)
  index !== -1 ? selected.value.splice(index, 1) : selected.value.push(p)
}

function save() {
  menu.value = false
  emit('saved', includePreSelected ? [...selected.value, ...preSelected] : selected.value)
}

watch(menu, (newVal) => {
  if (newVal) {
    selected.value = []
  }
})
</script>

<template>
  <v-menu
    v-model="menu"
    activator="parent"
    submenu
    :close-on-content-click="false"
    :max-height="400"
    :width="350"
  >
    <v-list class="pb-0">
      <v-list-item
        v-for="(label, key) in ProgramLabel"
        :key="key"
        :class="{ 'opacity-3 no-pointer-events': !noDisable && preSelected.includes(key) }"
        @click="select(key)"
      >
        <div class="d-flex align-center ga-4">
          <UiCheckbox :value="preSelected.includes(key) || selected.includes(key)" />
          <span>
            {{ label }}
          </span>
        </div>
      </v-list-item>
      <div class="program-selector-nested__btn">
        <v-btn color="primary" block :disabled="selected.length === 0" @click="save()">
          добавить
        </v-btn>
      </div>
    </v-list>
  </v-menu>
</template>

<style lang="scss">
.program-selector-nested {
  cursor: pointer;
  &--active {
    .v-icon {
      transform: rotate(-180deg);
    }
  }
  &__btn {
    position: sticky;
    bottom: 0;
    background: white;
    padding: 0 16px 10px;
    box-shadow: 0 0 10px 10px white;
  }
}
</style>
