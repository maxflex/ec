export const isMissed = (ce: CallEvent) => ce.state === 'Disconnected' && !ce.user

export const missedCount = ref(0)
export const hasIncoming = ref(false)
export const callAppDialog = ref(false)

export const player = reactive<{
  itemId: string | null
  playing: boolean
  audio: HTMLAudioElement | null
  progress: {
    currentTime: number
    duration: number
  }
}>({
  itemId: null,
  audio: null,
  playing: false,
  progress: {
    currentTime: 0,
    duration: 0,
  },
})

export const loadMissedCount = async function () {
  const { data } = await useHttp<string>(`calls`, {
    params: {
      count: 1,
      status: 'missed',
    },
  })
  if (data.value) {
    missedCount.value = Number.parseInt(data.value)
  }
}
