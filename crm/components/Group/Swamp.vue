<script setup lang="ts">
const { swamp } = defineProps<{ swamp: ContractVersionProgramResource }>()

const emit = defineEmits<{
  (e: 'attach', swamp: ContractVersionProgramResource): void
}>()
</script>

<template>
  <div class="swamp-item">
    <div>
      <span
        v-if="swamp.is_closed"
        class="text-red"
      >расторгнут</span>
      <span
        v-else
        class="text-success"
      >к исполнению</span>
    </div>
    <div>
      {{ ProgramLabel[swamp.program] }}
    </div>
    <div>{{ swamp.lessons }} уроков</div>
    <div class="table-actions">
      <v-btn
        color="primary"
        @click="emit('attach', swamp)"
      >
        присвоить группу
      </v-btn>
    </div>
  </div>
</template>

<style lang="scss">
.swamp-item {
  & > div {
    &:nth-child(1) {
      width: 470px;
    }
    &:nth-child(2) {
      width: 350px;
    }
  }
}
</style>
