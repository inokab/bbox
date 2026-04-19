import type { ValidateRequest } from './schema.js'

const SUPPORTED_CURRENCIES = ['HUF', 'EUR', 'USD'] as const
const MAX_AMOUNT = 10_000_000

interface RuleResult {
  approved: boolean
  reason: string | null
}

type Rule = (request: ValidateRequest) => RuleResult | null

const rules: Rule[] = [
  (req) => {
    if (req.amount <= 0) {
      return { approved: false, reason: 'amount_must_be_positive' }
    }
    return null
  },

  (req) => {
    if (req.amount > MAX_AMOUNT) {
      return { approved: false, reason: 'amount_exceeds_limit' }
    }
    return null
  },

  (req) => {
    if (!SUPPORTED_CURRENCIES.includes(req.currency as typeof SUPPORTED_CURRENCIES[number])) {
      return { approved: false, reason: 'unsupported_currency' }
    }
    return null
  },
]

export function applyRules(request: ValidateRequest): RuleResult {
  for (const rule of rules) {
    const result = rule(request)
    if (result !== null) {
      return result
    }
  }

  return { approved: true, reason: null }
}