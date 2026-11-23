import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { useForm, useFieldArray } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import {
  ShoppingCart,
  Plus,
  Trash2,
  Calculator,
  ArrowLeft,
  AlertCircle,
  AlertTriangle,
  CheckCircle2,
  ShieldAlert,
} from 'lucide-react'
import { customerApi, orderApi, productApi } from '@/api/services'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { formatCurrency } from '@/lib/utils'
import { calculateClientTaxPreview, type OrderItemPreviewInput } from '@/lib/taxPreview'
import type { Customer, Product } from '@/types'

const orderCreateSchema = z.object({
  customer_id: z.coerce.number().min(1, 'Please select a customer'),
  discount_amount: z.coerce.number().min(0, 'Discount must be positive').default(0),
  notes: z.string().optional(),
  items: z
    .array(
      z.object({
        product_id: z.coerce.number().min(1, 'Please choose a product'),
        quantity: z.coerce.number().int().min(1, 'Quantity must be at least 1'),
      })
    )
    .min(1, 'Order must include at least one item'),
})

type OrderFormValues = z.infer<typeof orderCreateSchema>

export function OrderCreate() {
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const [serverError, setServerError] = useState<string | null>(null)

  // Fetch all customers & products for dropdown selection
  const { data: customers } = useQuery({
    queryKey: ['customers', 'all'],
    queryFn: () => customerApi.getAll({ all: true }) as Promise<Customer[]>,
  })

  const { data: products } = useQuery({
    queryKey: ['products', 'all'],
    queryFn: () => productApi.getAll({ all: true }) as Promise<Product[]>,
  })

  const {
    register,
    control,
    handleSubmit,
    watch,
    formState: { errors },
  } = useForm<OrderFormValues>({
    resolver: zodResolver(orderCreateSchema) as any,
    defaultValues: {
      customer_id: 0,
      discount_amount: 0,
      notes: '',
      items: [{ product_id: 0, quantity: 1 }],
    },
  })

  const { fields, append, remove } = useFieldArray({
    control,
    name: 'items',
  })

  const watchedCustomerId = watch('customer_id')
  const watchedItems = watch('items')
  const watchedDiscount = watch('discount_amount') || 0

  const selectedCustomer = customers?.find((c) => c.id === Number(watchedCustomerId))

  // Map watched items to calculate Live Client Tax Calculation Preview (Preview Only!)
  const previewInputs: OrderItemPreviewInput[] = (watchedItems || [])
    .map((item) => {
      const p = products?.find((prod) => prod.id === Number(item.product_id))
      if (!p) return null
      return {
        product_id: p.id,
        quantity: Number(item.quantity) || 1,
        unit_price: Number(p.selling_price),
        tax_rate: Number(p.tax_rate),
      }
    })
    .filter(Boolean) as OrderItemPreviewInput[]

  const preview = calculateClientTaxPreview(previewInputs, Number(watchedDiscount))

  // Credit limit utilization preview
  const projectedBalance = selectedCustomer
    ? Number(selectedCustomer.outstanding_balance) + preview.estimatedGrandTotal
    : 0
  const isCreditExceeded = selectedCustomer
    ? projectedBalance > Number(selectedCustomer.credit_limit)
    : false

  const createMutation = useMutation({
    mutationFn: orderApi.create,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['orders'] })
      queryClient.invalidateQueries({ queryKey: ['products'] })
      queryClient.invalidateQueries({ queryKey: ['dashboard-metrics'] })
      navigate('/orders')
    },
    onError: (err: any) => {
      // Catch HTTP 422 (Validation Failed / Stock Deficit) or 400 (Bad Request)
      setServerError(err.message || 'Server error occurred while creating order')
    },
  })

  const onSubmit = (values: OrderFormValues) => {
    setServerError(null)
    createMutation.mutate({
      customer_id: Number(values.customer_id),
      discount_amount: Number(values.discount_amount),
      notes: values.notes,
      items: values.items.map((it) => ({
        product_id: Number(it.product_id),
        quantity: Number(it.quantity),
      })),
    })
  }

  return (
    <div className="space-y-6 max-w-5xl mx-auto">
      {/* Header */}
      <div className="flex items-center space-x-3">
        <Button variant="ghost" size="sm" onClick={() => navigate('/orders')}>
          <ArrowLeft className="w-4 h-4 mr-1" />
          Back to Orders
        </Button>
        <div>
          <h1 className="text-2xl font-bold tracking-tight text-slate-900">New Sales Order</h1>
          <p className="text-xs text-slate-500">
            Form displays live client tax preview; PHP backend enforces authoritative business validation & stock checks.
          </p>
        </div>
      </div>

      {serverError && (
        <div className="p-4 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 flex items-start">
          <AlertCircle className="w-5 h-5 mr-3 shrink-0 text-rose-600 mt-0.5" />
          <div>
            <div className="font-bold">Backend Business Validation Failed (HTTP 422):</div>
            <p className="mt-1">{serverError}</p>
          </div>
        </div>
      )}

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
        {/* Customer Selection Card */}
        <Card>
          <CardHeader>
            <CardTitle className="text-base">1. Customer Account</CardTitle>
            <CardDescription>Select commercial customer to view credit limits and pricing tier</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <Label>Customer</Label>
                <select
                  className="w-full h-10 px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  {...register('customer_id')}
                >
                  <option value={0}>-- Select Customer --</option>
                  {customers?.map((c) => (
                    <option key={c.id} value={c.id}>
                      {c.name} {c.company ? `(${c.company})` : ''}
                    </option>
                  ))}
                </select>
                {errors.customer_id && (
                  <p className="text-xs text-rose-600 mt-1">{errors.customer_id.message}</p>
                )}
              </div>

              {/* Customer Credit Info */}
              {selectedCustomer && (
                <div className="p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs space-y-1">
                  <div className="flex justify-between">
                    <span className="text-slate-500">Credit Limit:</span>
                    <span className="font-semibold text-slate-900">
                      {formatCurrency(selectedCustomer.credit_limit)}
                    </span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-slate-500">Current Outstanding:</span>
                    <span className="font-semibold text-slate-700">
                      {formatCurrency(selectedCustomer.outstanding_balance)}
                    </span>
                  </div>
                  <div className="flex justify-between border-t border-slate-200 pt-1">
                    <span className="text-slate-500">Projected Balance:</span>
                    <span
                      className={`font-bold ${
                        isCreditExceeded ? 'text-rose-600' : 'text-slate-900'
                      }`}
                    >
                      {formatCurrency(projectedBalance)}
                    </span>
                  </div>
                  {isCreditExceeded && (
                    <div className="mt-1 flex items-center text-rose-600 font-semibold text-[11px]">
                      <ShieldAlert className="w-3.5 h-3.5 mr-1" />
                      Warning: Order exceeds customer credit limit! Backend will reject order.
                    </div>
                  )}
                </div>
              )}
            </div>
          </CardContent>
        </Card>

        {/* Order Line Items */}
        <Card>
          <CardHeader className="flex flex-row items-center justify-between">
            <div>
              <CardTitle className="text-base">2. Order Line Items</CardTitle>
              <CardDescription>
                Live Stock Availability Display (React displays badges, PHP verifies availability)
              </CardDescription>
            </div>
            <Button
              type="button"
              variant="outline"
              size="sm"
              onClick={() => append({ product_id: 0, quantity: 1 })}
            >
              <Plus className="w-3.5 h-3.5 mr-1" />
              Add Item
            </Button>
          </CardHeader>
          <CardContent className="p-0">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead className="w-1/2">Product (SKU & Available Stock)</TableHead>
                  <TableHead className="w-24">Quantity</TableHead>
                  <TableHead>Unit Price</TableHead>
                  <TableHead>Tax Rate</TableHead>
                  <TableHead className="text-right">Line Total (Preview)</TableHead>
                  <TableHead className="w-12"></TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {fields.map((field, index) => {
                  const currentProductId = watchedItems?.[index]?.product_id
                  const product = products?.find((p) => p.id === Number(currentProductId))
                  const quantity = watchedItems?.[index]?.quantity || 1
                  const isStockDeficit = product ? quantity > product.stock_quantity : false

                  const lineSubtotal = product ? quantity * product.selling_price : 0
                  const lineTax = product ? lineSubtotal * (product.tax_rate / 100) : 0
                  const lineTotal = lineSubtotal + lineTax

                  return (
                    <TableRow key={field.id}>
                      <TableCell>
                        <select
                          className="w-full h-9 px-2 bg-white border border-slate-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500"
                          {...register(`items.${index}.product_id` as const)}
                        >
                          <option value={0}>-- Select Product --</option>
                          {products?.map((p) => (
                            <option key={p.id} value={p.id}>
                              {p.sku} - {p.name} (Stock: {p.stock_quantity} {p.unit}) - {formatCurrency(p.selling_price)}
                            </option>
                          ))}
                        </select>
                        {product && (
                          <div className="mt-1 flex items-center space-x-2">
                            {isStockDeficit ? (
                              <Badge variant="destructive" className="text-[10px]">
                                Deficit! Available: {product.stock_quantity} {product.unit}
                              </Badge>
                            ) : (
                              <Badge variant="success" className="text-[10px]">
                                In Stock: {product.stock_quantity} {product.unit}
                              </Badge>
                            )}
                          </div>
                        )}
                      </TableCell>
                      <TableCell>
                        <Input
                          type="number"
                          min="1"
                          className="h-9 text-xs"
                          {...register(`items.${index}.quantity` as const)}
                        />
                      </TableCell>
                      <TableCell className="text-xs font-semibold text-slate-800">
                        {product ? formatCurrency(product.selling_price) : '-'}
                      </TableCell>
                      <TableCell className="text-xs text-slate-600">
                        {product ? `${product.tax_rate}%` : '-'}
                      </TableCell>
                      <TableCell className="text-right text-xs font-bold text-slate-900">
                        {product ? formatCurrency(lineTotal) : '-'}
                      </TableCell>
                      <TableCell>
                        {fields.length > 1 && (
                          <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            className="text-rose-500 hover:text-rose-700"
                            onClick={() => remove(index)}
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                          </Button>
                        )}
                      </TableCell>
                    </TableRow>
                  )
                })}
              </TableBody>
            </Table>
          </CardContent>
        </Card>

        {/* Calculation & Live Tax Preview Card */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
          <Card>
            <CardHeader>
              <CardTitle className="text-base">Order Notes</CardTitle>
            </CardHeader>
            <CardContent>
              <textarea
                className="w-full h-28 p-3 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Special shipping instructions, PO number, or project reference..."
                {...register('notes')}
              />
            </CardContent>
          </Card>

          <Card className="bg-slate-50/80 border-blue-200">
            <CardHeader className="pb-3">
              <div className="flex items-center justify-between">
                <CardTitle className="text-base flex items-center text-blue-900">
                  <Calculator className="w-4 h-4 mr-2 text-blue-600" />
                  Tax & Grand Total Preview (React Preview Only)
                </CardTitle>
                <span className="text-[10px] font-semibold text-blue-600 uppercase bg-blue-100 px-2 py-0.5 rounded">
                  Preview Only
                </span>
              </div>
              <CardDescription>
                Authoritative final calculations and tax invoices are calculated by PHP Backend.
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-2 text-sm">
              <div className="flex justify-between text-slate-600">
                <span>Estimated Subtotal:</span>
                <span className="font-semibold text-slate-900">{formatCurrency(preview.subtotal)}</span>
              </div>
              <div className="flex justify-between text-slate-600">
                <span>Estimated Tax:</span>
                <span className="font-semibold text-slate-900">{formatCurrency(preview.estimatedTax)}</span>
              </div>

              <div className="flex items-center justify-between pt-2">
                <span className="text-slate-600">Discount Amount:</span>
                <div className="w-28">
                  <Input
                    type="number"
                    step="0.01"
                    className="h-8 text-right text-xs"
                    {...register('discount_amount')}
                  />
                </div>
              </div>

              <div className="flex justify-between items-center pt-3 border-t border-slate-200">
                <span className="text-base font-bold text-slate-900">Estimated Grand Total:</span>
                <span className="text-xl font-extrabold text-blue-700">
                  {formatCurrency(preview.estimatedGrandTotal)}
                </span>
              </div>

              {preview.estimatedGrandTotal >= 1000 && (
                <div className="p-2.5 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 flex items-center mt-2">
                  <AlertTriangle className="w-4 h-4 mr-2 shrink-0 text-amber-600" />
                  <span>
                    High-value order (&ge; $1,000.00): Will automatically enter{' '}
                    <span className="font-semibold">Pending Approval</span> workflow for Manager sign-off.
                  </span>
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Submit Actions */}
        <div className="flex justify-end space-x-3 pt-4">
          <Button type="button" variant="outline" onClick={() => navigate('/orders')}>
            Cancel
          </Button>
          <Button type="submit" isLoading={createMutation.isPending} size="lg">
            Submit Authoritative Order
          </Button>
        </div>
      </form>
    </div>
  )
}
