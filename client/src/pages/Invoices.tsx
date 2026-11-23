import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import {
  FileText,
  Search,
  CheckCircle2,
  Printer,
  Download,
  ShieldCheck,
  Building,
  CreditCard,
  AlertCircle,
} from 'lucide-react'
import { invoiceApi } from '@/api/services'
import { useAuthStore } from '@/store/authStore'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { PaginationControls } from '@/components/ui/pagination'
import { Dialog, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Invoice, PaginatedResult } from '@/types'

export function Invoices() {
  const queryClient = useQueryClient()
  const { user } = useAuthStore()

  const [page, setPage] = useState(1)
  const [limit, setLimit] = useState(10)
  const [status, setStatus] = useState('')
  const [search, setSearch] = useState('')

  const [selectedInvoice, setSelectedInvoice] = useState<Invoice | null>(null)
  const [feedback, setFeedback] = useState<{ type: 'success' | 'error'; message: string } | null>(null)

  // Fetch paginated invoices
  const { data, isLoading } = useQuery({
    queryKey: ['invoices', page, limit, status, search],
    queryFn: () => invoiceApi.getAll({ page, limit, status, search }) as Promise<PaginatedResult<Invoice>>,
  })

  // Mark invoice as paid
  const payMutation = useMutation({
    mutationFn: invoiceApi.pay,
    onSuccess: (updated) => {
      queryClient.invalidateQueries({ queryKey: ['invoices'] })
      queryClient.invalidateQueries({ queryKey: ['dashboard-metrics'] })
      queryClient.invalidateQueries({ queryKey: ['customers'] })
      setSelectedInvoice(updated)
      setFeedback({ type: 'success', message: `Invoice ${updated.invoice_number} marked as PAID!` })
      setTimeout(() => setFeedback(null), 4000)
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.message || 'Payment update failed' })
    },
  })

  const handlePrint = () => {
    window.print()
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight text-slate-900">Authoritative Invoices & Billing</h1>
          <p className="text-sm text-slate-500">
            Immutable financial documents generated and authoritatively stamped by the PHP core
          </p>
        </div>
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
              placeholder="Search by Invoice # or Customer..."
              className="pl-9"
              value={search}
              onChange={(e) => {
                setSearch(e.target.value)
                setPage(1)
              }}
            />
          </div>

          <div className="flex items-center space-x-2">
            <span className="text-xs text-slate-500 font-medium">Status:</span>
            <select
              value={status}
              onChange={(e) => {
                setStatus(e.target.value)
                setPage(1)
              }}
              className="text-xs border border-slate-300 rounded px-2.5 py-1.5 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium"
            >
              <option value="">All Invoices</option>
              <option value="unpaid">Unpaid</option>
              <option value="paid">Paid</option>
            </select>
          </div>
        </CardContent>
      </Card>

      {/* Invoices Table */}
      <Card>
        <CardContent className="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Invoice #</TableHead>
                <TableHead>Customer</TableHead>
                <TableHead>Subtotal</TableHead>
                <TableHead>Tax Amount</TableHead>
                <TableHead>Total Amount</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Due Date</TableHead>
                <TableHead className="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {isLoading ? (
                <TableRow>
                  <TableCell colSpan={8} className="h-32 text-center text-slate-500">
                    Loading invoices...
                  </TableCell>
                </TableRow>
              ) : !data || data.data.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={8} className="h-32 text-center text-slate-500">
                    No invoices generated yet.
                  </TableCell>
                </TableRow>
              ) : (
                data.data.map((invoice) => (
                  <TableRow key={invoice.id}>
                    <TableCell className="font-mono text-xs font-bold text-blue-600">
                      {invoice.invoice_number}
                    </TableCell>
                    <TableCell className="font-medium text-slate-900">{invoice.customer_name}</TableCell>
                    <TableCell className="text-slate-600">{formatCurrency(invoice.subtotal)}</TableCell>
                    <TableCell className="text-slate-600 font-medium">{formatCurrency(invoice.tax_amount)}</TableCell>
                    <TableCell className="font-bold text-slate-900">
                      {formatCurrency(invoice.total_amount)}
                    </TableCell>
                    <TableCell>
                      <Badge variant={invoice.status === 'paid' ? 'success' : 'warning'}>
                        {invoice.status.toUpperCase()}
                      </Badge>
                    </TableCell>
                    <TableCell className="text-xs text-slate-500">{invoice.due_date}</TableCell>
                    <TableCell className="text-right space-x-2">
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={async () => {
                          const full = await invoiceApi.getById(invoice.id)
                          setSelectedInvoice(full)
                        }}
                      >
                        <FileText className="w-3.5 h-3.5 mr-1" />
                        View / Print
                      </Button>
                      {invoice.status !== 'paid' && ['admin', 'manager', 'accountant'].includes(user?.role || '') && (
                        <Button
                          variant="success"
                          size="sm"
                          isLoading={payMutation.isPending}
                          onClick={() => payMutation.mutate(invoice.id)}
                        >
                          <CreditCard className="w-3.5 h-3.5 mr-1" />
                          Mark Paid
                        </Button>
                      )}
                    </TableCell>
                  </TableRow>
                ))
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

      {/* Authoritative Invoice View / Print Modal */}
      <Dialog open={!!selectedInvoice} onClose={() => setSelectedInvoice(null)} maxWidth="max-w-3xl">
        <div id="authoritative-invoice" className="p-2 space-y-6">
          {/* Header */}
          <div className="flex justify-between items-start border-b border-slate-200 pb-6">
            <div className="space-y-1">
              <div className="flex items-center space-x-2">
                <div className="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold">
                  AV
                </div>
                <h2 className="text-xl font-bold text-slate-900">AVO ERP ENTERPRISE</h2>
              </div>
              <p className="text-xs text-slate-500">100 Enterprise Way, Suite 800, Silicon Valley, CA</p>
              <p className="text-xs text-slate-500">Tax ID: US-EIN-9844201 | billing@avoerp.com</p>
            </div>
            <div className="text-right space-y-1">
              <h3 className="text-2xl font-extrabold text-blue-700 tracking-tight">INVOICE</h3>
              <p className="font-mono text-sm font-bold text-slate-900">{selectedInvoice?.invoice_number}</p>
              <Badge variant={selectedInvoice?.status === 'paid' ? 'success' : 'warning'}>
                {selectedInvoice?.status.toUpperCase()}
              </Badge>
            </div>
          </div>

          {/* Billing & Order details */}
          <div className="grid grid-cols-2 gap-6 text-xs">
            <div>
              <p className="font-bold text-slate-400 uppercase tracking-wider mb-1">Billed To:</p>
              <p className="font-bold text-sm text-slate-900">{selectedInvoice?.customer_name}</p>
              {selectedInvoice?.customer?.company && (
                <p className="text-slate-600">{selectedInvoice.customer.company}</p>
              )}
              {selectedInvoice?.customer?.address && (
                <p className="text-slate-500">{selectedInvoice.customer.address}</p>
              )}
              {selectedInvoice?.customer?.tax_number && (
                <p className="text-slate-500 font-mono mt-1">Tax ID: {selectedInvoice.customer.tax_number}</p>
              )}
            </div>

            <div className="text-right space-y-1">
              <p>
                <span className="text-slate-400 font-semibold">Invoice Date:</span>{' '}
                <span className="font-medium text-slate-900">{selectedInvoice && formatDate(selectedInvoice.issued_at)}</span>
              </p>
              <p>
                <span className="text-slate-400 font-semibold">Payment Due:</span>{' '}
                <span className="font-bold text-slate-900">{selectedInvoice?.due_date}</span>
              </p>
              <p>
                <span className="text-slate-400 font-semibold">Order Reference:</span>{' '}
                <span className="font-mono font-medium text-blue-600">{selectedInvoice?.order?.order_number}</span>
              </p>
            </div>
          </div>

          {/* Items Table */}
          <div className="border border-slate-200 rounded-lg overflow-hidden">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>SKU</TableHead>
                  <TableHead>Description</TableHead>
                  <TableHead className="text-right">Qty</TableHead>
                  <TableHead className="text-right">Unit Price</TableHead>
                  <TableHead className="text-right">Tax Rate</TableHead>
                  <TableHead className="text-right">Tax Amount</TableHead>
                  <TableHead className="text-right">Total Amount</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {selectedInvoice?.order?.items?.map((item, idx) => (
                  <TableRow key={idx}>
                    <TableCell className="font-mono text-xs">{item.sku}</TableCell>
                    <TableCell className="font-medium">{item.product_name}</TableCell>
                    <TableCell className="text-right">{item.quantity}</TableCell>
                    <TableCell className="text-right">{formatCurrency(item.unit_price)}</TableCell>
                    <TableCell className="text-right">{item.tax_rate}%</TableCell>
                    <TableCell className="text-right">{formatCurrency(item.tax_amount)}</TableCell>
                    <TableCell className="text-right font-bold">{formatCurrency(item.line_total)}</TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>

          {/* Totals & Authoritative Checksum */}
          <div className="flex flex-col sm:flex-row justify-between items-end gap-4 pt-2">
            <div className="p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs space-y-1 w-full sm:w-96">
              <div className="flex items-center text-slate-700 font-semibold">
                <ShieldCheck className="w-4 h-4 mr-1 text-emerald-600" />
                PHP Authoritative Cryptographic Checksum:
              </div>
              <p className="font-mono text-[10px] text-slate-500 break-all bg-white p-1.5 rounded border border-slate-200">
                {selectedInvoice?.checksum}
              </p>
              <p className="text-[10px] text-slate-400 italic">
                Verified tamper-proof digital invoice authority.
              </p>
            </div>

            <div className="w-full sm:w-64 space-y-2 text-sm">
              <div className="flex justify-between text-slate-600">
                <span>Subtotal:</span>
                <span className="font-medium">{selectedInvoice && formatCurrency(selectedInvoice.subtotal)}</span>
              </div>
              <div className="flex justify-between text-slate-600">
                <span>Final Calculated Tax:</span>
                <span className="font-medium">{selectedInvoice && formatCurrency(selectedInvoice.tax_amount)}</span>
              </div>
              <div className="flex justify-between text-lg font-bold text-slate-900 border-t border-slate-300 pt-2">
                <span>Invoice Total:</span>
                <span className="text-blue-700">{selectedInvoice && formatCurrency(selectedInvoice.total_amount)}</span>
              </div>
            </div>
          </div>
        </div>

        <DialogFooter className="mt-4">
          <Button variant="outline" onClick={() => setSelectedInvoice(null)}>
            Close
          </Button>
          <Button variant="secondary" onClick={handlePrint}>
            <Printer className="w-4 h-4 mr-1.5" />
            Print / Save PDF
          </Button>
          {selectedInvoice?.status !== 'paid' && ['admin', 'manager', 'accountant'].includes(user?.role || '') && (
            <Button
              variant="success"
              isLoading={payMutation.isPending}
              onClick={() => selectedInvoice && payMutation.mutate(selectedInvoice.id)}
            >
              <CreditCard className="w-4 h-4 mr-1.5" />
              Mark as Paid
            </Button>
          )}
        </DialogFooter>
      </Dialog>
    </div>
  )
}

