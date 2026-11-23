import { useState } from 'react'
import { Link } from 'react-router-dom'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import {
  ShoppingCart,
  Plus,
  Search,
  Check,
  X,
  FileText,
  Eye,
  AlertCircle,
  CheckCircle2,
  Lock,
} from 'lucide-react'
import { orderApi, invoiceApi } from '@/api/services'
import { useAuthStore } from '@/store/authStore'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { PaginationControls } from '@/components/ui/pagination'
import { Dialog, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Order, PaginatedResult } from '@/types'

const STATUS_BADGES: Record<string, 'default' | 'secondary' | 'warning' | 'success' | 'destructive' | 'info'> = {
  draft: 'secondary',
  pending_approval: 'warning',
  approved: 'default',
  invoiced: 'success',
  rejected: 'destructive',
}

export function Orders() {
  const queryClient = useQueryClient()
  const { user, canApproveOrders, canGenerateInvoices } = useAuthStore()

  const [page, setPage] = useState(1)
  const [limit, setLimit] = useState(10)
  const [status, setStatus] = useState('')
  const [search, setSearch] = useState('')

  const [selectedOrder, setSelectedOrder] = useState<Order | null>(null)
  const [rejectModalOrder, setRejectModalOrder] = useState<Order | null>(null)
  const [rejectReason, setRejectReason] = useState('Credit score or delivery term mismatch')
  const [feedback, setFeedback] = useState<{ type: 'success' | 'error'; message: string } | null>(null)

  // Fetch paginated orders
  const { data, isLoading } = useQuery({
    queryKey: ['orders', page, limit, status, search],
    queryFn: () => orderApi.getAll({ page, limit, status, search }) as Promise<PaginatedResult<Order>>,
  })

  // Approve Order Mutation (Approval workflow: React display actions, PHP control workflow)
  const approveMutation = useMutation({
    mutationFn: orderApi.approve,
    onSuccess: (updated) => {
      queryClient.invalidateQueries({ queryKey: ['orders'] })
      queryClient.invalidateQueries({ queryKey: ['dashboard-metrics'] })
      queryClient.invalidateQueries({ queryKey: ['products'] })
      setSelectedOrder(null)
      setFeedback({ type: 'success', message: `Order ${updated.order_number} approved & inventory decremented!` })
      setTimeout(() => setFeedback(null), 4000)
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.message || 'Approval rejected by server (RBAC / Stock check)' })
    },
  })

  // Reject Order Mutation
  const rejectMutation = useMutation({
    mutationFn: ({ id, reason }: { id: number; reason: string }) => orderApi.reject(id, reason),
    onSuccess: (updated) => {
      queryClient.invalidateQueries({ queryKey: ['orders'] })
      queryClient.invalidateQueries({ queryKey: ['dashboard-metrics'] })
      setRejectModalOrder(null)
      setSelectedOrder(null)
      setFeedback({ type: 'success', message: `Order ${updated.order_number} rejected.` })
      setTimeout(() => setFeedback(null), 4000)
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.message || 'Failed to reject order' })
    },
  })

  // Generate Invoice Mutation (Authoritative invoice generation)
  const invoiceMutation = useMutation({
    mutationFn: (orderId: number) => invoiceApi.generate(orderId),
    onSuccess: (inv) => {
      queryClient.invalidateQueries({ queryKey: ['orders'] })
      queryClient.invalidateQueries({ queryKey: ['invoices'] })
      queryClient.invalidateQueries({ queryKey: ['dashboard-metrics'] })
      setSelectedOrder(null)
      setFeedback({
        type: 'success',
        message: `Authoritative Invoice ${inv.invoice_number} created with SHA-256 Checksum!`,
      })
      setTimeout(() => setFeedback(null), 5000)
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.message || 'Invoice generation failed' })
    },
  })

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight text-slate-900">Sales Order Pipeline</h1>
          <p className="text-sm text-slate-500">
            Multi-stage approval workflow with automated credit and inventory validation
          </p>
        </div>
        <Link to="/orders/new">
          <Button>
            <Plus className="w-4 h-4 mr-2" />
            Create Sales Order
          </Button>
        </Link>
      </div>

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
              placeholder="Search by Order # or Customer..."
              className="pl-9"
              value={search}
              onChange={(e) => {
                setSearch(e.target.value)
                setPage(1)
              }}
            />
          </div>

          <div className="flex items-center space-x-2">
            <span className="text-xs text-slate-500 font-medium">Status Filter:</span>
            <select
              value={status}
              onChange={(e) => {
                setStatus(e.target.value)
                setPage(1)
              }}
              className="text-xs border border-slate-300 rounded px-2.5 py-1.5 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium"
            >
              <option value="">All Orders</option>
              <option value="pending_approval">Pending Approval</option>
              <option value="approved">Approved</option>
              <option value="invoiced">Invoiced</option>
              <option value="rejected">Rejected</option>
              <option value="draft">Draft</option>
            </select>
          </div>
        </CardContent>
      </Card>

      {/* Orders Table */}
      <Card>
        <CardContent className="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Order #</TableHead>
                <TableHead>Customer</TableHead>
                <TableHead>Grand Total</TableHead>
                <TableHead>Created By</TableHead>
                <TableHead>Workflow Status</TableHead>
                <TableHead>Date</TableHead>
                <TableHead className="text-right">Approval Workflow Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {isLoading ? (
                <TableRow>
                  <TableCell colSpan={7} className="h-32 text-center text-slate-500">
                    Loading sales orders...
                  </TableCell>
                </TableRow>
              ) : !data || data.data.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={7} className="h-32 text-center text-slate-500">
                    No orders found.
                  </TableCell>
                </TableRow>
              ) : (
                data.data.map((order) => {
                  const isPending = order.status === 'pending_approval'
                  const isApproved = order.status === 'approved'

                  return (
                    <TableRow key={order.id}>
                      <TableCell className="font-mono text-xs font-bold text-blue-600">
                        {order.order_number}
                      </TableCell>
                      <TableCell className="font-medium text-slate-900">{order.customer_name}</TableCell>
                      <TableCell className="font-bold text-slate-900">
                        {formatCurrency(order.grand_total)}
                      </TableCell>
                      <TableCell className="text-xs text-slate-500">{order.created_by_name}</TableCell>
                      <TableCell>
                        <Badge variant={STATUS_BADGES[order.status] || 'default'}>
                          {order.status.replace('_', ' ').toUpperCase()}
                        </Badge>
                      </TableCell>
                      <TableCell className="text-xs text-slate-400">{formatDate(order.created_at)}</TableCell>
                      <TableCell className="text-right space-x-1.5">
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={async () => {
                            const full = await orderApi.getById(order.id)
                            setSelectedOrder(full)
                          }}
                        >
                          <Eye className="w-3.5 h-3.5 mr-1" />
                          View
                        </Button>

                        {/* Approval workflow actions: React displays actions, PHP controls workflow */}
                        {isPending && canApproveOrders() && (
                          <>
                            <Button
                              variant="success"
                              size="sm"
                              isLoading={approveMutation.isPending}
                              onClick={() => approveMutation.mutate(order.id)}
                            >
                              <Check className="w-3.5 h-3.5 mr-1" />
                              Approve
                            </Button>
                            <Button
                              variant="destructive"
                              size="sm"
                              onClick={() => setRejectModalOrder(order)}
                            >
                              <X className="w-3.5 h-3.5 mr-1" />
                              Reject
                            </Button>
                          </>
                        )}

                        {isPending && !canApproveOrders() && (
                          <span className="text-[11px] text-slate-400 italic inline-flex items-center">
                            <Lock className="w-3 h-3 mr-1" />
                            Awaiting Manager
                          </span>
                        )}

                        {isApproved && canGenerateInvoices() && (
                          <Button
                            variant="default"
                            size="sm"
                            className="bg-indigo-600 hover:bg-indigo-700"
                            isLoading={invoiceMutation.isPending}
                            onClick={() => invoiceMutation.mutate(order.id)}
                          >
                            <FileText className="w-3.5 h-3.5 mr-1" />
                            Generate Invoice
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

      {/* View Order Modal */}
      <Dialog open={!!selectedOrder} onClose={() => setSelectedOrder(null)} maxWidth="max-w-2xl">
        <DialogHeader>
          <div className="flex items-center justify-between pr-8">
            <DialogTitle>Order Details: {selectedOrder?.order_number}</DialogTitle>
            {selectedOrder && (
              <Badge variant={STATUS_BADGES[selectedOrder.status] || 'default'}>
                {selectedOrder.status.replace('_', ' ').toUpperCase()}
              </Badge>
            )}
          </div>
          <DialogDescription>
            Customer: <span className="font-semibold text-slate-900">{selectedOrder?.customer_name}</span> | Placed on:{' '}
            {selectedOrder && formatDate(selectedOrder.created_at)}
          </DialogDescription>
        </DialogHeader>

        {selectedOrder && (
          <div className="py-4 space-y-4">
            {/* Line items */}
            <div className="border border-slate-200 rounded-lg overflow-hidden">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>SKU</TableHead>
                    <TableHead>Item</TableHead>
                    <TableHead>Qty</TableHead>
                    <TableHead>Price</TableHead>
                    <TableHead>Tax</TableHead>
                    <TableHead className="text-right">Line Total</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {selectedOrder.items?.map((item, idx) => (
                    <TableRow key={idx}>
                      <TableCell className="font-mono text-xs">{item.sku}</TableCell>
                      <TableCell className="font-medium">{item.product_name}</TableCell>
                      <TableCell>{item.quantity}</TableCell>
                      <TableCell>{formatCurrency(item.unit_price)}</TableCell>
                      <TableCell className="text-xs text-slate-500">
                        {item.tax_rate}% ({formatCurrency(item.tax_amount)})
                      </TableCell>
                      <TableCell className="text-right font-semibold">
                        {formatCurrency(item.line_total)}
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </div>

            {/* Calculations Breakdown */}
            <div className="bg-slate-50 p-4 rounded-xl space-y-1.5 text-sm">
              <div className="flex justify-between text-slate-600">
                <span>Subtotal:</span>
                <span className="font-medium">{formatCurrency(selectedOrder.subtotal)}</span>
              </div>
              <div className="flex justify-between text-slate-600">
                <span>Authoritative Calculated Tax:</span>
                <span className="font-medium">{formatCurrency(selectedOrder.tax_amount)}</span>
              </div>
              {selectedOrder.discount_amount > 0 && (
                <div className="flex justify-between text-emerald-600">
                  <span>Discount Applied:</span>
                  <span>-{formatCurrency(selectedOrder.discount_amount)}</span>
                </div>
              )}
              <div className="flex justify-between text-base font-bold text-slate-900 pt-2 border-t border-slate-200">
                <span>Authoritative Grand Total:</span>
                <span>{formatCurrency(selectedOrder.grand_total)}</span>
              </div>
            </div>

            {selectedOrder.notes && (
              <div className="text-xs text-slate-500 p-3 bg-white border border-slate-200 rounded-lg">
                <span className="font-semibold text-slate-700">Order Notes:</span> {selectedOrder.notes}
              </div>
            )}

            {selectedOrder.rejection_reason && (
              <div className="text-xs text-rose-700 p-3 bg-rose-50 border border-rose-200 rounded-lg">
                <span className="font-semibold">Rejection Reason:</span> {selectedOrder.rejection_reason}
              </div>
            )}
          </div>
        )}

        <DialogFooter>
          <Button variant="outline" onClick={() => setSelectedOrder(null)}>
            Close
          </Button>
          {selectedOrder?.status === 'pending_approval' && canApproveOrders() && (
            <Button
              variant="success"
              isLoading={approveMutation.isPending}
              onClick={() => approveMutation.mutate(selectedOrder.id)}
            >
              <Check className="w-4 h-4 mr-1.5" />
              Approve Order
            </Button>
          )}
          {selectedOrder?.status === 'approved' && canGenerateInvoices() && (
            <Button
              variant="default"
              className="bg-indigo-600 hover:bg-indigo-700"
              isLoading={invoiceMutation.isPending}
              onClick={() => invoiceMutation.mutate(selectedOrder.id)}
            >
              <FileText className="w-4 h-4 mr-1.5" />
              Generate Authoritative Invoice
            </Button>
          )}
        </DialogFooter>
      </Dialog>

      {/* Reject Modal */}
      <Dialog open={!!rejectModalOrder} onClose={() => setRejectModalOrder(null)}>
        <DialogHeader>
          <DialogTitle>Reject Order: {rejectModalOrder?.order_number}</DialogTitle>
          <DialogDescription>Please provide a reason for rejecting this order workflow</DialogDescription>
        </DialogHeader>
        <div className="py-4 space-y-2">
          <Label>Rejection Reason</Label>
          <Input
            value={rejectReason}
            onChange={(e) => setRejectReason(e.target.value)}
            placeholder="Reason for rejection..."
          />
        </div>
        <DialogFooter>
          <Button variant="outline" onClick={() => setRejectModalOrder(null)}>
            Cancel
          </Button>
          <Button
            variant="destructive"
            isLoading={rejectMutation.isPending}
            onClick={() => {
              if (rejectModalOrder) {
                rejectMutation.mutate({ id: rejectModalOrder.id, reason: rejectReason })
              }
            }}
          >
            Confirm Rejection
          </Button>
        </DialogFooter>
      </Dialog>
    </div>
  )
}
