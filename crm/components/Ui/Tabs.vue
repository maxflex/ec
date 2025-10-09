<script setup lang="ts" generic="T extends Record<string, string>">
type Tab = keyof T
type TabCounts = Partial<Record<Tab, number>>

const {
  items,
  available,
  showZero,
  counts = {} as TabCounts,
  countsExtra = {} as TabCounts,
} = defineProps<{
  items: T
  /**
   * Доступные вкладки (если доступны не всегда все)
   */
  available?: Tab[]
  /**
   * Каунтеры на вкладках
   */
  counts?: TabCounts
  /**
   * +/- к основным каунтерам
   */
  countsExtra?: TabCounts

  showZero?: boolean
}>()

const model = defineModel<Tab>()

function isTabAvailable(tab: Tab): boolean {
  // доступны все вкладки
  if (available === undefined) {
    return true
  }

  return available.includes(tab)
}
</script>

<template>
  <div class="tabs">
    <template v-for="(label, key) in items" :key="key">
      <div
        v-if="isTabAvailable(key)"
        class="tabs-item"
        :class="{ 'tabs-item--active': model === key }"
        @click="model = key"
      >
        {{ label + (key in counts && (showZero || counts[key]) ? ':' : '') }}
        <span v-if="key in counts && (showZero || counts[key])">
          {{ counts[key] }}
        </span>
        <span
          v-if="countsExtra[key]"
          class="text-deepOrange"
        >
          <template v-if="countsExtra[key] > 0">
            + {{ countsExtra[key] }}
          </template>
          <template v-else>
            - {{ Math.abs(countsExtra[key]) }}
          </template>
        </span>
      </div>
    </template>
    <slot></slot>
  </div>
</template>

<style lang="scss">
.tabs {
  display: flex;
  border-bottom: 1px solid #e0e0e0;
  white-space: nowrap;
  overflow-x: scroll;

  &::-webkit-scrollbar {
    display: none;
  }
  // box-shadow: 0 0 10px 20px rgba(white, 0.5);
  &-item {
    padding: 12px 20px;
    cursor: pointer;
    transition: all cubic-bezier(0.4, 0, 0.2, 1) 0.2s;
    user-select: none;
    &:hover {
      background: #f6f6f6;
    }
    &--active {
      background: #e4e4e4 !important;
      // pointer-events: none;
    }
  }
}
</style>
