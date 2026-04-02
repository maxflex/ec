<script setup lang="ts">
import type { CallListResource } from '.'
import { mdiPause, mdiPlay } from '@mdi/js'
import { differenceInSeconds, parse } from 'date-fns'

const { item } = defineProps<{
  item: CallListResource
}>()

const player = reactive<{
  playing: boolean
  audio: HTMLAudioElement | null
  progress: {
    currentTime: number
    duration: number
  }
}>({
  audio: null,
  playing: false,
  progress: {
    currentTime: 0,
    duration: 0,
  },
})

async function getAudio(action: 'play' | 'download') {
  const { data } = await useHttp(
    `calls/recording/${action}/${item.id}`,
  )
  console.log(data)
  return data.value as string
}

async function togglePlay(e: MouseEvent) {
  e.stopPropagation()

  if (!player.audio) {
    // Для single-view плеера загружаем аудио только один раз, дальше просто play/pause.
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
    player.audio.play()
    return
  }

  player.playing = !player.playing
  player.playing ? player.audio!.play() : player.audio!.pause()
}

function audioSeek(e: MouseEvent) {
  if (!player.audio || !player.progress.duration) {
    return
  }
  e.stopPropagation()
  const target = e.target as HTMLElement
  const percent = e.offsetX / target.clientWidth
  player.audio.currentTime = Math.round(
    player.progress.duration * percent,
  )
}

const audioProgressWidth = computed(() => {
  const { currentTime, duration } = player.progress
  if (!duration) {
    return {
      width: '0%',
    }
  }
  return {
    width: `${(currentTime / duration) * 100}%`,
  }
})

const initialDurationSeconds = computed(() => {
  if (!item.answered_at) {
    return 0
  }

  const dateFormat = 'yyyy-MM-dd HH:mm:ss'
  const answeredAt = parse(item.answered_at, dateFormat, new Date())
  const finishedAt = parse(item.finished_at, dateFormat, new Date())
  const seconds = differenceInSeconds(finishedAt, answeredAt)
  return Number.isFinite(seconds) && seconds > 0 ? seconds : 0
})

const playbackTimeLabel = computed<string>(() => {
  // До первого запуска показываем общую длительность записи.
  if (!player.audio) {
    return formatToMinutesSeconds(initialDurationSeconds.value)
  }

  return formatToMinutesSeconds(player.progress.currentTime)
})

function formatToMinutesSeconds(time: number) {
  // Нормализуем и форматируем время для стабильного вывода в виде mm:ss.
  const safeTime = Number.isFinite(time) && time > 0 ? Math.floor(time) : 0
  const minutes = Math.floor(safeTime / 60)
  const seconds = safeTime % 60
  return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
}
</script>

<template>
  <div
    class="call-player"
    :class="{
      'call-player--no-recording': !item.has_recording,
      'call-player--playing': !!player.audio,
    }"
  >
    <!-- <v-icon :icon="mdiArrowDownThinCircleOutline" @click="downloadAudio" /> -->
    <v-btn
      :disabled="!item.has_recording"
      :icon="player.playing ? mdiPause : mdiPlay"
      :size="40"
      variant="text"
      @click="togglePlay"
    >
    </v-btn>
    <div class="call-player-progress" @click="audioSeek">
      <div
        v-if="player.audio"
        class="call-player-progress__playhead"
        :style="audioProgressWidth"
      />
    </div>
    <div
      v-if="item.has_recording"
      class="call-player-time"
    >
      {{ playbackTimeLabel }}
    </div>
  </div>
</template>

<style scoped lang="scss">
.call-player {
  display: flex;
  align-items: center;
  height: 30px;
  left: -10px;
  position: relative;
  gap: 6px;

  &-time {
    padding-left: 10px;
    // font-variant-numeric: tabular-nums;
  }

  &--no-recording {
    .call-player-progress {
      opacity: 0.5;
    }
  }

  &-progress {
    width: 500px;
    background-color: rgb(var(--v-theme-border));
    opacity: 0.5;
    height: 10px;
    margin-left: 2px;
    border-radius: 8px;
    position: relative;
    overflow: hidden;
    transition: opacity ease-in-out 0.5s;
    // transition: background-color linear 0.1s;

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

  &--playing {
    .call-player-progress {
      opacity: 1;
    }
  }
}
</style>
