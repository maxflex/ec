import { defineEventHandler, getQuery } from 'h3'
import redis from 'redis'

const config = useRuntimeConfig()

export default defineEventHandler(async (event) => {
  // Выбор потока делаем на уровне единого SSE-relay:
  // - default => legacy-канал EC (sse), чтобы поведение EC не менялось
  // - project=mr => только MR
  // Для всех существующих EC-клиентов endpoint работает как раньше, без query.
  const query = getQuery(event)
  const channel = query.project === 'mr' ? 'sse.mr' : 'sse'

  // console.log('New SSE client connected')
  const redisClient = redis.createClient({
    socket: {
      host: config.redisHost,
      port: 6379,
    },
  })
  const eventStream = createEventStream(event, { autoclose: true })

  await redisClient.connect()

  const pushSystemEvent = async (eventName: string, payload: Record<string, unknown>) => {
    await eventStream.push(JSON.stringify({
      // Системные события формируются на уровне SSE-relay (Nuxt), а не Laravel events.
      // Поэтому используем отдельный нейтральный namespace, чтобы не вводить в заблуждение.
      event: `sse.system.${eventName}`,
      data: {
        data: payload,
      },
    }))
  }

  // cleanup the interval when the connection is terminated or the writer is closed
  const heartbeatInterval = setInterval(() => {
    void pushSystemEvent('heartbeat', { ts: Date.now() })
  }, 10_000)

  eventStream.onClosed(async () => {
    // console.log('closing SSE...')
    clearInterval(heartbeatInterval)
    try {
      await redisClient.unsubscribe(channel)
    }
    catch (error) {
      console.error('[sse] redis unsubscribe error', error)
    }
    await redisClient.quit()
    await eventStream.close()
  })

  const sendPromise = eventStream.send()

  // Do not await: sending before stream is open can block.
  void pushSystemEvent('connected', { ts: Date.now() })

  void redisClient.subscribe(channel, (message) => {
    void eventStream.push(message)
  }).catch((error) => {
    console.error('[sse] redis subscribe error', error, { channel })
  })

  return sendPromise
})
