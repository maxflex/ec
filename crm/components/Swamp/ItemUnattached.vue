<script setup lang="ts">
import type { SwampListResource } from '.'

const { item } = defineProps<{ item: SwampListResource }>()
</script>

<template>
  <div class="text-gray">
    Нет группы
  </div>
  <div>
    <!-- teacher -->
  </div>
  <div>
    {{ ProgramShortLabel[item.program] }}
  </div>
  <div></div>
  <div></div>
  <div></div>
  <div></div>
  <div></div>
  <div>
    <ContractVersionProgramStatus :item="item" />
    <SwampChanges :item="item" />
  </div>
</template>
