// const isDev = import.meta.env.MODE === 'development'
const isDev = import.meta.url.indexOf('http://localhost:3000') === 0
const ports = []
let eventSource = null

globalThis.onconnect = (e) => {
  ports.push(e.ports[0])

  if (eventSource === null) {
    eventSource = new EventSource(isDev
      ? 'http://localhost:3000/api/sse'
      : 'https://v3-node.ege-centr.ru:4443/sse',
    )
  }

  eventSource.onmessage = ({ data }) => {
    const res = JSON.parse(data)
    ports.forEach(port => port.postMessage(res))
    // if (isDev) {
    console.log(res)
    // }
  }
}

// Optional: clean up when no ports are connected (advanced use case)
// self.onmessage = (e) => {
//   if (e.data === 'cleanup') {
//     ports.length = 0;
//     eventSource.close();
//     eventSource = null;
//   }
// }
