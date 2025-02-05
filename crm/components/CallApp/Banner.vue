<script setup lang="ts">
import { mdiPhoneIncoming, mdiPhoneMissed, mdiPhoneOutgoing } from '@mdi/js'
import { isMissed } from '.'

const { item } = defineProps<{
  item: CallEvent
}>()

defineEmits(['close'])
</script>

<template>
  <div
    class="call-banner"
    :class="{
      'call-banner--me': item.user?.id === useAuthStore().user?.id,
      'call-banner--missed': isMissed(item),
    }"
  >
    <div class="call-banner__number">
      <CallAppStateIcon :state="item.state" />
      {{ formatPhone(item.number) }}
    </div>
    <transition name="call-title-transition">
      <div v-if="item.user" :key="1">
        принял {{ formatName(item.user) }}
      </div>
      <div v-else :key="2" class="call-banner__info">
        <CallAppAon :item="item.aon" full />
        <div v-if="item.last_interaction" class="call-banner__last-interaction">
          <v-icon
            v-if="item.last_interaction.is_missed"
            :icon="mdiPhoneMissed"
            :color="item.last_interaction.is_missed_callback ? 'orange' : 'error'"
          />
          <v-icon v-else-if="item.last_interaction.type === 'incoming'" color="black" :icon="mdiPhoneIncoming" />
          <v-icon v-else color="black" :icon="mdiPhoneOutgoing" />
          <template v-if="item.last_interaction.user">
            {{ formatName(item.last_interaction.user) }},
          </template>
          <template v-else>
            Пропущенный,
          </template>
          {{ formatDateAgo(item.last_interaction.created_at) }} назад
          <CallAppDuration v-if="item.last_interaction.answered_at" :item="item.last_interaction" class="text-label pl-2" />
        </div>
      </div>
    </transition>

    <v-btn icon="$close" variant="text" :size="38" @click.stop="$emit('close')" />
  </div>
</template>

<style lang="scss">
.call-banner {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
  padding: 12px 20px;
  cursor: pointer;
  overflow: hidden;
  border-top: 1px solid #ffecb3;
  // border-top: 1px solid #e0e0e0;
  gap: 30px;
  &:before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    // background-color: $bg;
    // background-color: white;
    // background-color: #f2fcfe;
    background-color: #fff8e1;
    z-index: -1;
    transition: all linear 0.3s;
    transition-property: background-color, border-color;
  }
  &--me {
    border-color: #c8e6c9;
    &:before {
      background-color: #e8f5e9;
    }
  }
  &--missed {
    border-color: #ffcdd2;
    &:before {
      background-color: #ffebee;
    }
  }

  &__number {
    font-weight: 500;
    display: flex;
    align-items: center;
    .v-icon {
      color: black;
    }
  }
  &__info {
    display: flex;
    align-items: center;
    gap: 30px;
  }
  & > .v-btn {
    // opacity: 0;
    position: absolute;
    right: 10px;
  }
  .v-icon {
    margin-right: 6px;
  }
  &:hover {
    & > .v-btn {
      opacity: 1;
    }
  }
  &__last-interaction {
    display: flex;
    align-items: center;
    padding-left: 50px;
    .v-icon {
      font-size: 22px;
    }
  }
}
</style>
