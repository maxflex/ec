<script setup lang="ts">
import type { RequestListResource } from '../Request'
import { mdiChevronRight, mdiClipboardOutline, mdiHistory, mdiPhoneOutgoing } from '@mdi/js'

const { items, request, showComment, person } = defineProps<{
  items: PhoneResource[]
  request?: RequestListResource
  person?: PersonResource
  showComment?: boolean
}>()
</script>

<template>
  <div class="phone-list">
    <div v-for="item in items" :key="item.id">
      <!-- transition="slide-y-transition" -->
      <v-menu
        location="bottom center"
        offset="10"
      >
        <template #activator="{ props }">
          <PhoneNumber
            :item="item"
            class="phone-list__number"
            :request="request"
            v-bind="props"
          />
        </template>
        <v-list class="phone-list__list">
          <v-list-item>
            <v-icon :icon="mdiPhoneOutgoing" />
            позвонить
          </v-list-item>
          <v-list-item>
            <v-icon :icon="mdiClipboardOutline" />
            скопировать номер
          </v-list-item>
          <v-list-item link>
            <v-icon :icon="mdiHistory" />
            история
            <v-icon :icon="mdiChevronRight" class="phone-list__expand" />
            <v-menu
              :open-on-focus="false"
              activator="parent" submenu location="right center" transition="slide-x-transition"
            >
              <v-list>
                <v-list-item link>
                  история звонков
                </v-list-item>
                <v-list-item link>
                  история telegram
                </v-list-item>
                <v-list-item link>
                  история SMS
                </v-list-item>
              </v-list>
            </v-menu>
          </v-list-item>
          <v-divider />
          <v-list-item>
            <v-icon icon="$preview" />
            войти в лк
          </v-list-item>
        </v-list>
      </v-menu>
      <div
        v-if="showComment"
        class="phone-list__comment"
      >
        {{ item.comment }}
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.phone-list {
  overflow: visible;
  & > div {
    display: flex;
    overflow: visible;
  }
  &__number {
    cursor: pointer;
    width: 160px;
    position: relative;
    overflow: visible;
    z-index: 1;

    //&:hover {
    //  color: rgb(var(--v-theme-secondary)) !important;
    //}

    &:after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      width: 140px;
      background-color: transparent;
      z-index: -1;
      border-radius: 4px;
      transition: all ease-in-out 0.3s;
    }
    &[aria-expanded='true'] {
      &:after {
        background-color: rgba(var(--v-theme-primary), 0.2);
        box-shadow: 0 0 3px 3px rgba(var(--v-theme-primary), 0.2);
      }
    }
  }
  &__comment {
    color: rgb(var(--v-theme-gray));
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block;
    white-space: nowrap;
    width: 120px;
  }

  &__list {
    .v-list-item {
      .v-icon {
        margin-right: 6px;
      }
    }
  }

  &__expand {
    $size: 20px;
    margin: 0 !important;
    position: absolute;
    right: 6px;
    top: calc(50% - #{$size / 2});
    color: rgb(var(--v-theme-gray));
    font-size: $size;
  }
}
</style>
