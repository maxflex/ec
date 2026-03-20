<script setup lang="ts">
const { count } = defineProps<{
  count: number
}>()

const isCountUpdated = ref(false)
const rollDirection = ref<'up' | 'down'>('up')
let clearUpdatedStateTimeout: number | null = null

const rollTransitionName = computed(() => {
  return rollDirection.value === 'down'
    ? 'menu-count-roll-down'
    : 'menu-count-roll-up'
})

watch(() => count, (newValue, oldValue) => {
  // Анимация нужна только при реальном изменении уже отрисованного счетчика.
  if (newValue === oldValue) {
    return
  }

  // Рост — сверху вниз, снижение — снизу вверх.
  rollDirection.value = newValue > oldValue ? 'down' : 'up'

  if (clearUpdatedStateTimeout !== null) {
    clearTimeout(clearUpdatedStateTimeout)
    clearUpdatedStateTimeout = null
  }

  // Фоновую подсветку показываем только при увеличении счетчика.
  if (newValue > oldValue) {
    isCountUpdated.value = false
    requestAnimationFrame(() => {
      isCountUpdated.value = true
    })

    clearUpdatedStateTimeout = window.setTimeout(() => {
      isCountUpdated.value = false
      clearUpdatedStateTimeout = null
    }, 4000)
  }
  else {
    isCountUpdated.value = false
  }
})

onUnmounted(() => {
  if (clearUpdatedStateTimeout !== null) {
    clearTimeout(clearUpdatedStateTimeout)
  }
})
</script>

<template>
  <v-badge
    class="menu-count"
    :class="{ 'menu-count--updated': isCountUpdated }"
    inline
  >
    <template #badge>
      <span class="menu-count__slot">
        <Transition :name="rollTransitionName" mode="out-in">
          <span :key="count" class="menu-count__value">
            {{ count }}
          </span>
        </Transition>
      </span>
    </template>
  </v-badge>
</template>

<style lang="scss">
$badgeBg: #ffcc80;

.menu-count {
  &__slot {
    display: inline-flex;
    align-items: center;
    height: 16px;
    overflow: hidden;
  }

  &__value {
    display: inline-block;
    min-width: 1ch;
    text-align: center;
    line-height: 1;
  }

  // Повторяем фоновую подсветку item-updated на бейдже счетчика.
  &--updated {
    .v-badge__badge {
      animation: menuCountUpdated 4s cubic-bezier(0.22, 1, 0.36, 1) 1;
      // animation: menuCountUpdated 2s linear 1;
    }
  }

  .v-badge__badge {
    // Базовый цвет счетчика задаем без !important, чтобы анимация легко перекрывалась.
    background-color: $badgeBg;
    color: black;
    // Внешний box-shadow пульса должен быть виден вокруг бейджа.
    overflow: visible;
  }
}

@keyframes menuCountUpdated {
  from {
    $color: #fe8a1e;
    background-color: $color;
    box-shadow: 0 0 0 0 rgba($color, 0.6);
  }
  to {
    background-color: $badgeBg;
    box-shadow: 0 0 0 6px rgba($badgeBg, 0);
  }
}

.menu-count-roll-up-enter-active,
.menu-count-roll-up-leave-active,
.menu-count-roll-down-enter-active,
.menu-count-roll-down-leave-active {
  transition:
    transform 0.28s cubic-bezier(0.22, 1, 0.36, 1),
    opacity 0.28s ease;
}

.menu-count-roll-up-enter-from {
  transform: translateY(115%);
  opacity: 0;
}

.menu-count-roll-up-enter-to {
  transform: translateY(0);
  opacity: 1;
}

.menu-count-roll-up-leave-from {
  transform: translateY(0);
  opacity: 1;
}

.menu-count-roll-up-leave-to {
  transform: translateY(-115%);
  opacity: 0;
}

.menu-count-roll-down-enter-from {
  transform: translateY(-115%);
  opacity: 0;
}

.menu-count-roll-down-enter-to {
  transform: translateY(0);
  opacity: 1;
}

.menu-count-roll-down-leave-from {
  transform: translateY(0);
  opacity: 1;
}

.menu-count-roll-down-leave-to {
  transform: translateY(115%);
  opacity: 0;
}
</style>
