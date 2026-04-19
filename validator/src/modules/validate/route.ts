import type { FastifyInstance } from 'fastify'
import { validateHandler } from './handler.js'
import { validateJsonSchema } from './schema.js'

export async function validateRoute(app: FastifyInstance): Promise<void> {
  app.post('/validate', { schema: validateJsonSchema }, validateHandler)
}