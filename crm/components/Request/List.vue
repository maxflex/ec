<script setup lang="ts">
import type { RequestDialog } from '#build/components'
import { PROGRAM } from '~/utils/sment'

const model = defineModel<RequestListResource[]>({ default: () => [] })
const requestDialog = ref<null | InstanceType<typeof RequestDialog>>()

function onRequestUpdated(r: RequestListResource) {
  const index = model.value.findIndex(m => m.id === r.id)
  if (index !== -1) {
    model.value.splice(index, 1, r)
  }
}
</script>

<template>
  <div class="request-list">
    <div
      v-for="r in model"
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
            {{ PROGRAM[r.program] }}
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
      </div>
      <div class="request__right">
        <div>{{ formatDateTime(r.created_at) }}</div>
        <div>
          <div v-if="r.client">
            Клиент:
            <NuxtLink :to="{ name: 'clients-id', params: { id: r.client.id } }">
              {{ formatName(r.client) }}
            </NuxtLink>
          </div>
          <div v-else>
            <a href="#">добавить клиента</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <RequestDialog
    ref="requestDialog"
    @updated="onRequestUpdated"
  />
</template>

<style lang="scss">
.request {
  background: #fff;
  border: 1px solid #e0e0e0;
  border-left: none;
  border-right: none;
  margin-bottom: 24px;
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
}
.request-list {
  background: #fafafa;
  & > .request {
    &:first-child {
      border-top: none;
      // margin-top: 24px;
    }
  }
}
</style>
