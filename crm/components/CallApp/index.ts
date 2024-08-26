export const isMissed = (ce: CallEvent) => ce.state === 'Disconnected' && !ce.user

export const hasIncoming = ref(false)
export const callAppDialog = ref(false)
