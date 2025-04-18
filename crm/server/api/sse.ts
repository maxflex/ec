import { defineEventHandler } from 'h3'
import redis from 'redis'

const config = useRuntimeConfig()

export default defineEventHandler(async (event) => {
  // console.log('New SSE client connected')
  const redisClient = redis.createClient({
    socket: {
      host: config.redisHost,
      port: 6379,
    },
  })
  const eventStream = createEventStream(event, { autoclose: true })

  await redisClient.connect()

  await redisClient.subscribe('sse', message => eventStream.push(message))

  // cleanup the interval when the connection is terminated or the writer is closed
  eventStream.onClosed(async () => {
    // console.log('closing SSE...')
    await redisClient.unsubscribe('sse')
    await redisClient.quit()
    await eventStream.close()
  })

  event.node.req.on('close', () => console.log('REQ close'))

  return eventStream.send()
})
