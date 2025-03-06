export default defineNuxtPlugin(() => {
  const worker = new SharedWorker('/workers/shared-worker.js', {
    type: 'module',
  })

  const listeners: { [K in SseEvent]?: (data: any) => void } = {}

  worker.port.onmessage = (event) => {
    const { data } = event
    // App\\Events\\CallEvent => CallEvent
    const eventName = data.event?.split('\\').pop() as SseEvent

    // Type guard to check if listeners[eventName] is a function
    const callback = listeners[eventName]
    if (typeof callback === 'function') {
      callback(data.data.data)
    }
  }

  return {
    provide: {
      addSseListener(event: SseEvent, callback: (data: any) => void) {
        listeners[event] = callback
      },
      removeSseListener(event: SseEvent) {
        delete listeners[event]
      },
    },
  }
})
