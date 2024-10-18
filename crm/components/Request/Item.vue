<script setup lang="ts">
import { mdiInbox, mdiWeb } from '@mdi/js'

const { item, expanding } = defineProps<{
  item: RequestListResource
  expanding?: boolean
}>()
const emit = defineEmits<{
  edit: [r: RequestListResource]
  expand: [r: RequestListResource]
}>()
const router = useRouter()
</script>

<template>
  <div :id="`request-${item.id}`">
    <div class="table-actionss">
      <v-btn
        icon="$edit"
        :size="48"
        variant="plain"
        @click="emit('edit', item)"
      />
    </div>
    <div style="width: 90px">
      <div class="d-flex align-center ga-2">
        <RequestStatus :item="item" />
        {{ item.id }}
      </div>
    </div>
    <div style="width: 140px">
      <template v-if="item.responsible_user">
        {{ formatName(item.responsible_user, 'initials') }}
      </template>
      <span v-else class="text-gray">
        отсутствует
      </span>
    </div>
    <div style="width: 130px">
      <span v-if="item.direction">
        {{ RequestDirectionLabel[item.direction] }}
      </span>
      <span v-else class="text-gray">
        отсутствует
      </span>
    </div>
    <div style="width: 300px">
      <PhoneList :items="item.phones" :verified="item.is_verified" />
    </div>
    <div style="width: 170px">
      <UiPerson v-if="item.client" :item="item.client" />
      <span v-else-if="item.clients.length === 0" class="text-gray">
        нет клиента
      </span>
      <span v-else>
        <template v-if="item.clients.length === 1">
          <UiPerson :item="item.clients[0]" class="text-black" />
        </template>
        <template v-else>
          {{ item.clients.length }} клиента
        </template>
      </span>
    </div>
    <div class="request-item__actions">
      <v-btn
        class="no-pointer-events"
        :size="42"
        variant="text"
        :icon="mdiWeb"
        :class="{ 'no-items': item.user_id }"
      />
      <CommentBtn
        :class="{ 'no-items': item.comments_count === 0 }"
        :count="item.comments_count"
        :entity-id="item.id"
        entity-type="request"
      />
      <div
        class="badge"
        @click="router.push({ name: 'requests-id-passes', params: { id: item.id } })"
      >
        <v-btn
          :size="42"
          variant="plain"
          icon="$pass"
          :class="{ 'no-items': item.passes.length === 0 }"
        />
        <v-badge
          v-if="item.passes.length > 0"
          floating
          :content="item.passes.length"
        />
      </div>
      <div class="badge" @click="emit('expand', item)">
        <v-btn
          :size="42"
          variant="plain"
          :icon="mdiInbox"
          :class="{ 'no-items no-pointer-events': item.associated_requests_count === 0 }"
          :loading="expanding"
        />
        <v-badge
          v-if="item.associated_requests_count > 0"
          floating
          :content="item.associated_requests_count"
        />
      </div>
    </div>
    <div class="text-gray" style="flex: initial; width: 80px">
      {{ formatDateAgo(item.created_at!) }}
    </div>
  </div>
</template>

<style lang="scss">
.request-item {
  &__actions {
    flex: 1;
    color: rgb(var(--v-theme-gray));
    width: 150px;
    display: flex;
    align-items: center;
    .no-items {
      &:not(:hover) {
        opacity: 0.2 !important;
      }
    }
  }
}
</style>
