<template>
  <v-tooltip bottom :width="440">
    <template #activator="{ props }">
      <div class="question-tooltip" v-bind="props">
        ?
      </div>
    </template>
    <slot />
  </v-tooltip>
</template>

<style lang="scss">
.question-tooltip {
  $size: 44px;
  height: $size;
  width: $size;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 2px solid rgb(var(--v-theme-border));
  border-radius: 24px;
  color: rgb(var(--v-theme-gray));
  font-weight: bold;
  user-select: none;
  transition: background-color ease-in-out 0.1s;
  &:hover {
    background: rgba(var(--v-theme-gray), 0.1);
  }
}
</style>
