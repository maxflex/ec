<script setup lang="ts">
import { mdiContentCopy } from '@mdi/js'
import type { InstructionDialog, InstructionDiffDialog } from '#build/components'

const route = useRoute()
const instruction = ref<InstructionResource>()
const instructionDialog = ref<InstanceType<typeof InstructionDialog>>()
const instructionDiffDialog = ref<InstanceType<typeof InstructionDiffDialog>>()

async function loadData() {
  const { data } = await useHttp<InstructionResource>(`instructions/${route.params.id}`)
  instruction.value = data.value as InstructionResource
}

nextTick(loadData)
</script>

<template>
  <div v-if="instruction" class="instruction">
    <div class="instruction__content">
      <h1>
        {{ instruction.title }}
        <div>
          <v-btn
            variant="plain"
            :icon="mdiContentCopy"
            :size="48"
            :disabled="instruction.versions[0].id === instruction.id"
            @click="instructionDiffDialog?.open(instruction!)"
          />
          <v-btn
            variant="plain"
            icon="$edit"
            :size="48"
            @click="instructionDialog?.edit(instruction!)"
          />
        </div>
      </h1>
      <div class="ql-snow">
        <div class="ql-editor pa-0">
          <div v-html="instruction.text" />
        </div>
      </div>
    </div>
    <div class="instruction__panel">
      <div class="table table--padding table--hover">
        <RouterLink
          v-for="(v, index) in instruction.versions"
          :key="v.id"
          :class="{
            selected: v.id === instruction.id,
          }"
          :to="{
            name: 'instructions-id',
            params: { id: v.id },
          }"
          class="cursor-pointer table-item"
        >
          версия {{ index + 1 }}
          <div class="text-gray">
            создана {{ formatDateTime(v.created_at) }}
          </div>
        </RouterLink>
        <div>
          <a
            class="link-icon"
            @click="instructionDialog?.addVersion(instruction!)"
          >
            добавить версию
            <v-icon
              :size="16"
              icon="$next"
            />
          </a>
        </div>
      </div>
    </div>
  </div>
  <InstructionDialog ref="instructionDialog" @updated="i => (instruction = i)" />
  <InstructionDiffDialog ref="instructionDiffDialog" />
</template>

<style lang="scss">
.instruction {
  display: flex;
  min-height: 100vh;
  &__content {
    flex: 1;
    padding: 20px;
    margin-right: 255px; /* Ensure space for the fixed panel */
    & > h1 {
      margin-bottom: 36px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      & > div {
        display: inline-flex;
        .v-icon {
          font-size: 24px !important;
        }
      }
    }
  }
  &__panel {
    width: 255px;
    border-left: 1px solid #e0e0e0;
    position: fixed;
    right: 0;
    top: 0;
    bottom: 0;
    overflow-y: auto; /* Allow scrolling within the panel if needed */
    .table {
      & > a {
        display: block !important;
      }
      & > div {
        border-bottom: none !important;
        &:hover {
          background: none !important;
        }
      }
      .selected {
        background: rgba(
          var(--v-border-color),
          var(--v-hover-opacity)
        ) !important;
        pointer-events: none;
      }
    }
  }
}
</style>
