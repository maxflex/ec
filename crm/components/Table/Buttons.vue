<template>
  <div class="table-buttons">
    <slot />
  </div>
</template>

<style lang="scss">
.table-buttons {
  position: absolute;
  top: -18px;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  width: 100%;
}
</style>
