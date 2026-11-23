/**
 * Enterprise Validation Utilities for AVO ERP
 * Comprehensive Phone & Email validation across all forms
 */

/**
 * Validates email address according to RFC 5322 format
 */
export function isValidEmail(email: string): boolean {
  if (!email || typeof email !== 'string') return false
  const trimmed = email.trim()
  const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/
  return emailRegex.test(trimmed)
}

/**
 * Validates phone numbers (Standard 10-digit mobile, +91 Indian format, landlines, and E.164 international)
 * Strictly rejects lengths < 10 or > 15, repetitive dummy digits, and non-numeric garbage
 */
export function isValidPhone(phone: string): boolean {
  if (!phone || typeof phone !== 'string') return false
  const trimmed = phone.trim()

  // Remove formatting characters: spaces, dashes, parentheses, dots
  const cleaned = trimmed.replace(/[\s\-\(\)\.]/g, '')

  // Digits without leading '+'
  const digitsOnly = cleaned.replace(/^\+/, '')

  // Reject anything less than 10 or more than 15 digits
  if (digitsOnly.length < 10 || digitsOnly.length > 15) {
    return false
  }

  // Reject repeating dummy digits (e.g. 0000000000, 1111111111)
  if (/^(\d)\1+$/.test(digitsOnly)) {
    return false
  }

  // Must match standard phone format
  const phoneRegex = /^\+?[0-9]{10,15}$/
  if (!phoneRegex.test(cleaned)) {
    return false
  }

  // If Indian 10-digit number without country code, ensure it starts with valid digit 6, 7, 8, or 9
  if (digitsOnly.length === 10) {
    return /^[6-9]\d{9}$/.test(digitsOnly)
  }

  // If Indian 12-digit number with 91 prefix, ensure 10-digit part starts with 6-9
  if (digitsOnly.length === 12 && digitsOnly.startsWith('91')) {
    return /^91[6-9]\d{9}$/.test(digitsOnly)
  }

  return true
}

/**
 * Sanitizes phone input in real-time on typing
 * Only allows digits, '+', spaces, and dashes, up to max 16 characters
 */
export function sanitizePhoneInput(val: string): string {
  if (!val) return ''
  let sanitized = val.replace(/[^0-9+\s\-]/g, '')
  // Keep '+' only at the beginning
  if (sanitized.includes('+')) {
    const hasLeadingPlus = sanitized.startsWith('+')
    sanitized = (hasLeadingPlus ? '+' : '') + sanitized.replace(/\+/g, '')
  }
  return sanitized.slice(0, 16)
}

/**
 * Returns user-friendly validation error for phone or null if valid
 */
export function getPhoneError(phone: string, isRequired = true): string | null {
  if (!phone || !phone.trim()) {
    return isRequired ? 'Phone number is required.' : null
  }
  const cleaned = phone.replace(/[\s\-\(\)\.]/g, '').replace(/^\+/, '')
  if (cleaned.length < 10) {
    return 'Phone number must be at least 10 digits.'
  }
  if (cleaned.length > 15) {
    return 'Phone number cannot exceed 15 digits.'
  }
  if (!isValidPhone(phone)) {
    return 'Please enter a valid 10-digit mobile (e.g. 98200 12345) or international number.'
  }
  return null
}

/**
 * Returns user-friendly validation error for email or null if valid
 */
export function getEmailError(email: string, isRequired = true): string | null {
  if (!email || !email.trim()) {
    return isRequired ? 'Email address is required.' : null
  }
  if (!isValidEmail(email)) {
    return 'Please enter a valid email address (e.g. name@company.com).'
  }
  return null
}
