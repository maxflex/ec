<script setup lang="ts">
import type { RequestListResource } from '../Request'
import {
  mdiChevronRight,
  mdiClipboardOutline,
  mdiHistory,
  mdiPhoneOutgoing,
} from '@mdi/js'

const { items, request, showComment, noColors } = defineProps<{
  items: PhoneResource[]
  request?: RequestListResource
  showComment?: boolean
  noColors?: boolean // не раскрашивать номер телефона в цвет
}>()

const router = useRouter()
const { logIn } = useAuthStore()

const previewModeAvailableEntityTypes: EntityType[] = [
  EntityTypeValue.client,
  EntityTypeValue.representative,
  EntityTypeValue.teacher,
]

function call(item: PhoneResource): void {
  window.location.href = `tel:${item.number}`
}

async function copyToClipboard(item: PhoneResource): Promise<void> {
  await navigator.clipboard.writeText(item.number)
  useGlobalMessage(`<b>${formatPhone(item.number)}</b> скопирован`, 'success')
}

function isPreviewModeAvailable(item: PhoneResource): boolean {
  return previewModeAvailableEntityTypes.includes(item.entity_type)
}

function openHistory(path: '/calls' | '/telegram-messages' | '/sms-messages', item: PhoneResource): void {
  router.push({
    path,
    query: {
      number: item.number,
    },
  })
}

async function enterPreviewMode(item: PhoneResource): Promise<void> {
  if (!isPreviewModeAvailable(item)) {
    return
  }
  const { data } = await useHttp<TokenResponse>(
    `preview-mode`,
    {
      method: 'post',
      body: {
        phone_id: item.id,
      },
    },
  )
  if (data.value) {
    const { token, user } = data.value
    logIn(user, token, true)
  }
}
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
          <a
            v-if="noColors"
            class="phone-list__number"
            v-bind="props"
          >
            {{ formatPhone(item.number) }}
          </a>
          <PhoneNumber
            v-else
            :item="item"
            class="phone-list__number"
            :request="request"
            v-bind="props"
          />
        </template>
        <v-list class="phone-list__list">
          <v-list-item @click="call(item)">
            <v-icon :icon="mdiPhoneOutgoing" />
            <div style="line-height: 12px;">
              <div>
                позвонить
              </div>
              <div class="text-caption text-gray">
                {{ item.comment }}
              </div>
            </div>
          </v-list-item>
          <v-list-item @click="copyToClipboard(item)">
            <v-icon :icon="mdiClipboardOutline" />
            <span>
              скопировать номер
            </span>
          </v-list-item>
          <v-list-item link>
            <v-icon :icon="mdiHistory" />
            <span>
              история
            </span>
            <v-icon :icon="mdiChevronRight" class="phone-list__expand" />
            <!--  location="right center" transition="slide-x-transition" -->
            <v-menu :open-on-focus="false" activator="parent" submenu>
              <v-list>
                <v-list-item @click="openHistory('/calls', item)">
                  история звонков
                </v-list-item>
                <v-list-item @click="openHistory('/telegram-messages', item)">
                  история Telegram
                </v-list-item>
                <v-list-item @click="openHistory('/sms-messages', item)">
                  история SMS
                </v-list-item>
              </v-list>
            </v-menu>
          </v-list-item>
          <v-divider />
          <v-list-item
            :disabled="!isPreviewModeAvailable(item)"
            @click="enterPreviewMode(item)"
          >
            <v-icon icon="$preview" />
            <span>
              войти в лк
            </span>
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
    user-select: none;
    transition: color linear 0.2s;

    // &:hover {
    //   color: rgb(var(--v-theme-secondary)) !important;
    // }

    &:after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      width: 140px;
      background-color: transparent;
      z-index: -1;
      // z-index: 1;
      border-radius: 4px;
      transition: all ease-in-out 0.3s;
    }

    // &[aria-expanded='true'] {
    //   color: lighten(#337ab7, 15) !important;
    //   // &:after {
    //   //   background-color: rgba(white, 0.5);
    //   // }
    // }

    &[aria-expanded='true'] {
      &:after {
        background-color: rgba(var(--v-theme-primary), 0.2);
        // box-shadow: 0 0 3px 3px rgba(var(--v-theme-primary), 0.2);
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
      &__content {
        display: flex;
        align-items: center;
        gap: 8px;
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
