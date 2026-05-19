import { Hocuspocus } from '@hocuspocus/server'
import { Logger } from '@hocuspocus/extension-logger'

const server = new Hocuspocus({
  port: 1234,
  extensions: [
    new Logger()
  ]
})

server.listen()
console.log('🟢 Hocuspocus Server running at ws://localhost:1234')