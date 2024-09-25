<script setup lang="ts">
const model = defineModel<any[]>({ required: true })
</script>

<template>
  <v-select
    v-bind="$attrs"
    v-model="model"
    multiple
    :hide-details="!('presets' in $slots)"
    messages="123"
    class="multiple-select"
  >
    <template
      v-if="model.length > 1"
      #selection="{ index }"
    >
      <template v-if="index === 0">
        выбрано: {{ model.length }}
      </template>
    </template>
    <template v-if="'presets' in $slots" #message>
      <slot name="presets" />
    </template>
  </v-select>
</template>

<style lang="scss">
.multiple-select {
  position: relative;
  .v-messages {
    display: flex;
    gap: 8px;
    overflow: scroll;
    white-space: nowrap;
    padding-left: 16px;
    opacity: 1 !important;
    a {
      cursor: pointer;
      user-select: none;
    }
    &::-webkit-scrollbar {
      display: none;
    }
  }
  .v-input__details {
    position: absolute;
    bottom: -18px;
    width: 100%;
    padding: 0 !important;
    min-height: auto !important;
    &:before,
    &:after {
      content: '';
      position: absolute;
      top: 0;
      bottom: 0;
      z-index: 1;
    }
    &:before {
      left: 0;
      width: 16px;
      background: linear-gradient(
        to right,
        rgb(var(--v-theme-bg)),
        transparent
      );
    }
    &:after {
      right: 0;
      width: 16px;
      background: linear-gradient(to left, rgb(var(--v-theme-bg)), transparent);
    }
  }
}
</style>
