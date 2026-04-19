export interface ValidateRequest {
  merchant_id: string
  type: 'payment' | 'refund'
  amount: number
  currency: string
}

export interface ValidateResponse {
  approved: boolean
  reason: string | null
}

export const validateJsonSchema = {
  body: {
    type: 'object',
    required: ['merchant_id', 'type', 'amount', 'currency'],
    properties: {
      merchant_id: { type: 'string' },
      type: { type: 'string', enum: ['payment', 'refund'] },
      amount: { type: 'number' },
      currency: { type: 'string', minLength: 3, maxLength: 3 },
    },
    additionalProperties: false,
  },
  response: {
    200: {
      type: 'object',
      required: ['approved', 'reason'],
      properties: {
        approved: { type: 'boolean' },
        reason: { type: 'string', nullable: true },
      },
    },
  },
} as const