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

const progressTooltip = reactive<{
  visible: boolean
  left: number
  top: number
  previewTime: number
}>({
  visible: false,
  left: 0,
  top: 0,
  previewTime: 0,
})

function disposeAudio() {
  if (!player.audio) {
    return
  }

  // При размонтировании/смене звонка останавливаем и отпускаем ссылку,
  // чтобы браузер мог освободить объект Audio.
  player.audio.pause()
  player.audio.currentTime = 0
  player.audio.src = ''
  player.audio = null
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

  if (!player.audio) {
    // Для single-view плеера загружаем аудио только один раз, дальше просто play/pause.
    const audio = await getAudio('play')
    player.audio = new Audio(audio)
    player.progress = {
      currentTime: 0,
      duration: 0,
    }
    player.audio.addEventListener('loadedmetadata', () => {
      // Длительность нужна сразу после загрузки, чтобы корректно считать tooltip и seek.
      const { duration } = player.audio!
      player.progress.duration = Number.isFinite(duration) && duration > 0 ? duration : 0
    })
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
  const percent = getProgressPercent(e)
  player.audio.currentTime = Math.round(
    player.progress.duration * percent,
  )
}

function getProgressPercent(e: MouseEvent) {
  const target = e.currentTarget as HTMLElement | null
  if (!target) {
    return 0
  }
  const { left, width } = target.getBoundingClientRect()
  if (!width) {
    return 0
  }
  const rawPercent = (e.clientX - left) / width
  return Math.min(1, Math.max(0, rawPercent))
}

function onProgressHover(e: MouseEvent) {
  if (!player.audio || !player.progress.duration) {
    progressTooltip.visible = false
    return
  }

  progressTooltip.visible = true
  const target = e.currentTarget as HTMLElement | null
  if (!target) {
    progressTooltip.visible = false
    return
  }
  const rect = target.getBoundingClientRect()
  const percent = getProgressPercent(e)
  progressTooltip.left = rect.left + rect.width * percent
  // Y фиксируем по progress bar, чтобы tooltip не "прыгал" за курсором.
  progressTooltip.top = rect.top
  progressTooltip.previewTime = Math.round(
    player.progress.duration * percent,
  )
}

function hideProgressTooltip() {
  progressTooltip.visible = false
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

const progressTooltipLabel = computed(() => formatToMinutesSeconds(progressTooltip.previewTime))

function formatToMinutesSeconds(time: number) {
  // Нормализуем и форматируем время для стабильного вывода в виде mm:ss.
  const safeTime = Number.isFinite(time) && time > 0 ? Math.floor(time) : 0
  const minutes = Math.floor(safeTime / 60)
  const seconds = safeTime % 60
  return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
}

onBeforeUnmount(disposeAudio)
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
      :size="42"
      variant="text"
      @click="togglePlay"
    >
    </v-btn>
    <div
      class="call-player__progress-wrapper"
      @click="audioSeek"
      @mousemove="onProgressHover"
      @mouseleave="hideProgressTooltip"
    >
      <div class="call-player__progress">
        <div
          v-if="player.audio"
          class="call-player__progress-playhead"
          :style="audioProgressWidth"
        />
      </div>
      <v-fade-transition>
        <div
          v-if="progressTooltip.visible"
          class="call-player__progress-tooltip"
          :style="{
            left: `${progressTooltip.left}px`,
            top: `${progressTooltip.top}px`,
          }"
        >
          {{ progressTooltipLabel }}
        </div>
      </v-fade-transition>
    </div>
    <div
      v-if="item.has_recording"
      class="call-player__time"
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

  button {
    margin-right: 4px;
  }

  &__time {
    padding-left: 16px;
    // font-variant-numeric: tabular-nums;
  }

  &--no-recording {
    .call-player__progress {
      opacity: 0.5;
    }
  }

  &__progress {
    width: 500px;
    background-color: rgb(var(--v-theme-border));
    opacity: 0.5;
    height: 10px;
    margin-left: 2px;
    border-radius: 8px;
    position: relative;
    overflow: hidden;
    transform-origin: center;
    transition:
      opacity ease-in-out 0.5s,
      transform 0.2s cubic-bezier(0.2, 0, 0, 1),
      background-color 0.2s linear;

    &-playhead {
      background: rgb(var(--v-theme-secondary));
      height: 100%;
      position: absolute;
      left: 0;
      top: 0;
      transition:
        width linear 0.1s,
        background-color 0.2s linear;
      pointer-events: none;
    }
  }

  &__progress-wrapper {
    height: 20px;
    display: flex;
    align-items: center;
  }

  &__progress-tooltip {
    position: fixed;
    z-index: 30;
    transform: translate(-50%, calc(-100% - 6px));
    background: rgba(black, 0.7);
    color: white;
    height: 30px;
    width: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    pointer-events: none;
    white-space: nowrap;
    font-size: 14px;
  }

  &--playing {
    .call-player__progress {
      opacity: 1;

      &-wrapper {
        cursor: pointer;
        &:hover {
          .call-player__progress {
            transform: scaleY(1.2);
            &-playhead {
              background-color: #4a87be;
            }
          }
        }
      }
    }
  }
}
</style>
