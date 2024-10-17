<script setup lang="ts">
import { mdiWeb } from '@mdi/js'
import type { RequestDialog } from '#build/components'

const router = useRouter()
const model = defineModel<RequestListResource[]>({ default: () => [] })
const requestDialog = ref<null | InstanceType<typeof RequestDialog>>()

function onRequestUpdated(r: RequestListResource) {
  const index = model.value.findIndex(e => e.id === r.id)
  if (index !== -1) {
    model.value[index] = r
  }
  else {
    model.value.unshift(r)
  }
  itemUpdated('request', r.id)
}

function onRequestDeleted(r: RequestResource) {
  const index = model.value.findIndex(e => e.id === r.id)
  if (index !== -1) {
    model.value.splice(index, 1)
  }
}
</script>

<template>
  <div class="table request-list">
    <div
      v-for="item in model"
      :id="`request-${item.id}`"
      :key="item.id"
    >
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click="requestDialog?.edit(item)"
        />
      </div>
      <div style="width: 80px">
        <div class="d-flex align-center ga-2">
          <RequestStatus :item="item" />
          {{ item.id }}
        </div>
      </div>
      <div style="width: 180px">
        <template v-if="item.responsible_user">
          {{ formatName(item.responsible_user, 'initials') }}
        </template>
        <span v-else class="text-gray">
          нет ответственного
        </span>
      </div>
      <div style="width: 150px">
        <span v-if="item.direction">
          {{ RequestDirectionLabel[item.direction] }}
        </span>
        <span v-else class="text-gray">
          не установлено
        </span>
      </div>
      <div style="width: 200px">
        <PhoneList :items="item.phones" :verified="item.is_verified" />
      </div>
      <div style="width: 180px">
        <UiPerson v-if="item.client" :item="item.client" />
        <span v-else-if="item.clients.length === 0" class="text-gray">
          нет клиента
        </span>
        <span v-else>
          <template v-if="item.clients.length === 1">
            {{ formatName(item.clients[0]) }}
          </template>
          <template v-else>
            {{ item.clients.length }} клиента
          </template>
        </span>
      </div>
      <div class="request-list__actions">
        <v-btn
          class="no-pointer-events"
          :size="48"
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
          class="request-list__passes"
          @click="router.push({ name: 'requests-id-passes', params: { id: item.id } })"
        >
          <v-btn
            :size="48"
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
      </div>
      <div class="text-gray" style="flex: initial; width: 144px">
        {{ formatDateTime(item.created_at!) }}
      </div>
    </div>
  </div>
  <RequestDialog
    ref="requestDialog"
    @updated="onRequestUpdated"
    @deleted="onRequestDeleted"
  />
</template>

<style lang="scss">
.request-list {
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
  &__passes {
    position: relative;
    .v-badge {
      position: absolute;
      right: 20px;
      top: 20px;
      cursor: pointer;
    }
  }
}
</style>
