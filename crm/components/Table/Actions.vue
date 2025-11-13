<script setup lang="ts">
const emit = defineEmits(['click'])
</script>

<template>
  <div class="table-actions">
    <slot>
      <v-btn
        variant="plain"
        icon="$edit"
        :size="48"
        @click="emit('click')"
      />
    </slot>
  </div>
</template>

<style lang="scss">
.table-actions {
  position: absolute;
  padding-top: 4px;
  right: 15px;
  top: 0;
  z-index: 1;
  width: 100px;
  height: 100%;
  text-align: right;
  // overflow: hidden;
  // background: red;
  & > .badge,
  & > .v-btn {
    opacity: 0;
    box-shadow: 0 0 20px 20px rgba(white, 0.5);
    background: white;
    transform: translateX(10px);
    transition: all ease-in-out 0.2s;
    color: rgb(var(--v-theme-gray));
    // color:#bdc2ce;
    &:after {
      content: none !important;
    }
    &:hover {
      color: rgb(var(--v-theme-gray));
    }
    &:focus {
      opacity: 0;
    }
  }
  &:hover {
    & > .badge,
    & > .v-btn {
      transform: translateX(0);
      opacity: 1 !important;
    }
  }
}
</style>
