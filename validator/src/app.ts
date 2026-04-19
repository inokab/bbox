import Fastify from 'fastify'
import { validateRoute } from './modules/validate/route.js'

export function buildApp() {
  const app = Fastify({
    logger: true,
  })

  app.register(validateRoute)

  return app
}