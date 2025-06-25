<script setup lang="ts">
const { data, sticky } = defineProps<{
  data: IndexPageData
  sticky?: boolean
}>()
</script>

<template>
  <UiFilters
    v-if="$slots.filters || $slots.buttons"
    :sticky="sticky"
    :class="$attrs.class ? `${$attrs.class}__filters` : undefined"
  >
    <slot name="filters" />
    <template #buttons>
      <slot name="buttons" />
    </template>
  </UiFilters>
  <slot v-if="$slots.header" name="header" />
  <div class="index-page" v-bind="$attrs">
    <v-fade-transition>
      <UiLoader v-if="data.loading" />
      <slot v-else-if="data.noData" name="no-data">
        <UiNoData />
      </slot>
      <div v-else class="index-page__content">
        <slot />
      </div>
    </v-fade-transition>
  </div>
</template>

<style lang="scss">
.index-page {
  flex: 1;
  position: relative;
}
</style>
