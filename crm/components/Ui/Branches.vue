<script setup lang="ts">
const { branches } = defineProps<{
  branches: Branch[] | null
}>()
</script>

<template>
  <div
    v-if="branches?.length"
    class="branches"
  >
    <span
      v-for="b in branches"
      :key="b"
      :class="`branch branch--${b}`"
    >
      {{ BranchLabel[b] }}
    </span>
  </div>
  <template v-else>
    не установлено
  </template>
</template>

<style lang="scss">
.branches {
  display: flex;
  gap: 4px;
  .branch:not(:last-child):after {
    color: rgba(0, 0, 0, 0.87);
    content: ', ';
  }
}
.branch {
  &--ohr {
    --color: #ef1e25;
  }
  &--pvn {
    --color: #ef1e25;
  }
  &--bgt {
    --color: #019ee0;
  }
  &--izm {
    --color: #0252a2;
  }
  &--opl {
    --color: #b61d8e;
  }
  &--rpt {
    --color: #b61d8e;
  }
  &--skl {
    --color: #029a55;
  }
  &--orh {
    --color: #029a55;
  }
  &--ann {
    --color: #a1a2a3;
  }
  &--per {
    --color: #ffd803;
  }
  &--klg {
    --color: #fbaa33;
  }
  &--brt {
    --color: #b1d332;
  }
  &--str {
    --color: #0252a2;
  }
  &--vld {
    --color: #acadaf;
  }
  &--bel {
    --color: #fbaa33;
  }
  &--bib {
    --color: #a1a2a3;
  }
  &--svi {
    --color: #fbaa33;
  }
  &--nag {
    --color: #a1a2a3;
  }
  &--sok {
    --color: #ef1e25;
  }
  &--pla {
    --color: #b61d8e;
  }
  &--vod {
    --color: #029a55;
  }
  &--trg {
    --color: #fbaa33;
  }
}
</style>
