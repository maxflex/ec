<script setup lang="ts">
import { mdiCar } from '@mdi/js'
import type { ClientDialog, PassDialog, RequestDialog } from '#build/components'

const route = useRoute()
const model = defineModel<RequestListResource[]>({ default: () => [] })
const requestDialog = ref<null | InstanceType<typeof RequestDialog>>()
const clientDialog = ref<InstanceType<typeof ClientDialog>>()
const passDialog = ref<InstanceType<typeof PassDialog>>()
const showClient = route.name !== 'clients-id'

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

function onClientCreated(c: ClientListResource, requestId?: number) {
  if (!requestId) {
    return
  }
  const index = model.value.findIndex(e => e.id === requestId)
  if (index !== -1) {
    model.value[index].client = c
  }
  itemUpdated('request', requestId)
}

function onPassCreated(pass: PassResource) {
  const index = model.value.findIndex(e => e.id === pass.request_id)
  if (index === -1) {
    return
  }
  model.value[index].passes.push(pass)
  itemUpdated('request', pass.request_id!)
}

function onPassDeleted(pass: PassResource) {
  const index = model.value.findIndex(e => e.id === pass.request_id)
  if (index === -1) {
    return
  }
  const passIndex = model.value[index].passes.findIndex(e => e.id === pass.id)
  if (passIndex === -1) {
    return
  }
  model.value[index].passes.splice(passIndex, 1)
  itemUpdated('request', pass.request_id!)
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
      <div style="width: 100px">
        <div class="d-flex align-center ga-2">
          <RequestStatus :status="item.status" />
          {{ item.id }}
        </div>
      </div>
      <div style="width: 210px">
        <template v-if="item.responsible_user">
          {{ formatName(item.responsible_user) }}
        </template>
        <span v-else class="text-gray">
          нет ответственного
        </span>
      </div>
      <div style="width: 160px">
        <span v-if="item.program">
          {{ ProgramShortLabel[item.program] }}
        </span>
        <span v-else class="text-gray">
          не установлено
        </span>
      </div>
      <div style="width: 230px">
        <PhoneList :items="item.phones" />
      </div>
      <div v-if="showClient" style="width: 180px">
        <UiPerson v-if="item.client" :item="item.client" />
        <a v-else class="cursor-pointer" @click="clientDialog?.create(item.id)">добавить клиента</a>
      </div>
      <div class="request-list__actions">
        <CommentBtn
          :class="{ 'no-items': item.comments_count === 0 }"
          :count="item.comments_count"
          :entity-id="item.id"
          entity-type="request"
        />
        <div
          class="request-list__passes"
          @click="passDialog?.create(item.id)"
        >
          <v-btn
            :size="48"
            variant="plain"
            :icon="mdiCar"
            :class="{ 'no-items': item.passes.length === 0 }"
          />
          <v-badge
            v-if="item.passes.length > 0"
            floating
            :content="item.passes.length"
          />
        </div>
      </div>
      <div class="text-gray" style="flex: initial; width: 80px">
        {{ formatDate(item.created_at!) }}
      </div>
    </div>
  </div>
  <RequestDialog
    ref="requestDialog"
    @updated="onRequestUpdated"
    @deleted="onRequestDeleted"
  />
  <ClientDialog ref="clientDialog" @created="onClientCreated" />
  <PassDialog
    ref="passDialog"
    @created="onPassCreated"
    @deleted="onPassDeleted"
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
    gap: 6px;
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
