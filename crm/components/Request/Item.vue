<script setup lang="ts">
import type { Request } from "~/utils/models"
import { PROGRAM } from "~/utils/sment"

const { item } = defineProps<{
  item: Request
}>()
</script>

<template>
  <div class="request">
    <div class="request__left">
      <div class="request__title">
        <div>
          <RequestStatus :status="item.status" />
          Заявка {{ item.id }}
        </div>
        <div v-if="item.program">
          {{ PROGRAM[item.program] }}
        </div>
        <div>
          ответственный
          {{
            item.responsible_user
              ? formatName(item.responsible_user)
              : "не установлен"
          }}
        </div>
      </div>
      <div v-if="item.comment">
        {{ item.comment }}
      </div>
      <table class="request__phones">
        <tr v-for="phone in item.phones">
          <td>
            <a :href="`tel:${phone.number}`">
              {{ formatPhone(phone) }}
            </a>
            <v-icon
              :size="16"
              color="secondary"
              v-if="phone.is_verified"
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
      <div>{{ formatDateTime(item.created_at) }}</div>
      <div>
        Клиенты:
        <div v-for="client in item.clients" :key="client.id">
          <NuxtLink :to="{ name: 'clients-id', params: { id: client.id } }">
            {{ formatName(client) }}
          </NuxtLink>
        </div>
        <div>
          <a href="#">добавить</a>
        </div>
      </div>
    </div>
  </div>
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
</style>
