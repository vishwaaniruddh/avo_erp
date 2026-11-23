import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import {
  Package,
  Plus,
  Search,
  AlertCircle,
  SlidersHorizontal,
  Trash2,
  CheckCircle2,
} from 'lucide-react'
import { productApi } from '@/api/services'
import { useAuthStore } from '@/store/authStore'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { PaginationControls } from '@/components/ui/pagination'
import { Dialog, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { formatCurrency } from '@/lib/utils'
import type { PaginatedResult, Product } from '@/types'

const productSchema = z.object({
  sku: z.string().min(2, 'SKU must be at least 2 chars').toUpperCase(),
  name: z.string().min(2, 'Name is required'),
  category: z.string().min(2, 'Category is required'),
  description: z.string().optional(),
  cost_price: z.coerce.number().min(0, 'Cost price cannot be negative'),
  selling_price: z.coerce.number().min(0, 'Selling price cannot be negative'),
  tax_rate: z.coerce.number().min(0).max(100, 'Tax rate must be 0-100%'),
  stock_quantity: z.coerce.number().int().min(0, 'Stock cannot be negative'),
  reorder_level: z.coerce.number().int().min(0),
  unit: z.string().min(1, 'Unit is required'),
})

type ProductFormValues = z.infer<typeof productSchema>

export function Products() {
  const queryClient = useQueryClient()
  const { canManageProducts, canDeleteProducts } = useAuthStore()

  const [page, setPage] = useState(1)
  const [limit, setLimit] = useState(10)
  const [search, setSearch] = useState('')

  // Modals state
  const [isAddOpen, setIsAddOpen] = useState(false)
  const [stockModalProduct, setStockModalProduct] = useState<Product | null>(null)
  const [stockDelta, setStockDelta] = useState<number>(10)
  const [stockReason, setStockReason] = useState<string>('Restock delivery batch')

  const [feedback, setFeedback] = useState<{ type: 'success' | 'error'; message: string } | null>(null)

  // Fetch paginated products (React: Display controls, PHP: Fetch records)
  const { data, isLoading } = useQuery({
    queryKey: ['products', page, limit, search],
    queryFn: () => productApi.getAll({ page, limit, search }) as Promise<PaginatedResult<Product>>,
  })

  // Create Product Mutation
  const createMutation = useMutation({
    mutationFn: productApi.create,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['products'] })
      setIsAddOpen(false)
      reset()
      setFeedback({ type: 'success', message: 'Product created successfully!' })
      setTimeout(() => setFeedback(null), 4000)
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.message || 'Failed to create product' })
    },
  })

  // Adjust Stock Mutation (Stock availability: React display, PHP verify/update)
  const stockMutation = useMutation({
    mutationFn: ({ id, delta, reason }: { id: number; delta: number; reason: string }) =>
      productApi.adjustStock(id, delta, reason),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['products'] })
      queryClient.invalidateQueries({ queryKey: ['dashboard-metrics'] })
      setStockModalProduct(null)
      setFeedback({ type: 'success', message: 'Product inventory stock adjusted successfully!' })
      setTimeout(() => setFeedback(null), 4000)
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.message || 'Failed to adjust stock' })
    },
  })

  // Delete Mutation (Casbin enforced)
  const deleteMutation = useMutation({
    mutationFn: productApi.delete,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['products'] })
      setFeedback({ type: 'success', message: 'Product deleted successfully' })
      setTimeout(() => setFeedback(null), 4000)
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.message || 'Failed to delete product (RBAC blocked)' })
    },
  })

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<ProductFormValues>({
    resolver: zodResolver(productSchema) as any,
    defaultValues: {
      category: 'Electronics',
      tax_rate: 18,
      stock_quantity: 25,
      reorder_level: 10,
      unit: 'pcs',
    },
  })

  const onAddSubmit = (values: ProductFormValues) => {
    createMutation.mutate(values)
  }

  const handleStockAdjustment = () => {
    if (!stockModalProduct) return
    stockMutation.mutate({
      id: stockModalProduct.id,
      delta: Number(stockDelta),
      reason: stockReason,
    })
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight text-slate-900">Inventory & Product Catalog</h1>
          <p className="text-sm text-slate-500">
            Real-time stock tracking with PHP authoritative stock verification and Casbin RBAC access control
          </p>
        </div>
        {canManageProducts() && (
          <Button onClick={() => setIsAddOpen(true)}>
            <Plus className="w-4 h-4 mr-2" />
            Add New Product
          </Button>
        )}
      </div>

      {/* Feedback banner */}
      {feedback && (
        <div
          className={`p-3 rounded-lg text-xs flex items-center ${
            feedback.type === 'success'
              ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
              : 'bg-rose-50 text-rose-800 border border-rose-200'
          }`}
        >
          {feedback.type === 'success' ? (
            <CheckCircle2 className="w-4 h-4 mr-2 shrink-0" />
          ) : (
            <AlertCircle className="w-4 h-4 mr-2 shrink-0" />
          )}
          {feedback.message}
        </div>
      )}

      {/* Filter Toolbar */}
      <Card>
        <CardContent className="p-4 flex flex-col sm:flex-row gap-4 items-center justify-between">
          <div className="relative w-full sm:w-80">
            <Search className="w-4 h-4 absolute left-3 top-3 text-slate-400" />
            <Input
              type="text"
              placeholder="Search by SKU, Name, or Category..."
              className="pl-9"
              value={search}
              onChange={(e) => {
                setSearch(e.target.value)
                setPage(1)
              }}
            />
          </div>
          <div className="text-xs text-slate-500">
            Backend Caching: <span className="font-semibold text-blue-600">Active (FileCache TTL 300s)</span>
          </div>
        </CardContent>
      </Card>

      {/* Products Table */}
      <Card>
        <CardContent className="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>SKU</TableHead>
                <TableHead>Product Name</TableHead>
                <TableHead>Category</TableHead>
                <TableHead>Cost / Price</TableHead>
                <TableHead>Tax Rate</TableHead>
                <TableHead>Stock Availability (React Display)</TableHead>
                <TableHead className="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {isLoading ? (
                <TableRow>
                  <TableCell colSpan={7} className="h-32 text-center text-slate-500">
                    Fetching records from backend...
                  </TableCell>
                </TableRow>
              ) : !data || data.data.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={7} className="h-32 text-center text-slate-500">
                    No products found.
                  </TableCell>
                </TableRow>
              ) : (
                data.data.map((product) => {
                  const isLow = product.stock_quantity <= product.reorder_level
                  const isOut = product.stock_quantity <= 0

                  return (
                    <TableRow key={product.id}>
                      <TableCell className="font-mono text-xs font-semibold text-slate-900">
                        {product.sku}
                      </TableCell>
                      <TableCell>
                        <div className="font-medium text-slate-900">{product.name}</div>
                        {product.description && (
                          <div className="text-xs text-slate-400 truncate max-w-xs">{product.description}</div>
                        )}
                      </TableCell>
                      <TableCell>
                        <span className="text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-medium">
                          {product.category}
                        </span>
                      </TableCell>
                      <TableCell>
                        <div className="text-sm font-semibold text-slate-900">
                          {formatCurrency(product.selling_price)}
                        </div>
                        <div className="text-xs text-slate-400">Cost: {formatCurrency(product.cost_price)}</div>
                      </TableCell>
                      <TableCell className="font-medium text-slate-700">{product.tax_rate}%</TableCell>
                      <TableCell>
                        {isOut ? (
                          <Badge variant="destructive">OUT OF STOCK</Badge>
                        ) : isLow ? (
                          <Badge variant="warning">
                            LOW: {product.stock_quantity} {product.unit}
                          </Badge>
                        ) : (
                          <Badge variant="success">
                            IN STOCK: {product.stock_quantity} {product.unit}
                          </Badge>
                        )}
                      </TableCell>
                      <TableCell className="text-right space-x-1">
                        {canManageProducts() && (
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => {
                              setStockModalProduct(product)
                              setStockDelta(10)
                              setStockReason('Warehouse restock')
                            }}
                          >
                            <SlidersHorizontal className="w-3.5 h-3.5 mr-1" />
                            Adjust Stock
                          </Button>
                        )}
                        {canDeleteProducts() && (
                          <Button
                            variant="ghost"
                            size="sm"
                            className="text-rose-600 hover:text-rose-700 hover:bg-rose-50"
                            onClick={() => {
                              if (confirm(`Are you sure you want to delete SKU ${product.sku}?`)) {
                                deleteMutation.mutate(product.id)
                              }
                            }}
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                          </Button>
                        )}
                      </TableCell>
                    </TableRow>
                  )
                })
              )}
            </TableBody>
          </Table>

          {data && (
            <PaginationControls
              currentPage={data.pagination.page}
              totalPages={data.pagination.total_pages}
              totalItems={data.pagination.total}
              limit={data.pagination.limit}
              onPageChange={setPage}
              onLimitChange={(l) => {
                setLimit(l)
                setPage(1)
              }}
            />
          )}
        </CardContent>
      </Card>

      {/* Adjust Stock Modal */}
      <Dialog open={!!stockModalProduct} onClose={() => setStockModalProduct(null)}>
        <DialogHeader>
          <DialogTitle>Adjust Stock: {stockModalProduct?.name}</DialogTitle>
          <DialogDescription>
            Current Stock: <span className="font-bold text-slate-900">{stockModalProduct?.stock_quantity} {stockModalProduct?.unit}</span>.
            Enter a positive delta to add stock, or a negative delta to deduct.
          </DialogDescription>
        </DialogHeader>
        <div className="space-y-4 py-4">
          <div className="space-y-1">
            <Label>Stock Delta (Adjustment Quantity)</Label>
            <Input
              type="number"
              value={stockDelta}
              onChange={(e) => setStockDelta(Number(e.target.value))}
            />
            <p className="text-xs text-slate-400">
              New Projected Stock: {(stockModalProduct?.stock_quantity ?? 0) + Number(stockDelta)}{' '}
              {stockModalProduct?.unit}
            </p>
          </div>
          <div className="space-y-1">
            <Label>Audit Adjustment Reason</Label>
            <Input
              type="text"
              value={stockReason}
              onChange={(e) => setStockReason(e.target.value)}
              placeholder="e.g. Supplier delivery PO-9821, or damaged goods return"
            />
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" onClick={() => setStockModalProduct(null)}>
            Cancel
          </Button>
          <Button onClick={handleStockAdjustment} isLoading={stockMutation.isPending}>
            Confirm Adjustment
          </Button>
        </DialogFooter>
      </Dialog>

      {/* Add Product Modal */}
      <Dialog open={isAddOpen} onClose={() => setIsAddOpen(false)} maxWidth="max-w-xl">
        <DialogHeader>
          <DialogTitle>Register New Product</DialogTitle>
          <DialogDescription>Create a new catalog item with pricing, tax rate and initial stock</DialogDescription>
        </DialogHeader>
        <form onSubmit={handleSubmit(onAddSubmit)} className="space-y-4 py-4">
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-1">
              <Label>SKU Code</Label>
              <Input placeholder="e.g. HD-RAM-32GB" error={errors.sku?.message} {...register('sku')} />
            </div>
            <div className="space-y-1">
              <Label>Category</Label>
              <Input placeholder="Hardware, Software..." error={errors.category?.message} {...register('category')} />
            </div>
          </div>

          <div className="space-y-1">
            <Label>Product Name</Label>
            <Input placeholder="Enterprise Product Description" error={errors.name?.message} {...register('name')} />
          </div>

          <div className="grid grid-cols-3 gap-4">
            <div className="space-y-1">
              <Label>Cost Price ($)</Label>
              <Input type="number" step="0.01" error={errors.cost_price?.message} {...register('cost_price')} />
            </div>
            <div className="space-y-1">
              <Label>Selling Price ($)</Label>
              <Input type="number" step="0.01" error={errors.selling_price?.message} {...register('selling_price')} />
            </div>
            <div className="space-y-1">
              <Label>Tax Rate (%)</Label>
              <Input type="number" step="0.1" error={errors.tax_rate?.message} {...register('tax_rate')} />
            </div>
          </div>

          <div className="grid grid-cols-3 gap-4">
            <div className="space-y-1">
              <Label>Initial Stock</Label>
              <Input type="number" error={errors.stock_quantity?.message} {...register('stock_quantity')} />
            </div>
            <div className="space-y-1">
              <Label>Reorder Level</Label>
              <Input type="number" error={errors.reorder_level?.message} {...register('reorder_level')} />
            </div>
            <div className="space-y-1">
              <Label>Unit of Measure</Label>
              <Input placeholder="pcs, unit, box" error={errors.unit?.message} {...register('unit')} />
            </div>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" onClick={() => setIsAddOpen(false)}>
              Cancel
            </Button>
            <Button type="submit" isLoading={createMutation.isPending}>
              Save Product
            </Button>
          </DialogFooter>
        </form>
      </Dialog>
    </div>
  )
}
