<script setup lang="ts">
const { items } = defineProps<{
  items: CallEvent[]
}>()
const timerKey = computed(() => items.filter(e => e.state === 'Connected').length)
// const { user } = useAuthStore()
</script>

<template>
  <div class="active-calls">
    <div
      v-for="item in items" :key="item.number"
      class="calls-list__item"
    >
      <div>
        <div class="calls-list__number">
          <CallAppStateIcon :state="item.state" />
          <span>
            {{ formatPhone(item.number) }}
          </span>
        </div>
        <div>
          <CallAppCallTimer v-if="item.state === 'Connected'" :key="timerKey" :item="item" />
        </div>
      </div>
      <div>
        <transition name="call-title-transition">
          <div v-if="item.state === 'Connected'" :key="1">
            Разговаривает
            {{ formatName(item.user!) }}
          </div>

          <div v-else :key="2">
            Входящий звонок
          </div>
        </transition>
      </div>
      <CallAppAon :item="item.phone" />
      <div v-if="item.phone?.comment">
        {{ item.phone.comment }}
      </div>
    </div>
  </div>
</template>
