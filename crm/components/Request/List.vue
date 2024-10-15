<script setup lang="ts">
import { mdiAccount, mdiCar } from '@mdi/js'
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
  <div class="request-list">
    <div
      v-for="r in model"
      :id="`request-${r.id}`"
      :key="r.id"
      class="request"
    >
      <div class="request__left">
        <div class="request__title">
          <div @click="requestDialog?.edit(r)">
            <RequestStatus :status="r.status" />
            Заявка {{ r.id }}
          </div>
          <div v-if="r.program">
            {{ ProgramLabel[r.program] }}
          </div>
          <div>
            ответственный
            {{
              r.responsible_user
                ? formatName(r.responsible_user)
                : "не установлен"
            }}
          </div>
        </div>
        <div v-if="r.comment">
          {{ r.comment }}
        </div>
        <table class="request__phones">
          <tr
            v-for="phone in r.phones"
            :key="phone.id"
          >
            <td>
              <a :href="`tel:${phone.number}`">
                {{ formatPhone(phone.number as string) }}
              </a>
              <v-icon
                v-if="phone.is_verified"
                :size="16"
                color="secondary"
                icon="$verified"
              />
            </td>
            <td>
              {{ phone.comment }}
            </td>
          </tr>
        </table>
        <template v-if="showClient">
          <div v-if="r.client">
            Клиент:
            <NuxtLink :to="{ name: 'clients-id', params: { id: r.client.id } }">
              {{ formatName(r.client) }}
            </NuxtLink>
          </div>
          <!--          <div v-else>
            <a class="cursor-pointer" >добавить клиента</a>
          </div> -->
        </template>
        <PassList2 v-if="r.passes.length" :items="r.passes" @edit="passDialog?.edit" />
      </div>
      <div class="request__right">
        <div>{{ formatDateTime(r.created_at) }}</div>
        <div class="request__actions">
          <CommentBtn
            :count="r.comments_count"
            :entity-id="r.id"
            entity-type="request"
          />

          <v-menu>
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                icon="$more"
                :size="48"
                variant="plain"
              />
            </template>
            <v-list>
              <v-list-item @click="requestDialog?.edit(r)">
                <template #prepend>
                  <v-icon icon="$edit" />
                </template>
                редактировать заявку
              </v-list-item>
              <v-list-item @click="clientDialog?.create(r.id)">
                <template #prepend>
                  <v-icon :icon="mdiAccount" />
                </template>
                добавить клиента
              </v-list-item>
              <v-list-item @click="passDialog?.create(r.id)">
                <template #prepend>
                  <v-icon :icon="mdiCar" />
                </template>
                добавить пропуск
              </v-list-item>
            </v-list>
          </v-menu>
        </div>
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
.request {
  background: #fff;
  border-bottom: 1px solid #e0e0e0;
  border-left: none;
  border-right: none;
  padding: 20px;
  display: flex;
  position: relative;
  &__left,
  &__right {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }
  &__left {
    width: 80%;
  }
  &__right {
    flex: 1;
    text-align: right;
    display: flex;
    justify-content: space-between;
    color: rgb(var(--v-theme-gray));
  }
  &__title {
    display: flex;
    align-items: center;
    width: 100%;
    gap: 18px;
    & > div {
      &:first-child {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        &:hover {
          color: black;
        }
      }
      &:not(:first-child) {
        color: rgb(var(--v-theme-gray));
      }
    }
  }
  &__phones {
    width: fit-content;
    .v-icon {
      top: -2px;
      position: relative;
      margin-left: 4px;
    }
    tr {
      td {
        &:first-child {
          padding-right: 10px;
        }
        &:last-child {
          color: rgb(var(--v-theme-gray));
        }
      }
    }
  }
  &__actions {
    display: flex;
    flex: 1;
    justify-content: flex-end;
    align-items: flex-end;
    position: relative;
    right: -10px;
    bottom: -10px;
  }
}
.request-list {
  background: #fafafa;
}
</style>
