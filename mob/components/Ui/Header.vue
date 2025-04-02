<script setup lang="ts">
import { adminMenu, clientMenu } from '~/components/Menu'

const { isClient } = useAuthStore()

const menu = isClient ? clientMenu : adminMenu
const dialog = ref(false)
const showMenu = ref(false)
const isBurgerClosing = ref(false)

function toggleMenu() {
  dialog.value = !dialog.value
}

watch(dialog, (nowOpen, wasOpen) => {
  nextTick(() => (showMenu.value = nowOpen))

  if (wasOpen && !nowOpen) {
    isBurgerClosing.value = true
    setTimeout(() => {
      isBurgerClosing.value = false
    }, 400)
  }
})
</script>

<template>
  <header class="header" :class="{ 'header--menu-open': dialog }">
    <div class="header__wrapper">
      <div class="header__logo">
        <img src="/img/logo.svg" />
        <span>ЕГЭ-Центр</span>
      </div>
      <div class="header__actions">
        <a href="tel:+74956468592" class="header__phone">
          <img src="/img/phone.svg" />
        </a>
        <div class="header__hamburger" @click="toggleMenu()">
          <div
            class="hamburger hamburger--squeeze"
            :class="[
              {
                'is-active': dialog,
                'is-unactive-transition': isBurgerClosing,
              },
            ]"
          >
            <div class="hamburger-box">
              <div class="hamburger-inner"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <transition name="header-dialog-transition">
      <div v-if="dialog" class="menu-dialog">
        <div class="container">
          <transition name="menu-transition">
            <div v-if="showMenu" class="header__menu">
              <li v-for="m in menu" :key="m.to">
                <RouterLink :to="m.to" @click="dialog = false">
                  <v-icon :icon="m.icon" />
                  {{ m.title }}
                </RouterLink>
              </li>
            </div>
          </transition>
        </div>
      </div>
    </transition>
    <v-dialog :model-value="dialog" :fullscreen="false" :scrim="false" persistent>
    </v-dialog>
  </header>
</template>

<style lang="scss">
.header {
  $mb: 22px;
  $ease: cubic-bezier(0.25, 0.8, 0.25, 1);
  position: relative;
  z-index: 10;

  &--menu-open {
    .header {
      &__logo,
      &__phone {
        pointer-events: none;
        opacity: 0;
        // visibility: none;
      }
    }
  }

  &__logo,
  &__phone {
    transition: opacity 240ms cubic-bezier(0.4, 0, 0.6, 1) 80ms;
  }

  &__phone {
    height: 22px;
  }

  &__wrapper {
    padding: 16px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 2;
  }
  &__logo {
    display: flex;
    align-items: center;
    img {
      max-width: 39px;
      margin-right: 8px;
    }
    span {
      font-weight: 500;
      font-size: 20px;
    }
  }
  &__hamburger {
    display: flex;
  }

  &__actions {
    display: flex;
    align-items: center;
    gap: 16px;
    padding-top: 3px;
  }

  &__menu {
    overflow: hidden;
    float: left;
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 20px;

    &:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      height: 40px;
      width: 100%;
      background: linear-gradient(to top, white, rgba(255, 255, 255, 0));
      // background: transparent;
    }
    & > li:last-child {
      padding-bottom: 30px;
    }
    li {
      display: block;
      font-size: 18px;
      font-weight: 500;
    }

    a {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 0px 0px;
      cursor: pointer;
      color: black !important;
      transition: color 0.25s $ease;
      .v-icon {
        font-size: 24px;
      }
    }
  }
}

.header-dialog-transition {
  &-enter-active,
  &-leave-active {
    transition:
      opacity 0.32s cubic-bezier(0.4, 0, 0.6, 1),
      background-color 0.32s cubic-bezier(0.4, 0, 0.6, 1);
    backdrop-filter: saturate(180%) blur(20px);
  }

  &-enter-from, // Corrected from &-enter
  &-leave-to {
    // Corrected from &-leave-to
    opacity: 0;
    background: transparent;
  }

  &-enter-to, // Corrected from &-enter
  &-leave-from {
    // Corrected from &-leave
    opacity: 1;
    background: white;
  }
}

.menu-transition {
  &-enter-active,
  &-leave-active {
    transition:
      height 300ms cubic-bezier(0.4, 0, 0.6, 1),
      transform 150ms cubic-bezier(0.4, 0, 0.6, 1),
      opacity 150ms cubic-bezier(0.4, 0, 0.6, 1);
    transition-delay: 100ms;
  }

  &-enter-from {
    // Vue 3 uses -enter-from instead of -enter
    transform: translateY(-8px);
    opacity: 0;
    height: 0;
  }

  &-enter-to {
    opacity: 1;
    transform: translateY(0);
    height: 361px;
  }

  &-leave-from {
    opacity: 1;
    transform: translateY(0);
    height: 361px;
  }

  &-leave-to {
    transform: translateY(-8px); // Y-axis for correct transition
    opacity: 0;
    height: 0;
  }
}

.menu-dialog {
  position: fixed;
  top: 0;
  left: 0;
  background: white;
  width: 100vw;
  height: 100vh;
  overflow-y: scroll;
  z-index: 1;
  &::-webkit-scrollbar {
    display: none;
    width: 0px;
  }

  .container {
    margin-top: 60px;
    display: inline-block;
    padding-bottom: 60px;

    max-width: 640px;
    width: 100%;
    margin-left: auto;
    margin-right: auto;
    padding: 0 16px;
  }
}
</style>
