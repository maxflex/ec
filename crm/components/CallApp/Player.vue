<script setup lang="ts">
import { mdiArrowDownThinCircleOutline, mdiPauseCircleOutline, mdiPlayCircleOutline } from '@mdi/js'
import { player } from '.'

const { item } = defineProps<{
  item: CallListResource
}>()

async function downloadAudio(e: MouseEvent) {
  e.stopPropagation()
  const audio = await getAudio('download')
  const link = document.createElement('a')
  link.href = audio
  link.click()
}

async function getAudio(action: 'play' | 'download') {
  const { data } = await useHttp(
    `calls/recording/${action}/${item.id}`,
  )
  console.log(data)
  return data.value as string
}

async function togglePlay(e: MouseEvent) {
  e.stopPropagation()
  // нажали на новую запись
  if (item.id !== player.itemId) {
    // если до этого что-то играло, останавливаем
    if (player.playing) {
      player.audio!.pause()
    }
    const audio = await getAudio('play')
    player.audio = new Audio(audio)
    player.progress = {
      currentTime: 0,
      duration: 0,
    }
    player.audio.addEventListener('timeupdate', () => {
      const { currentTime, duration } = player.audio!
      player.progress = {
        currentTime,
        duration,
      }
    })
    player.audio.addEventListener(
      'ended',
      () => (player.playing = false),
    )
    player.playing = true
  }
  else {
    player.playing = !player.playing
  }
  player.itemId = item.id
  player.playing ? player.audio!.play() : player.audio!.pause()
}

function audioSeek(e: MouseEvent) {
  e.stopPropagation()
  const target = e.target as HTMLElement
  const percent = e.offsetX / target.clientWidth
  player.audio!.currentTime = Math.round(
    player.progress.duration * percent,
  )
}

const audioProgressWidth = computed(() => {
  const { currentTime, duration } = player.progress
  return {
    width: `${(currentTime / duration) * 100}%`,
  }
})
</script>

<template>
  <div class="call-app-player">
    <v-icon :icon="mdiArrowDownThinCircleOutline" @click="downloadAudio" />
    <v-icon
      :icon="player.playing && item.id === player.itemId ? mdiPauseCircleOutline : mdiPlayCircleOutline"
      @click="togglePlay"
    />
    <div
      class="call-app-player-progress"
      :class="{
        'call-app-player-progress--playing': item.id === player.itemId,
      }"
      @click="audioSeek"
    >
      <div
        v-if="item.id === player.itemId"
        class="call-app-player-progress__playhead"
        :style="audioProgressWidth"
      />
    </div>
  </div>
</template>

<style scoped lang="scss">
.call-app-player {
  display: flex;
  align-items: center;
  margin-top: 10px;
  height: 30px;
  // padding-top: 6px;
  .v-icon {
    margin-right: 6px;
  }
  &-progress {
    background-color: rgb(var(--v-theme-border));
    height: 8px;
    margin-left: 2px;
    width: 100%;
    border-radius: 4px;
    position: relative;
    overflow: hidden;
    pointer-events: none;
    // transition: background-color linear 0.1s;
    &--playing {
      //background-color: orange;
      pointer-events: all;
      cursor: text;
    }
    &__playhead {
      background: rgb(var(--v-theme-secondary));
      height: 100%;
      position: absolute;
      left: 0;
      top: 0;
      transition: width linear 0.1s;
      pointer-events: none;
    }
  }
}
</style>
