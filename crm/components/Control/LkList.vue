<script setup lang="ts">
import type { ControlLkResource } from '.'
import { ClientDialog } from '#components'

const { items } = defineProps<{
  items: ControlLkResource[]
}>()

const clientDialog = ref<InstanceType<typeof ClientDialog>>()
</script>

<template>
  <div class="control-lk table table--padding flex-start">
    <div v-for="item in items" :key="item.id" class="control-lk__item">
      <div class="control-lk__item-client">
        <div class="control-lk__name">
          <UiPerson :item="item" />
        </div>
        <div class="control-lk__phones">
          <PhoneList :items="item.phones" />
        </div>
        <div class="control-lk__last-seen-at">
          <UiLastSeenAt :item="item" />
        </div>
        <div class="control-lk__actions">
          {{ item.logs_count }} /
          <span class="text-secondary">
            {{ item.tg_logs_count }}
          </span>
        </div>
        <div class="control-lk__directions">
          <ClientDirections :item="item.directions" />
        </div>
      </div>
      <div class="control-lk__item-parent">
        <div class="control-lk__name">
          <UiPerson :item="item.parent" />
        </div>

        <div class="control-lk__phones">
          <PhoneList :items="item.parent.phones" />
        </div>
        <div class="control-lk__last-seen-at">
          <UiLastSeenAt :item="item.parent" />
        </div>
        <div class="control-lk__actions">
          {{ item.parent.logs_count }} /
          <span class="text-secondary">
            {{ item.parent.tg_logs_count }}
          </span>
        </div>
        <!-- <div class="control-lk__reports-read">
          {{ item.parent.reports_published_count }} /
          {{ item.parent.reports_read_count }}
        </div> -->
        <div>
          отчеты:
          {{ item.parent.reports_read_count }} /
          {{ item.parent.reports_published_count }}
        </div>
      </div>
      <div class="control-lk__comment">
        <CommentBtn
          :size="42"
          :class="{ 'no-items': item.comments_count === 0 }"
          :count="item.comments_count"
          :entity-id="item.id"
          :entity-type="EntityTypeValue.client"
        />
        <div class="vfn-1">
          <v-btn
            icon="$edit"
            :size="42"
            variant="plain"
            @click="clientDialog?.edit(item.id)"
          />
        </div>
      </div>
    </div>
  </div>
  <ClientDialog ref="clientDialog" />
</template>

<style lang="scss">
.control-lk {
  &__name {
    width: 200px;
  }
  &__last-seen-at {
    width: 140px;
  }
  &__phones {
    width: 220px;
  }
  &__actions {
    width: 120px;
  }
  &__reports-read {
    width: 60px;
    flex: 1;
  }
  &__header {
    & > div {
      color: rgb(var(--v-theme-gray));
    }
  }
  &__comment {
    display: flex;
    align-items: center;
    position: absolute;
    flex: initial !important;
    width: 44px;
    right: 68px;
    top: 16px;
    color: rgb(var(--v-theme-gray));
    gap: 10px;
  }
  &__item {
    flex-direction: column;
    & > div {
      display: flex;
    }
  }
}
</style>
