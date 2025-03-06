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

export const filters = ref<CallAppFilters>({
  q: '',
  status: 'all',
})

export const openCallApp = function (number: string = '') {
  callAppDialog.value = true
  filters.value = {
    q: number,
    status: 'all',
  }
}

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
