<script setup lang="ts">
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
      </div>
    </transition>
    <div v-if="item.last_interaction">
      <pre>{{ item.last_interaction }}</pre>
    </div>
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
      margin-right: 6px;
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
  &:hover {
    & > .v-btn {
      opacity: 1;
    }
  }
}
</style>
