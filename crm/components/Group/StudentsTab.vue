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
      <div v-for="item in items" :key="item.id" :class="{ changed: item.draft_id }">
        <div style="width: 280px">
          <UiAvatar :item="item.client" :size="38" class="mr-4" />
          <UiPerson :item="item.client" />
        </div>
        <div>
          <TeethBar :items="item.teeth" :current="group.teeth" />
        </div>
        <div style="width: 240px" class="pl-6">
          <template v-if="item.draft_id">
            {{ item.is_removed ? 'удален' : 'добавлен' }} в
            <RouterLink :to="{ name: 'schedule-drafts-editor', query: { id: item.draft_id } }">
              проекте №{{ item.draft_id }}
            </RouterLink>
          </template>
        </div>
      </div>
    </div>
  </UiIndexPage>
</template>
