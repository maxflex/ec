<script setup lang="ts">
const { items, disabled } = defineProps<{
  items: SelectItems
  disabled?: boolean
}>()
const model = defineModel<string | number | boolean>()
const selectedItem = computed(() => items.find(e => e.value === model.value))
</script>

<template>
  <v-menu :disabled="disabled">
    <template #activator="{ props }">
      <a
        v-bind="props"
        class="ui-dropdown"
        :class="{
          'ui-dropdown--disabled': disabled,
        }"
      >
        {{ selectedItem?.title }}
        <v-icon icon="$expand" />
      </a>
    </template>
    <v-list>
      <v-list-item
        v-for="i in items"
        :key="String(i.value)"
        @click="() => model = i.value"
      >
        {{ i.title }}
      </v-list-item>
    </v-list>
  </v-menu>
</template>

<style lang="scss">
.ui-dropdown {
  display: inline-flex;
  align-items: center;
  // color: rgb(var(--v-theme-secondary));
  cursor: pointer;

  .v-icon {
    transition: transform ease-in-out 0.2s;
    font-size: 1.2em;
    top: 1px;
  }

  &[aria-expanded='true'] {
    .v-icon {
      transform: rotate(-180deg);
    }
  }

  &--disabled {
    cursor: text;
    color: black !important;

    .v-icon {
      display: none;
    }
  }
}
</style>
