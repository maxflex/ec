<template>
  <div class="table-buttons">
    <slot />
  </div>
</template>

<style lang="scss">
.table-buttons {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  width: 100%;
}
</style>
