<script setup lang="ts">
import type { CallListResource } from '.'
import { mdiPause, mdiPlay } from '@mdi/js'
import { differenceInSeconds, parse } from 'date-fns'

const { item } = defineProps<{
  item: CallListResource
}>()

const player = reactive<{
  playing: boolean
  loading: boolean
  objectUrl: string | null
  audio: HTMLAudioElement | null
  progress: {
    currentTime: number
    duration: number
  }
}>({
  audio: null,
  playing: false,
  loading: false,
  objectUrl: null,
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
  if (player.audio) {
    // При размонтировании/смене звонка останавливаем и отпускаем ссылку,
    // чтобы браузер мог освободить объект Audio.
    player.audio.pause()
    player.audio.currentTime = 0
    player.audio.src = ''
    player.audio = null
  }

  if (player.objectUrl) {
    // Освобождаем blob URL, чтобы не держать аудио-данные в памяти после ухода со страницы.
    URL.revokeObjectURL(player.objectUrl)
    player.objectUrl = null
  }

  player.playing = false
  player.progress = {
    currentTime: 0,
    duration: 0,
  }
}

function getAudioUrl(): string {
  if (!item.recording_url) {
    throw new Error('Recording URL is empty')
  }

  return item.recording_url
}

async function togglePlay(e: MouseEvent) {
  e.stopPropagation()

  const nextPlayingState = !player.playing
  // Сначала фиксируем пользовательское намерение в UI, затем асинхронно приводим Audio к этому состоянию.
  player.playing = nextPlayingState

  if (player.loading) {
    return
  }

  if (!player.audio) {
    if (!nextPlayingState) {
      return
    }
    const audio = await createAudio()
    if (!audio) {
      player.playing = false
      return
    }
    if (player.playing) {
      await safePlay(audio)
    }
    return
  }

  if (player.audio.paused || player.audio.ended) {
    player.playing = true
    await safePlay(player.audio)
    return
  }

  player.playing = false
  player.audio.pause()
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

function getSafeDuration(duration: number) {
  return Number.isFinite(duration) && duration > 0 ? duration : 0
}

function syncProgressFromAudio(audio: HTMLAudioElement) {
  player.progress = {
    currentTime: Number.isFinite(audio.currentTime) && audio.currentTime >= 0 ? audio.currentTime : 0,
    duration: getSafeDuration(audio.duration),
  }
}

function bindAudioEvents(audio: HTMLAudioElement) {
  audio.addEventListener('loadedmetadata', () => {
    // После загрузки метаданных обновляем длительность: это база для seek и tooltip.
    syncProgressFromAudio(audio)
  })
  audio.addEventListener('timeupdate', () => syncProgressFromAudio(audio))
  audio.addEventListener('play', () => {
    player.playing = true
  })
  audio.addEventListener('pause', () => {
    player.playing = false
  })
  audio.addEventListener('ended', () => {
    player.playing = false
    syncProgressFromAudio(audio)
  })
}

async function ensureObjectUrl() {
  if (player.objectUrl) {
    return player.objectUrl
  }

  // TEST анимации
  // await new Promise(resolve => setTimeout(resolve, 3000))

  // Скачиваем запись из CDN в Blob и затем воспроизводим локально из памяти вкладки.
  const sourceUrl = getAudioUrl()
  const response = await fetch(sourceUrl)
  if (!response.ok) {
    throw new Error(`Audio blob download failed: ${response.status}`)
  }

  const blob = await response.blob()
  player.objectUrl = URL.createObjectURL(blob)
  return player.objectUrl
}

async function createAudio() {
  if (player.loading) {
    return null
  }

  player.loading = true
  try {
    const objectUrl = await ensureObjectUrl()
    const audio = new Audio(objectUrl)
    bindAudioEvents(audio)
    player.audio = audio
    player.progress = {
      currentTime: 0,
      duration: 0,
    }
    return audio
  }
  catch (error) {
    console.error('CallPlayer: failed to initialize audio from blob', error)
    useGlobalMessage('Не удалось загрузить запись звонка', 'error')
    return null
  }
  finally {
    player.loading = false
  }
}

async function safePlay(audio: HTMLAudioElement) {
  try {
    await audio.play()
    player.playing = true
  }
  catch (error) {
    player.playing = false
    console.error('CallPlayer: play failed', error)
  }
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
      'call-player--playing': player.playing || player.loading || !!player.audio,
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
      <div
        class="call-player__progress"
        :class="{ 'call-player__progress--loading': player.loading }"
      >
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

    &::after {
      content: '';
      position: absolute;
      inset: 0;
      border-radius: inherit;
      background: linear-gradient(
        90deg,
        rgb(var(--v-theme-border)) 0%,
        rgb(var(--v-theme-border)) 35%,
        rgba(255, 255, 255, 0.5) 50%,
        rgb(var(--v-theme-border)) 65%,
        rgb(var(--v-theme-border)) 100%
      );
      background-size: 200% auto;
      animation: call-player-progress-shimmer 4s linear infinite;
      opacity: 0;
      transition: opacity 0.28s ease;
      pointer-events: none;
      z-index: 2;
    }

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

    &--loading {
      opacity: 1;

      &::after {
        opacity: 1;
      }
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

@keyframes call-player-progress-shimmer {
  0% {
    background-position: 200% center;
  }
  100% {
    background-position: -200% center;
  }
}
</style>
