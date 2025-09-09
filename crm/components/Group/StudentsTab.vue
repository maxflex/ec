<script setup lang="ts">
import type { GroupResource } from '.'

const { group } = defineProps<{ group: GroupResource }>()

const { items, indexPageData } = useIndex<ClientGroupResource>(
  `client-groups`,
  ref({}),
  {
    saveFilters: false,
    staticFilters: {
      group_id: group.id,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <div class="table table--actions-on-hover">
      <div
        v-for="(item, idx) in items"
        :key="item.id"
        :class="{
          'changed': item.draft_id,
          'students-tab--is-first-added': items[idx + 1]?.is_first_added,
        }"
      >
        <div style="width: 280px">
          <UiAvatar :item="item.client" :size="38" class="mr-4" />
          <UiPerson :item="item.client" />
        </div>
        <div>
          <TeethBar :items="item.teeth" :current="group.teeth" />
        </div>
        <div style="width: 240px" class="pl-6">
          <template v-if="item.draft_id">
            <template v-if="item.is_removed">
              <template v-if="item.group_id">
                уходит в
              </template>
              <template v-else>
                уходит из группы
              </template>
            </template>
            <template v-else>
              <template v-if="item.real_group_id && item.real_group_id !== item.group_id">
                переходит из <RouterLink
                  :to="{ name: 'groups-id', params: { id: item.real_group_id } }"
                >
                  ГР-{{ item.real_group_id }}
                </RouterLink>
              </template>
              <template v-else>
                добавлен в группу
              </template>
            </template>
            <RouterLink
              v-if="item.group_id && group.id !== item.group_id"
              :to="{ name: 'groups-id', params: { id: item.group_id } }"
            >
              ГР-{{ item.group_id }}
            </RouterLink>
            в проекте
            <RouterLink :to="{ name: 'schedule-drafts-editor', query: { id: item.draft_id } }">
              {{ item.draft_id }}
            </RouterLink>
          </template>
        </div>
      </div>
    </div>
  </UiIndexPage>
</template>

<style lang="scss">
.students-tab {
  &--is-first-added {
    border-bottom: 3px solid rgb(var(--v-theme-orange)) !important;
  }
}
</style>
