<script setup lang="ts">
import type { ClientsBrowseResource } from '.'

const { items } = defineProps<{
  items: ClientsBrowseResource[]
}>()
</script>

<template>
  <div class="clients-browse table table--padding flex-start">
    <div class="clients-browse__header">
      <div class="clients-browse__name">
        ученик
      </div>
      <div class="clients-browse__phones">
        телефоны
      </div>
      <div class="clients-browse__last-seen-at">
        был онлайн
      </div>
      <div class="clients-browse__actions">
        действий всего
        / через TG
      </div>
      <div class="clients-browse__parent-name">
        представитель
      </div>
      <div class="clients-browse__phones">
        телефоны
      </div>
      <div class="clients-browse__last-seen-at">
        был онлайн
      </div>
      <div class="clients-browse__actions">
        действий всего
        / через TG
      </div>
      <div class="clients-browse__reports-read">
        отчетов всего / прочит.
      </div>
    </div>
    <div v-for="item in items" :key="item.id">
      <div class="table-actionss">
        <CommentBtn
          :size="42"
          :class="{ 'no-items': item.comments_count === 0 }"
          :count="item.comments_count"
          :entity-id="item.id"
          :entity-type="EntityTypeValue.client"
        />
      </div>
      <div class="clients-browse__name">
        <UiPerson :item="item" />
      </div>
      <div class="clients-browse__phones">
        <PhoneList :items="item.phones" />
      </div>
      <div class="clients-browse__last-seen-at">
        <UiLastSeenAt :item="item" />
      </div>
      <div class="clients-browse__actions">
        {{ item.logs_count }} /
        <span class="text-secondary">
          {{ item.tg_logs_count }}
        </span>
      </div>
      <div class="clients-browse__parent-name">
        <UiPerson :item="item.parent" />
      </div>
      <div class="clients-browse__phones">
        <PhoneList :items="item.parent.phones" />
      </div>
      <div class="clients-browse__last-seen-at">
        <UiLastSeenAt :item="item.parent" />
      </div>
      <div class="clients-browse__actions">
        {{ item.parent.logs_count }} /
        <span class="text-secondary">
          {{ item.parent.tg_logs_count }}
        </span>
      </div>
      <div class="clients-browse__reports-read">
        {{ item.parent.reports_read_count }} / {{ item.parent.reports_published_count }}
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.clients-browse {
  &__name {
    width: 180px;
  }
  &__parent-name {
    padding-left: 50px;
    flex: 1;
    // width: 200px;
  }
  &__last-seen-at {
    width: 80px;
  }
  &__phones {
    width: 150px;
  }
  &__actions {
    width: 70px;
  }
  &__reports-read {
    width: 60px;
    flex: initial !important;
  }
  &__header {
    & > div {
      color: rgb(var(--v-theme-gray));
    }
  }
}
</style>
