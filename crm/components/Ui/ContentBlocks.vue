<template>
  <div class="content-blocks">
    <slot />
  </div>
</template>

<style lang="scss">
.content-blocks {
  display: flex;
  flex-direction: column;
  gap: 30px;
  padding: 0 var(--padding);
  & > div {
    display: flex;
    flex-direction: column;
    gap: 14px;
    & > div {
      &:first-child {
        font-weight: bold;
        font-size: 28px;
      }
      &:last-child {
        // word-wrap: break-word;
        // white-space: pre-wrap;
        max-width: 900px;
      }
    }
  }
}
</style>
