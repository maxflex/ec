export default defineNuxtPlugin(() => {
  const worker = new SharedWorker('/workers/shared-worker.js', {
    type: 'module',
  })

  const listeners: Record<string, (data: any) => void> = {}

  worker.port.onmessage = (event) => {
    const { data } = event
    const eventName = data.event?.split('\\').pop() // App\\Events\\CallEvent => CallEvent
    if (eventName && listeners[eventName]) {
      listeners[eventName](data.data.data)
    }
  }

  return {
    provide: {
      addSseListener(event: string, callback: (data: any) => void) {
        listeners[event] = callback
      },
    },
  }
})
