<script setup lang="ts">
const { data } = defineProps<{ data: IndexPageData }>()
</script>

<template>
  <UiFilters v-if="$slots.filters || $slots.buttons">
    <slot name="filters" />
    <template #buttons>
      <slot name="buttons" />
    </template>
  </UiFilters>
  <div class="index-page" v-bind="$attrs">
    <v-fade-transition>
      <UiLoader v-if="data.loading" />
      <UiNoData v-else-if="data.noData" />
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
