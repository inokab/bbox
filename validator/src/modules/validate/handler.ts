import type { FastifyReply, FastifyRequest } from 'fastify'
import { applyRules } from './rules.js'
import type { ValidateRequest, ValidateResponse } from './schema.js'

export async function validateHandler(
  request: FastifyRequest<{ Body: ValidateRequest }>,
  reply: FastifyReply,
): Promise<ValidateResponse> {
  const result = applyRules(request.body)

  return reply.send(result)
}