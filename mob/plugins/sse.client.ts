// plugins/sse.client.ts
export default defineNuxtPlugin(() => {
  const isDev = location.hostname === 'localhost'

  const eventSource = new EventSource(
    isDev
      ? 'http://localhost:3000/api/sse'
      : 'https://v3-node.ege-centr.ru:4443/sse',
  )

  const listeners: { [K in SseEvent]?: (data: any) => void } = {}

  eventSource.onmessage = (event) => {
    const parsed = JSON.parse(event.data)
    const eventName = parsed.event?.split('\\').pop() as SseEvent
    const callback = listeners[eventName]
    if (typeof callback === 'function') {
      callback(parsed.data.data)
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
