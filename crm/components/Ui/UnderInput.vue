<template>
  <a class="ui-under-input">
    <slot>
      очистить
    </slot>
  </a>
</template>

<style lang="scss">
.ui-under-input {
  position: absolute;
  font-size: 12px;
  cursor: pointer;
  bottom: -16px;
  left: 16px;
}
</style>
