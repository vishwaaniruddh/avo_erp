import { useParams, useNavigate, Link } from 'react-router-dom'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import {
  ArrowLeft,
  Printer,
  FileCheck2,
  Building2,
  Calendar,
  Layers,
  Wrench,
  ShieldCheck,
  AlertTriangle,
  Clock,
  UserCheck,
  Percent,
  Edit2,
  CheckCircle2,
} from 'lucide-react'
import { salesApi } from '@/api/services'
import { useAuthStore } from '@/store/authStore'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import type { SalesQuotation } from '@/types'

export function ViewQuotationPage() {
  const { id } = useParams<{ id: string }>()
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const { user: currentUser } = useAuthStore()

  const quoteId = Number(id)

  const {
    data: quote,
    isLoading,
    error,
  } = useQuery<any>({
    queryKey: ['sales-quote-detail', quoteId],
    queryFn: () => salesApi.getQuoteById(quoteId),
    enabled: !!quoteId,
  })

  // Convert to SO Mutation
  const convertMutation = useMutation({
    mutationFn: () => salesApi.convertQuoteToSO(quoteId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['sales-quotes'] })
      queryClient.invalidateQueries({ queryKey: ['sales-quote-detail', quoteId] })
      navigate('/sales/milestones')
    },
  })

  // L3 VP Approve / Reject Mutation
  const approveMutation = useMutation({
    mutationFn: ({ approved, remarks }: { approved: boolean; remarks?: string }) =>
      salesApi.approveQuote(quoteId, approved, remarks),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['sales-quotes'] })
      queryClient.invalidateQueries({ queryKey: ['sales-quote-detail', quoteId] })
    },
  })

  const isL3VP =
    currentUser?.level === 'L3' ||
    currentUser?.role_type === 'Executive' ||
    currentUser?.role_type === 'Admin'

  if (isLoading) {
    return (
      <div className="flex h-96 items-center justify-center">
        <div className="text-center">
          <div className="w-8 h-8 border-4 border-zinc-900 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
          <p className="text-xs text-zinc-500">Loading Quotation #{quoteId}...</p>
        </div>
      </div>
    )
  }

  if (error || !quote) {
    return (
      <div className="max-w-xl mx-auto py-16 text-center">
        <AlertTriangle className="w-10 h-10 text-amber-600 mx-auto mb-3" />
        <h2 className="text-sm font-bold text-zinc-900">Quotation Not Found</h2>
        <p className="text-xs text-zinc-500 mt-1 mb-4">
          The requested quotation does not exist or you do not have permission to view it.
        </p>
        <Link to="/sales/quotes">
          <Button size="sm" variant="outline" className="h-8 text-xs">
            <ArrowLeft className="w-3.5 h-3.5 mr-1" />
            Back to Quotation Portfolio
          </Button>
        </Link>
      </div>
    )
  }

  const items = quote.items || []
  const subtotal = Number(quote.subtotal_amount || 0)
  const discount = Number(quote.discount_amount || 0)
  const installationCharges = Number(quote.installation_charges || 0)
  const amcCharges = Number(quote.amc_charges || 0)
  const finalAmount = Number(quote.final_quote_amount || 0)
  const estimatedGst = Math.round(finalAmount * 0.18)
  const grandTotal = finalAmount + estimatedGst
  const totalMspFloor = Number(quote.total_msp_floor || 0)
  const isBelowMsp = Boolean(quote.is_below_msp)
  const isApproved = quote.approval_status === 'Approved' || quote.approval_status === 'Auto_Approved'

  const handlePrint = () => {
    window.print()
  }

  return (
    <div className="space-y-4 max-w-6xl mx-auto pb-16 print:p-0 print:m-0 print:max-w-full">
      {/* Top Header & Breadcrumb (Hidden during browser print) */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-zinc-200 pb-3 print:hidden">
        <div className="space-y-1">
          <div className="flex items-center space-x-2 text-xs text-zinc-500">
            <Link to="/sales/quotes" className="hover:text-zinc-900 transition-colors flex items-center gap-1">
              <ArrowLeft className="w-3.5 h-3.5" />
              <span>Back to Quotation Portfolio</span>
            </Link>
            <span>/</span>
            <span className="text-zinc-800 font-mono font-medium">{quote.quote_number}</span>
          </div>
          <div className="flex flex-wrap items-center gap-2">
            <h1 className="text-base sm:text-lg font-bold tracking-tight text-zinc-900 font-mono">
              {quote.quote_number}
            </h1>
            <span
              className={`text-[10px] font-mono font-bold px-2 py-0.5 rounded ${
                quote.approval_status === 'Auto_Approved'
                  ? 'bg-zinc-900 text-white'
                  : quote.approval_status === 'Approved'
                  ? 'bg-emerald-100 text-emerald-900 border border-emerald-300'
                  : quote.approval_status === 'Pending_VP_Approval'
                  ? 'bg-amber-100 text-amber-900 border border-amber-300 animate-pulse'
                  : 'bg-rose-100 text-rose-900 border border-rose-300'
              }`}
            >
              {quote.approval_status.replace(/_/g, ' ')}
            </span>
            {isBelowMsp ? (
              <span className="text-[10px] font-mono font-semibold px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200">
                &lt; Floor MSP (Exception Authorized)
              </span>
            ) : (
              <span className="text-[10px] font-mono font-semibold px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200">
                &ge; Floor MSP Compliant
              </span>
            )}
          </div>
        </div>

        {/* Action Buttons */}
        <div className="flex flex-wrap items-center gap-2">
          <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={() => navigate(`/sales/quotes/${quote.id}/edit`)}
            className="h-8 text-xs bg-white text-zinc-800 border-zinc-200 hover:bg-zinc-100"
          >
            <Edit2 className="w-3.5 h-3.5 mr-1.5" />
            Edit Quote
          </Button>

          <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={handlePrint}
            className="h-8 text-xs bg-white text-zinc-800 border-zinc-200 hover:bg-zinc-100"
          >
            <Printer className="w-3.5 h-3.5 mr-1.5" />
            Print / PDF
          </Button>

          {quote.converted_order_number ? (
            <Link to="/sales/milestones">
              <Button
                size="sm"
                variant="outline"
                className="h-8 text-xs bg-emerald-50 text-emerald-800 border-emerald-300 hover:bg-emerald-100 font-mono font-medium shadow-2xs"
                title={`Track Sales Order ${quote.converted_order_number} across 6 operational milestones`}
              >
                <CheckCircle2 className="w-3.5 h-3.5 mr-1.5 text-emerald-600" />
                SO: {quote.converted_order_number} &rarr;
              </Button>
            </Link>
          ) : isApproved ? (
            <Button
              size="sm"
              onClick={() => convertMutation.mutate()}
              disabled={convertMutation.isPending}
              className="h-8 text-xs bg-zinc-900 text-white hover:bg-zinc-800 font-medium shadow-2xs"
            >
              <FileCheck2 className="w-3.5 h-3.5 mr-1.5" />
              {convertMutation.isPending ? 'Converting...' : 'Convert to Sales Order'}
            </Button>
          ) : isL3VP && quote.approval_status === 'Pending_VP_Approval' ? (
            <div className="flex items-center gap-1.5">
              <Button
                size="sm"
                variant="outline"
                onClick={() => approveMutation.mutate({ approved: false, remarks: 'Pricing declined by L3 VP' })}
                disabled={approveMutation.isPending}
                className="h-8 text-xs border-red-200 text-red-700 hover:bg-red-50"
              >
                Reject Quote
              </Button>
              <Button
                size="sm"
                onClick={() => approveMutation.mutate({ approved: true, remarks: 'Strategic exception approved by L3 VP' })}
                disabled={approveMutation.isPending}
                className="h-8 text-xs bg-emerald-700 hover:bg-emerald-800 text-white font-medium shadow-2xs"
              >
                Authorize Below-MSP
              </Button>
            </div>
          ) : null}
        </div>
      </div>

      {/* Formal Header Block for Print & Proposal */}
      <Card className="border-zinc-200 shadow-2xs print:border-none print:shadow-none">
        <div className="p-5 border-b border-zinc-200 bg-zinc-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div className="space-y-1">
            <span className="text-[10px] font-mono uppercase font-bold text-zinc-400 tracking-wider">
              Commercial Quotation
            </span>
            <h2 className="text-base font-bold text-zinc-900">{quote.customer_name}</h2>
            <div className="flex items-center gap-3 text-xs text-zinc-500 font-medium">
              <span>Channel: <strong className="text-zinc-800">{quote.sale_type.replace('_', ' ')}</strong></span>
              {quote.lead_id ? (
                <span className="flex items-center gap-1">
                  &bull; Lead Ref:
                  <Link
                    to={`/sales/leads`}
                    className="font-mono text-zinc-900 underline font-semibold hover:text-blue-600 ml-1"
                    title="View Commercial Lead"
                  >
                    {quote.lead_number || `Lead #${quote.lead_id}`}
                  </Link>
                </span>
              ) : (
                <span className="text-zinc-400">&bull; Direct Commercial (No Lead)</span>
              )}
            </div>
          </div>

          <div className="sm:text-right font-mono text-xs space-y-1 text-zinc-600">
            <div>Quote No: <strong className="text-zinc-900">{quote.quote_number}</strong></div>
            <div>Quotation Date: <span className="text-zinc-900 font-semibold">{quote.quote_date ? new Date(quote.quote_date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) : (quote.created_at ? new Date(quote.created_at).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) : 'N/A')}</span></div>
            <div>Valid Until: <span className="text-zinc-900 font-semibold">{quote.valid_until || '30 Days'}</span></div>
            <div>Sales Representative: <span className="text-zinc-900">{quote.salesperson_name || 'Alexander Vance'}</span></div>
          </div>
        </div>

        {/* Customer Contact Details Strip */}
        {(quote.contact_person || quote.contact_phone || quote.contact_email) && (
          <div className="px-5 py-2.5 bg-white border-b border-zinc-100 flex flex-wrap items-center gap-6 text-xs text-zinc-600">
            {quote.contact_person && (
              <div>
                <span className="text-zinc-400 mr-1.5">Attention:</span>
                <strong className="text-zinc-800">{quote.contact_person}</strong>
              </div>
            )}
            {quote.contact_phone && (
              <div>
                <span className="text-zinc-400 mr-1.5">Phone:</span>
                <span className="font-mono text-zinc-800">{quote.contact_phone}</span>
              </div>
            )}
            {quote.contact_email && (
              <div>
                <span className="text-zinc-400 mr-1.5">Email:</span>
                <span className="font-mono text-zinc-800">{quote.contact_email}</span>
              </div>
            )}
          </div>
        )}

        {/* Equipment Line Items Table */}
        <div className="p-5 space-y-4">
          <div className="flex items-center gap-2">
            <Layers className="w-4 h-4 text-zinc-600" />
            <h3 className="text-xs font-bold text-zinc-900 uppercase tracking-wide">
              Equipment Line Items ({items.length} {items.length === 1 ? 'Item' : 'Items'})
            </h3>
          </div>

          <div className="overflow-x-auto border border-zinc-200 rounded-lg">
            <Table className="min-w-[800px]">
              <TableHeader className="bg-zinc-50/80 border-b border-zinc-200">
                <TableRow>
                  <TableHead className="w-10 text-center font-semibold text-zinc-600 text-[11px]">#</TableHead>
                  <TableHead className="font-semibold text-zinc-600 text-[11px]">Item Description</TableHead>
                  <TableHead className="w-32 font-semibold text-zinc-600 text-[11px]">Model / SKU</TableHead>
                  <TableHead className="w-16 text-center font-semibold text-zinc-600 text-[11px]">Qty</TableHead>
                  <TableHead className="w-44 font-semibold text-zinc-600 text-[11px]">Base Warranty</TableHead>
                  <TableHead className="w-28 text-right font-semibold text-zinc-600 text-[11px]">Unit Price (₹)</TableHead>
                  <TableHead className="w-40 font-semibold text-zinc-600 text-[11px]">Extended Warranty</TableHead>
                  <TableHead className="w-28 text-right font-semibold text-zinc-600 text-[11px]">Ext. Price (₹)</TableHead>
                  <TableHead className="w-32 text-right font-semibold text-zinc-900 text-[11px]">Line Total (₹)</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {items.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={9} className="text-center py-6 text-zinc-400 text-xs">
                      No line items specified.
                    </TableCell>
                  </TableRow>
                ) : (
                  items.map((item: any, idx: number) => {
                    const qty = Math.max(1, Number(item.quantity || 1))
                    const unitPrice = Number(item.quoted_unit_price || 0)
                    const extPrice = Number(item.extended_warranty_price || 0)
                    const lineTotal = Number(item.total_price || (unitPrice + extPrice) * qty)

                    return (
                      <TableRow key={item.id || idx} className="hover:bg-zinc-50/40">
                        <TableCell className="text-center font-mono text-xs font-semibold text-zinc-500">
                          #{idx + 1}
                        </TableCell>
                        <TableCell className="font-medium text-xs text-zinc-900">
                          {item.product_name}
                        </TableCell>
                        <TableCell className="font-mono text-xs text-zinc-600">
                          {item.product_sku || 'AVO-CUSTOM'}
                        </TableCell>
                        <TableCell className="text-center font-mono text-xs font-semibold text-zinc-800">
                          {qty}
                        </TableCell>
                        <TableCell className="text-xs text-zinc-600">
                          {item.warranty_terms || 'Standard 2-Year OEM'}
                        </TableCell>
                        <TableCell className="text-right font-mono text-xs text-zinc-800">
                          ₹{unitPrice.toLocaleString('en-IN')}
                        </TableCell>
                        <TableCell className="text-xs text-zinc-600">
                          {item.extended_warranty || 'None'}
                        </TableCell>
                        <TableCell className="text-right font-mono text-xs text-zinc-800">
                          {extPrice > 0 ? `₹${extPrice.toLocaleString('en-IN')}` : '-'}
                        </TableCell>
                        <TableCell className="text-right font-mono font-bold text-xs text-zinc-900">
                          ₹{lineTotal.toLocaleString('en-IN')}
                        </TableCell>
                      </TableRow>
                    )
                  })
                )}
              </TableBody>
            </Table>
          </div>
        </div>

        {/* Services & Value-Added Scope (If Included) */}
        {(installationCharges > 0 || amcCharges > 0) && (
          <div className="px-5 pb-5 space-y-3">
            <div className="flex items-center gap-2">
              <Wrench className="w-4 h-4 text-zinc-600" />
              <h3 className="text-xs font-bold text-zinc-900 uppercase tracking-wide">
                Services & Value-Added Support
              </h3>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
              {installationCharges > 0 && (
                <div className="p-3 rounded-lg border border-zinc-200 bg-zinc-50/60 flex items-center justify-between">
                  <div>
                    <span className="font-bold text-zinc-900 block">Site Installation, Testing & Commissioning</span>
                    <span className="text-[11px] text-zinc-500">On-site electrical placement, cabling & load testing</span>
                  </div>
                  <span className="font-mono font-bold text-zinc-900 text-sm">
                    ₹{installationCharges.toLocaleString('en-IN')}
                  </span>
                </div>
              )}

              {amcCharges > 0 && (
                <div className="p-3 rounded-lg border border-zinc-200 bg-zinc-50/60 flex items-center justify-between">
                  <div>
                    <span className="font-bold text-zinc-900 block">Annual Maintenance Contract (AMC) Support</span>
                    <span className="text-[11px] text-zinc-500">Comprehensive post-warranty SLA maintenance</span>
                  </div>
                  <span className="font-mono font-bold text-zinc-900 text-sm">
                    ₹{amcCharges.toLocaleString('en-IN')}
                  </span>
                </div>
              )}
            </div>
          </div>
        )}

        {/* Commercial Economics & Grand Total Breakdown */}
        <div className="p-5 border-t border-zinc-200 bg-zinc-50/40">
          <div className="flex flex-col sm:flex-row justify-between items-start gap-6">
            {/* Commercial Terms Column */}
            <div className="space-y-2 text-xs text-zinc-600 max-w-md">
              <h4 className="font-bold text-zinc-900 uppercase text-[11px]">Standard Commercial Terms</h4>
              <ul className="space-y-1 list-disc list-inside text-[11px] text-zinc-500">
                <li>Payment: 30% Advance, 70% against PI before dispatch</li>
                <li>Delivery Timeline: 2 to 3 Weeks from receipt of Purchase Order</li>
                <li>Comprehensive OEM warranty covers all internal modules & electronics</li>
                <li>Price validity: 30 days from date of issuance</li>
              </ul>
              {quote.approval_remarks && (
                <div className="pt-2">
                  <span className="font-semibold text-zinc-700 block text-[11px]">Approval Remarks / Strategic Rationale:</span>
                  <p className="text-[11px] text-zinc-600 italic bg-white p-2 rounded border border-zinc-200 mt-0.5">
                    "{quote.approval_remarks}"
                  </p>
                </div>
              )}
            </div>

            {/* Financial Tally Box */}
            <div className="w-full sm:w-80 space-y-2 text-xs font-mono">
              <div className="flex justify-between py-1 border-b border-zinc-200/80">
                <span className="text-zinc-600 font-sans">Commercial Subtotal:</span>
                <span className="font-semibold text-zinc-900">₹{subtotal.toLocaleString('en-IN')}</span>
              </div>

              {discount > 0 && (
                <div className="flex justify-between py-1 border-b border-zinc-200/80 text-emerald-700">
                  <span className="font-sans">Discount / Concession:</span>
                  <span className="font-bold">-₹{discount.toLocaleString('en-IN')}</span>
                </div>
              )}

              <div className="flex justify-between py-1 border-b border-zinc-200/80">
                <span className="text-zinc-800 font-bold font-sans">Net Commercial Quote:</span>
                <span className="font-bold text-zinc-900 text-sm">₹{finalAmount.toLocaleString('en-IN')}</span>
              </div>

              <div className="flex justify-between py-1 border-b border-zinc-200/80 text-zinc-500 text-[11px]">
                <span className="font-sans">Estimated GST (18%):</span>
                <span>₹{estimatedGst.toLocaleString('en-IN')}</span>
              </div>

              <div className="flex justify-between p-3 rounded bg-zinc-900 text-white font-bold text-sm">
                <span className="uppercase text-xs font-sans tracking-wider">Grand Total:</span>
                <span>₹{grandTotal.toLocaleString('en-IN')}</span>
              </div>
            </div>
          </div>
        </div>

        {/* Dynamic Floor MSP Governance Summary Card */}
        <div className="px-5 py-3 border-t border-zinc-200 bg-zinc-100/50 flex flex-wrap items-center justify-between gap-4 text-xs font-mono">
          <div className="flex items-center gap-4">
            <div>
              <span className="text-zinc-500 text-[10px] block">TOTAL FLOOR MSP</span>
              <span className="font-bold text-zinc-800">₹{totalMspFloor.toLocaleString('en-IN')}</span>
            </div>
            <div>
              <span className="text-zinc-500 text-[10px] block">GROSS MARGIN</span>
              <span className="font-bold text-zinc-900">{quote.gross_margin_percent}%</span>
            </div>
            <div>
              <span className="text-zinc-500 text-[10px] block">CPQ GATE</span>
              <span className="font-bold text-zinc-900">
                {isBelowMsp ? 'Below-Floor Exception' : 'Auto-Authorized'}
              </span>
            </div>
          </div>

          <div className="flex items-center gap-2">
            <span className="text-zinc-500 text-[11px] font-sans">Authorized By:</span>
            <span className="font-sans font-bold text-zinc-900">
              {quote.approved_by_name || (isApproved ? 'System Auto-Engine' : 'Pending VP')}
            </span>
          </div>
        </div>
      </Card>
    </div>
  )
}
