<script setup lang="ts">
import type { SwampListResource } from '.'
import { mdiArrowDownThin } from '@mdi/js'

const { items } = defineProps<{
  items: SwampListResource[]
}>()
</script>

<template>
  <div class="table table--padding swamp-list">
    <div v-for="item in items" :key="item.id">
      <!-- добавлен в группу по программе -->
      <SwampItemAttached v-if="item.group" :item="item" />

      <!-- не добавлен в группу программе -->
      <SwampItemUnattached v-else :item="item" />

      <template v-if="item.changes">
        <div class="swamp-list__schedule-draft">
          <RouterLink
            :to="{
              name: 'schedule-drafts-editor',
              query: {
                id: item.changes!.schedule_draft_id,
              },
            }"
          >
            <template v-if="item.changes!.type === 'added'">
              добавлен
            </template>
            <template v-else-if="item.changes!.type === 'changed'">
              перемещён
            </template>
            <template v-else>
              удалён
            </template>
            в проекте {{ item.changes!.schedule_draft_id }}
          </RouterLink>
          <div>
            <v-icon :icon="mdiArrowDownThin" :size="20" />
          </div>
        </div>
        <SwampItemAttached v-if="item.changes.group" class="changed" :item="item" changes />
        <SwampItemUnattached v-else :item="item" class="changed" />
      </template>
    </div>
  </div>
</template>

<style lang="scss">
.swamp-list {
  & > div {
    flex-direction: column;
    align-items: flex-start !important;
    padding: 0 !important;
    gap: 0 !important;

    & > div {
      display: flex;
      width: 100%;
      padding-top: 16px;
      padding-bottom: 16px;

      & > div {
        width: 100px;

        &:first-child {
          width: 130px;
          padding-left: 20px;
        }
        // teacher
        &:nth-child(2) {
          width: 200px;
        }
        // program
        &:nth-child(3) {
          width: 130px;
        }
        // first lesson date
        &:nth-child(4) {
          width: 100px;
        }
        // расписание
        &:nth-child(7) {
          width: 200px;
        }

        &:last-child {
          width: initial;
          flex: 1;
          padding-right: 20px;
        }
      }
    }
  }

  // &__changes {
  //   & > div:nth-child(2) {
  //     $color: #fcf0d4;
  //     background: $color;
  //   }
  // }

  &__schedule-draft {
    padding: 8px 0 16px !important;
    justify-content: center;
    align-items: center;
    flex-direction: column;
  }
}
</style>
