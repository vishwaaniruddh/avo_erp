/**
 * Client-Side Tax Preview
 * Note: Division of Responsibility -
 * React calculates PREVIEW ONLY for immediate UI feedback.
 * Authoritative final calculation is strictly computed and enforced by the PHP Backend.
 */

export interface OrderItemPreviewInput {
  product_id: number
  quantity: number
  unit_price: number
  tax_rate: number
}

export interface TaxPreviewResult {
  subtotal: number
  estimatedTax: number
  estimatedDiscount: number
  estimatedGrandTotal: number
  itemPreviews: {
    product_id: number
    quantity: number
    unit_price: number
    tax_rate: number
    lineSubtotal: number
    lineTax: number
    lineTotal: number
  }[]
}

export function calculateClientTaxPreview(
  items: OrderItemPreviewInput[],
  discount: number = 0
): TaxPreviewResult {
  let subtotal = 0
  let estimatedTax = 0

  const itemPreviews = items.map((item) => {
    const qty = Math.max(0, Number(item.quantity) || 0)
    const price = Math.max(0, Number(item.unit_price) || 0)
    const rate = Math.max(0, Number(item.tax_rate) || 0)

    const lineSubtotal = Number((qty * price).toFixed(2))
    const lineTax = Number((lineSubtotal * (rate / 100)).toFixed(2))
    const lineTotal = Number((lineSubtotal + lineTax).toFixed(2))

    subtotal += lineSubtotal
    estimatedTax += lineTax

    return {
      product_id: item.product_id,
      quantity: qty,
      unit_price: price,
      tax_rate: rate,
      lineSubtotal,
      lineTax,
      lineTotal,
    }
  })

  const estimatedDiscount = Math.max(0, Number(discount) || 0)
  const estimatedGrandTotal = Math.max(
    0,
    Number((subtotal + estimatedTax - estimatedDiscount).toFixed(2))
  )

  return {
    subtotal: Number(subtotal.toFixed(2)),
    estimatedTax: Number(estimatedTax.toFixed(2)),
    estimatedDiscount: Number(estimatedDiscount.toFixed(2)),
    estimatedGrandTotal,
    itemPreviews,
  }
}

