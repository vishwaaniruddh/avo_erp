import { useState, useMemo, useEffect } from 'react'
import { useNavigate, useSearchParams, useParams, Link } from 'react-router-dom'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import {
  ArrowLeft,
  Plus,
  Trash2,
  Copy,
  Calculator,
  ShieldCheck,
  AlertTriangle,
  Building2,
  Layers,
  Wrench,
  Sparkles,
} from 'lucide-react'
import { salesApi, productApi } from '@/api/services'
import { useAuthStore } from '@/store/authStore'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { AccessDeniedPage } from './AccessDeniedPage'
import { isValidPhone, isValidEmail, sanitizePhoneInput, getPhoneError, getEmailError } from '@/lib/validation'
import type { SalesLead, SalesQuotation } from '@/types'

interface QuoteLineItem {
  id: string
  product_id?: number
  product_name: string
  product_sku: string
  quantity: number
  warranty_terms: string
  quoted_unit_price: number
  extended_warranty: string
  extended_warranty_price: number
  unit_cost_price?: number
  unit_msp_floor?: number
}

const WARRANTY_OPTIONS = [
  'Standard 2-Year OEM Warranty',
  '1-Year Comprehensive Warranty',
  '3-Year Comprehensive Warranty',
  '5-Year Mission-Critical Warranty',
  'No Warranty / As-Is',
]

const EXTENDED_WARRANTY_OPTIONS = [
  'None',
  '+1 Year Extended Warranty',
  '+2 Years Extended Warranty',
  '+3 Years Extended Warranty',
]

export function CreateQuotationPage() {
  const navigate = useNavigate()
  const { id } = useParams<{ id?: string }>()
  const [searchParams] = useSearchParams()
  const queryClient = useQueryClient()
  const { user: currentUser, canCreateSalesQuotes } = useAuthStore()

  const isEditMode = Boolean(id)
  const quoteId = id ? Number(id) : undefined

  if (!canCreateSalesQuotes()) {
    return (
      <AccessDeniedPage
        moduleName="Commercial Quotation Generation"
        requiredRole="Sales Representative / Commercial Lead / Executive"
      />
    )
  }

  const leadIdParam = searchParams.get('lead_id')

  // Fetch products from database
  const { data: dbProducts = [] } = useQuery({
    queryKey: ['products-catalog-all'],
    queryFn: () => productApi.getAll({ all: true }).then((res: any) => res.data || res),
  })

  // Fetch leads to allow linking / autofill
  const { data: leads = [] } = useQuery<SalesLead[]>({
    queryKey: ['sales-leads'],
    queryFn: () => salesApi.getLeads(),
  })

  // Fetch existing quotation when in edit mode
  const { data: editQuoteData } = useQuery({
    queryKey: ['sales-quote-edit', quoteId],
    queryFn: () => salesApi.getQuoteById(quoteId!),
    enabled: isEditMode && !!quoteId,
  })

  // Create default line item helper
  const createDefaultItem = (
    name = '',
    sku = '',
    price = 0,
    warranty = 'Standard 2-Year OEM Warranty',
    extWarranty = 'None',
    extPrice = 0,
    cost = 0,
    msp = 0,
    pId?: number
  ): QuoteLineItem => ({
    id: `item-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`,
    product_id: pId,
    product_name: name,
    product_sku: sku,
    quantity: 1,
    warranty_terms: warranty,
    quoted_unit_price: price,
    extended_warranty: extWarranty,
    extended_warranty_price: extPrice,
    unit_cost_price: cost,
    unit_msp_floor: msp,
  })

  // Customer & Scope State
  const [customerName, setCustomerName] = useState('')
  const [quoteDate, setQuoteDate] = useState<string>(() => new Date().toISOString().split('T')[0])
  const [saleType, setSaleType] = useState('End_Customer')
  const [selectedLeadId, setSelectedLeadId] = useState<number | undefined>(undefined)
  const [contactPerson, setContactPerson] = useState('')
  const [contactPhone, setContactPhone] = useState('')
  const [contactEmail, setContactEmail] = useState('')
  const [validUntilDays, setValidUntilDays] = useState(30)
  const [paymentTerms, setPaymentTerms] = useState('30% Advance, 70% against Proforma Invoice before dispatch')
  const [deliveryTimeline, setDeliveryTimeline] = useState('2 to 3 Weeks from receipt of Purchase Order')
  const [commercialNotes, setCommercialNotes] = useState('')
  const [approvalRemarks, setApprovalRemarks] = useState('')

  // Separate Services State (Installation & Commissioning, Extended AMC)
  const [includeInstallation, setIncludeInstallation] = useState(false)
  const [installationScope, setInstallationScope] = useState('On-site placement, cabling, battery interconnection & load testing')
  const [installationPrice, setInstallationPrice] = useState(5000)

  const [includeAmc, setIncludeAmc] = useState(false)
  const [amcDuration, setAmcDuration] = useState('1-Year Comprehensive AMC (Post-Warranty)')
  const [amcPrice, setAmcPrice] = useState(12000)

  // Smart Discount Input State: auto-detects flat integer vs percentage (e.g. 5000 vs 10%)
  const [discountInput, setDiscountInput] = useState('')
  const [feedback, setFeedback] = useState<{ type: 'success' | 'error'; message: string } | null>(null)

  // Line items state
  const [items, setItems] = useState<QuoteLineItem[]>([
    createDefaultItem('AVO 60kVA 3-Phase Online UPS', 'PWR-UPS-60KVA', 75000, 'Standard 2-Year OEM Warranty', 'None', 0, 50000, 70000),
  ])

  // Handle pre-fill if lead_id param exists in URL (for new quote)
  useEffect(() => {
    if (!isEditMode && leadIdParam && leads.length > 0) {
      const matchedLead = leads.find((l) => l.id === Number(leadIdParam))
      if (matchedLead) {
        setSelectedLeadId(matchedLead.id)
        setCustomerName(matchedLead.company_name)
        setSaleType(matchedLead.sale_type)
        setContactPerson(matchedLead.contact_person || '')
        setContactPhone(matchedLead.contact_phone || '')
        setContactEmail(matchedLead.contact_email || '')
        if (matchedLead.product_class && items.length === 1 && !items[0].product_name) {
          setItems([createDefaultItem(matchedLead.product_class)])
        }
      }
    }
  }, [isEditMode, leadIdParam, leads])

  // Handle pre-fill when in edit mode
  useEffect(() => {
    if (isEditMode && editQuoteData) {
      setCustomerName(editQuoteData.customer_name || '')
      if (editQuoteData.quote_date) {
        setQuoteDate(editQuoteData.quote_date.split('T')[0])
      } else if (editQuoteData.created_at) {
        setQuoteDate(editQuoteData.created_at.split('T')[0].split(' ')[0])
      }
      setSaleType(editQuoteData.sale_type || 'End_Customer')
      setSelectedLeadId(editQuoteData.lead_id || undefined)
      setContactPerson(editQuoteData.contact_person || '')
      setContactPhone(editQuoteData.contact_phone || '')
      setContactEmail(editQuoteData.contact_email || '')
      setDiscountInput(
        editQuoteData.discount_input ||
          (editQuoteData.discount_amount ? String(editQuoteData.discount_amount) : '')
      )
      setIncludeInstallation(Number(editQuoteData.installation_charges || 0) > 0)
      setInstallationPrice(Number(editQuoteData.installation_charges || 5000))
      setIncludeAmc(Number(editQuoteData.amc_charges || 0) > 0)
      setAmcPrice(Number(editQuoteData.amc_charges || 12000))
      setApprovalRemarks(editQuoteData.approval_remarks || '')
      if (editQuoteData.items && editQuoteData.items.length > 0) {
        setItems(
          editQuoteData.items.map((it: any) =>
            createDefaultItem(
              it.product_name,
              it.product_sku,
              Number(it.quoted_unit_price || 0),
              it.warranty_terms || 'Standard 2-Year OEM Warranty',
              it.extended_warranty || 'None',
              Number(it.extended_warranty_price || 0),
              Number(it.unit_cost_price || 0),
              Number(it.unit_msp_floor || 0),
              it.product_id
            )
          )
        )
      }
    }
  }, [isEditMode, editQuoteData])

  // Lead selection change
  const handleLeadSelect = (leadId: number | '') => {
    if (!leadId) {
      setSelectedLeadId(undefined)
      return
    }
    const l = leads.find((lead) => lead.id === Number(leadId))
    if (l) {
      setSelectedLeadId(l.id)
      setCustomerName(l.company_name)
      setSaleType(l.sale_type)
      setContactPerson(l.contact_person || '')
      setContactPhone(l.contact_phone || '')
      setContactEmail(l.contact_email || '')
      if (l.product_class && items.length > 0 && !items[0].product_name) {
        const nextItems = [...items]
        nextItems[0].product_name = l.product_class
        setItems(nextItems)
      }
    }
  }

  // Line Item Handlers
  const handleAddItem = (preset?: Partial<QuoteLineItem>) => {
    setItems((prev) => [
      ...prev,
      preset
        ? createDefaultItem(
            preset.product_name,
            preset.product_sku,
            preset.quoted_unit_price,
            preset.warranty_terms,
            preset.extended_warranty,
            preset.extended_warranty_price,
            preset.unit_cost_price,
            preset.unit_msp_floor,
            preset.product_id
          )
        : createDefaultItem(),
    ])
  }

  const handleDuplicateItem = (index: number) => {
    const target = items[index]
    const dup = createDefaultItem(
      target.product_name ? `${target.product_name} (Copy)` : '',
      target.product_sku,
      target.quoted_unit_price,
      target.warranty_terms,
      target.extended_warranty,
      target.extended_warranty_price,
      target.unit_cost_price,
      target.unit_msp_floor,
      target.product_id
    )
    const next = [...items]
    next.splice(index + 1, 0, dup)
    setItems(next)
  }

  const handleRemoveItem = (index: number) => {
    if (items.length <= 1) return
    setItems(items.filter((_, i) => i !== index))
  }

  const handleItemChange = (index: number, field: keyof QuoteLineItem, value: any) => {
    setItems((prev) => {
      const next = [...prev]
      next[index] = { ...next[index], [field]: value }
      return next
    })
  }

  const handleCatalogSelect = (index: number, productId: number) => {
    if (!productId) {
      setItems((prev) => {
        const next = [...prev]
        next[index] = {
          ...next[index],
          product_id: undefined,
        }
        return next
      })
      return
    }

    const found = dbProducts.find((p: any) => p.id === productId)
    if (found) {
      setItems((prev) => {
        const next = [...prev]
        const sellingPrice = Number(found.selling_price || found.min_selling_price || 0)
        const costPrice = Number(found.cost_price || 0)
        const mspFloor = Number(found.min_selling_price || found.selling_price || 0)

        next[index] = {
          ...next[index],
          product_id: found.id,
          product_name: found.name,
          product_sku: found.sku || '',
          quoted_unit_price: sellingPrice,
          unit_cost_price: costPrice,
          unit_msp_floor: mspFloor,
        }
        return next
      })
    }
  }

  // Equipment Subtotal
  const equipmentSubtotal = useMemo(() => {
    return items.reduce((sum, item) => {
      const qty = Math.max(1, Number(item.quantity || 1))
      const price = Number(item.quoted_unit_price || 0)
      const extPrice = Number(item.extended_warranty_price || 0)
      return sum + (price + extPrice) * qty
    }, 0)
  }, [items])

  // Services Subtotal (Separate Data)
  const servicesSubtotal = useMemo(() => {
    let total = 0
    if (includeInstallation) total += Math.max(0, Number(installationPrice || 0))
    if (includeAmc) total += Math.max(0, Number(amcPrice || 0))
    return total
  }, [includeInstallation, installationPrice, includeAmc, amcPrice])

  // Gross Commercial Subtotal
  const grossSubtotal = equipmentSubtotal + servicesSubtotal

  // Floor MSP & Cost calculations for internal governance
  const totalMspFloor = useMemo(() => {
    return items.reduce((sum, item) => {
      const qty = Math.max(1, Number(item.quantity || 1))
      return sum + Number(item.unit_msp_floor || 0) * qty
    }, 0)
  }, [items])

  const totalCostPrice = useMemo(() => {
    return items.reduce((sum, item) => {
      const qty = Math.max(1, Number(item.quantity || 1))
      return sum + Number(item.unit_cost_price || 0) * qty
    }, 0)
  }, [items])

  // Smart Discount Parsing (Auto-detect integer flat vs percentage e.g. 5000 vs 10%)
  const resolvedDiscount = useMemo(() => {
    const raw = discountInput.trim()
    if (!raw) {
      return { amount: 0, isPercent: false, percentValue: 0 }
    }

    if (raw.includes('%')) {
      const pct = parseFloat(raw.replace('%', '').trim())
      if (!isNaN(pct) && pct > 0) {
        const calculated = Math.round(grossSubtotal * (pct / 100))
        return {
          amount: Math.min(grossSubtotal, calculated),
          isPercent: true,
          percentValue: pct,
        }
      }
    }

    const flat = parseFloat(raw.replace(/,/g, ''))
    if (!isNaN(flat) && flat > 0) {
      return {
        amount: Math.min(grossSubtotal, flat),
        isPercent: false,
        percentValue: 0,
      }
    }

    return { amount: 0, isPercent: false, percentValue: 0 }
  }, [discountInput, grossSubtotal])

  const finalQuoteAmount = Math.max(0, grossSubtotal - resolvedDiscount.amount)
  const estimatedGst18 = Math.round(finalQuoteAmount * 0.18)
  const grandTotalWithGst = finalQuoteAmount + estimatedGst18

  // CPQ Floor MSP Governance Check
  const isBelowMsp = totalMspFloor > 0 && finalQuoteAmount < totalMspFloor
  const mspShortfall = isBelowMsp ? totalMspFloor - finalQuoteAmount : 0

  const grossMarginPercent =
    finalQuoteAmount > 0
      ? (((finalQuoteAmount - totalCostPrice) / finalQuoteAmount) * 100).toFixed(1)
      : '0.0'

  // Save / Update Mutation
  const saveMutation = useMutation({
    mutationFn: (payload: any) =>
      isEditMode && quoteId
        ? salesApi.updateQuote(quoteId, payload)
        : salesApi.createQuote(payload),
    onSuccess: (data: SalesQuotation) => {
      queryClient.invalidateQueries({ queryKey: ['sales-quotes'] })
      if (quoteId) {
        queryClient.invalidateQueries({ queryKey: ['sales-quote-detail', quoteId] })
      }
      navigate(isEditMode && quoteId ? `/sales/quotes/${quoteId}` : '/sales/quotes')
    },
    onError: (err: any) => {
      setFeedback({
        type: 'error',
        message: err?.response?.data?.message || err.message || 'Failed to save quotation',
      })
    },
  })

  // Submit validation check
  const isSubmitDisabled =
    !customerName.trim() ||
    items.length === 0 ||
    items.some((it) => !it.product_name.trim() || !it.quoted_unit_price) ||
    (contactPhone.trim() && !isValidPhone(contactPhone)) ||
    (contactEmail.trim() && !isValidEmail(contactEmail)) ||
    (isBelowMsp && !approvalRemarks.trim()) ||
    saveMutation.isPending

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()

    if (!customerName.trim()) {
      setFeedback({ type: 'error', message: 'Customer or Organization Name is required.' })
      return
    }

    if (items.some((it) => !it.product_name.trim() || !it.quoted_unit_price)) {
      setFeedback({
        type: 'error',
        message: 'All equipment line items must have a valid description and quoted price.',
      })
      return
    }

    if (contactPhone.trim() && !isValidPhone(contactPhone)) {
      setFeedback({ type: 'error', message: 'Please provide a valid 10-15 digit contact phone number.' })
      return
    }

    if (contactEmail.trim() && !isValidEmail(contactEmail)) {
      setFeedback({ type: 'error', message: 'Please provide a valid email format.' })
      return
    }

    if (isBelowMsp && !approvalRemarks.trim()) {
      setFeedback({
        type: 'error',
        message: 'A strategic justification is mandatory for Below-MSP Floor pricing override.',
      })
      return
    }

    const payload = {
      customer_name: customerName.trim(),
      quote_date: quoteDate || new Date().toISOString().split('T')[0],
      contact_person: contactPerson.trim() || null,
      contact_phone: contactPhone.trim() || null,
      contact_email: contactEmail.trim() || null,
      sale_type: saleType,
      lead_id: selectedLeadId,
      discount_input: discountInput.trim() || '0',
      discount_amount: resolvedDiscount.amount,
      installation_charges: includeInstallation ? Number(installationPrice || 0) : 0,
      amc_charges: includeAmc ? Number(amcPrice || 0) : 0,
      approval_remarks: approvalRemarks.trim() || null,
      valid_until: new Date(Date.now() + validUntilDays * 86400000).toISOString().split('T')[0],
      items: items.map((it) => ({
        product_id: it.product_id,
        product_name: it.product_name.trim(),
        product_sku: it.product_sku.trim() || 'AVO-CUSTOM',
        quantity: Math.max(1, Number(it.quantity || 1)),
        warranty_terms: it.warranty_terms,
        extended_warranty: it.extended_warranty,
        extended_warranty_price: Number(it.extended_warranty_price || 0),
        unit_cost_price: Number(it.unit_cost_price || 0),
        unit_msp_floor: Number(it.unit_msp_floor || 0),
        quoted_unit_price: Number(it.quoted_unit_price || 0),
      })),
    }

    saveMutation.mutate(payload)
  }

  return (
    <div className="space-y-4 max-w-7xl mx-auto pb-16">
      {/* Top Header & Breadcrumb */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-zinc-200 pb-3">
        <div className="space-y-1">
          <div className="flex items-center space-x-2 text-xs text-zinc-500">
            <Link to="/sales/quotes" className="hover:text-zinc-900 transition-colors flex items-center gap-1">
              <ArrowLeft className="w-3.5 h-3.5" />
              <span>Back to Quotation Portfolio</span>
            </Link>
            <span>/</span>
            <span className="text-zinc-800 font-medium">
              {isEditMode ? `Edit ${editQuoteData?.quote_number || 'Quotation'}` : 'New Quotation'}
            </span>
          </div>
          <div className="flex items-center gap-2">
            <h1 className="text-lg font-bold tracking-tight text-zinc-900">
              {isEditMode
                ? `Edit Quotation ${editQuoteData?.quote_number || ''}`
                : 'Build Commercial Quotation & CPQ Engine'}
            </h1>
            <span className="text-[10px] px-2 py-0.5 rounded font-mono font-semibold bg-zinc-900 text-white">
              {isEditMode ? 'Quotation Revision' : 'Dynamic Floor MSP Governance'}
            </span>
          </div>
          <p className="text-xs text-zinc-500">
            {isEditMode
              ? `Re-configure commercial items, adjust discounts, and re-evaluate CPQ Floor MSP governance for ${editQuoteData?.customer_name || 'customer'}.`
              : 'Configure multi-item UPS & power systems, add warranty tiers, configure services, and generate client-ready proposals.'}
          </p>
        </div>

        <div className="flex items-center gap-2">
          <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={() => navigate(isEditMode && quoteId ? `/sales/quotes/${quoteId}` : '/sales/quotes')}
            className="h-8 text-xs border-zinc-200"
          >
            Cancel
          </Button>
          <Button
            onClick={handleSubmit}
            disabled={isSubmitDisabled}
            size="sm"
            className="h-8 text-xs bg-zinc-900 text-white hover:bg-zinc-800 font-medium shadow-2xs"
          >
            {saveMutation.isPending
              ? 'Saving...'
              : isEditMode
              ? `Update Quotation (${items.length} items)`
              : `Save Quotation (${items.length} items)`}
          </Button>
        </div>
      </div>

      {feedback && (
        <div
          className={`p-3 rounded-md text-xs font-medium border flex items-center justify-between ${
            feedback.type === 'success'
              ? 'bg-emerald-50 text-emerald-800 border-emerald-200'
              : 'bg-red-50 text-red-800 border-red-200'
          }`}
        >
          <span>{feedback.message}</span>
          <button
            onClick={() => setFeedback(null)}
            className="text-xs opacity-70 hover:opacity-100 ml-4 cursor-pointer"
          >
            &times;
          </button>
        </div>
      )}

      {/* Section 1: Customer Profile & Commercial Scope */}
      <Card className="border-zinc-200 shadow-2xs">
        <div className="px-4 py-2.5 bg-zinc-50/80 border-b border-zinc-200 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Building2 className="w-4 h-4 text-zinc-600" />
            <h2 className="text-xs font-bold text-zinc-900 uppercase tracking-wide">
              1. Customer Profile & Commercial Channel
            </h2>
          </div>
          {selectedLeadId && (
            <span className="text-[10px] font-mono font-medium px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
              Linked to Active Lead #{selectedLeadId}
            </span>
          )}
        </div>

        <CardContent className="p-4 space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {/* Customer / Company */}
            <div>
              <Label className="text-xs font-semibold text-zinc-700 block mb-1">
                Customer / Organization Name *
              </Label>
              <Input
                value={customerName}
                onChange={(e) => setCustomerName(e.target.value)}
                placeholder="e.g. State Bank of India or Larsen & Toubro"
                className="h-8 text-xs bg-white"
              />
            </div>

            {/* Link to Lead */}
            <div>
              <Label className="text-xs font-semibold text-zinc-700 block mb-1">
                Link to Active Commercial Lead (Optional)
              </Label>
              <select
                value={selectedLeadId || ''}
                onChange={(e) => handleLeadSelect(e.target.value ? Number(e.target.value) : '')}
                className="h-8 w-full px-2 text-xs border border-zinc-200 rounded-md bg-white text-zinc-800"
              >
                <option value="">-- Manual Entry (No Lead Linked) --</option>
                {leads.map((l) => (
                  <option key={l.id} value={l.id}>
                    {l.lead_number} &bull; {l.company_name} ({l.sale_type.replace('_', ' ')})
                  </option>
                ))}
              </select>
            </div>

            {/* Commercial Sale Type */}
            <div>
              <Label className="text-xs font-semibold text-zinc-700 block mb-1">
                Commercial Sale Type *
              </Label>
              <select
                value={saleType}
                onChange={(e) => setSaleType(e.target.value)}
                className="h-8 w-full px-2 text-xs border border-zinc-200 rounded-md bg-white text-zinc-800 font-medium"
              >
                <option value="End_Customer">End Customer (Direct Commercial)</option>
                <option value="SI_Bid">System Integrator (SI) Multi-Bid</option>
                <option value="Distributor_Dealer">Distributor / Dealer Partner</option>
                <option value="Direct_Bid">Government / PSU Direct Bid</option>
              </select>
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 pt-2 border-t border-zinc-100">
            <div>
              <Label className="text-xs font-semibold text-zinc-700 block mb-1">
                Quotation Date *
              </Label>
              <Input
                type="date"
                value={quoteDate}
                onChange={(e) => setQuoteDate(e.target.value)}
                className="h-8 text-xs font-mono bg-white"
                required
              />
            </div>

            <div>
              <Label className="text-xs font-semibold text-zinc-700 block mb-1">Contact Person</Label>
              <Input
                value={contactPerson}
                onChange={(e) => setContactPerson(e.target.value)}
                placeholder="e.g. Rajesh Kumar (GM Procurement)"
                className="h-8 text-xs bg-white"
              />
            </div>

            <div>
              <Label className="text-xs font-semibold text-zinc-700 block mb-1">Phone Number</Label>
              <Input
                value={contactPhone}
                maxLength={16}
                onChange={(e) => setContactPhone(sanitizePhoneInput(e.target.value))}
                placeholder="e.g. +91 98200 12345"
                className={`h-8 text-xs font-mono bg-white ${
                  contactPhone && getPhoneError(contactPhone, false) ? 'border-red-500 focus:ring-red-400' : ''
                }`}
              />
              {contactPhone && getPhoneError(contactPhone, false) && (
                <p className="text-[10px] text-red-600 mt-1 font-medium">{getPhoneError(contactPhone, false)}</p>
              )}
            </div>

            <div>
              <Label className="text-xs font-semibold text-zinc-700 block mb-1">Email Address</Label>
              <Input
                type="email"
                value={contactEmail}
                onChange={(e) => setContactEmail(e.target.value)}
                placeholder="e.g. procurement@client.com"
                className={`h-8 text-xs font-mono bg-white ${
                  contactEmail && getEmailError(contactEmail, false) ? 'border-red-500 focus:ring-red-400' : ''
                }`}
              />
              {contactEmail && getEmailError(contactEmail, false) && (
                <p className="text-[10px] text-red-600 mt-1 font-medium">{getEmailError(contactEmail, false)}</p>
              )}
            </div>

            <div>
              <Label className="text-xs font-semibold text-zinc-700 block mb-1">Quotation Validity</Label>
              <select
                value={validUntilDays}
                onChange={(e) => setValidUntilDays(Number(e.target.value))}
                className="h-8 w-full px-2 text-xs border border-zinc-200 rounded-md bg-white text-zinc-800"
              >
                <option value={15}>15 Days Validity</option>
                <option value={30}>30 Days Validity (Standard)</option>
                <option value={45}>45 Days Validity</option>
                <option value={60}>60 Days Validity</option>
                <option value={90}>90 Days (Government / RFP)</option>
              </select>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Section 2: Equipment Specification & Multi-Item Configuration */}
      <Card className="border-zinc-200 shadow-2xs overflow-hidden">
        <div className="px-4 py-2.5 bg-zinc-50/80 border-b border-zinc-200 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Layers className="w-4 h-4 text-zinc-600" />
            <h2 className="text-xs font-bold text-zinc-900 uppercase tracking-wide">
              2. Equipment Specification ({items.length} {items.length === 1 ? 'Item' : 'Items'})
            </h2>
          </div>
          <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={() => handleAddItem()}
            className="h-7 text-xs bg-white border-zinc-300 text-zinc-800 hover:bg-zinc-100 font-medium"
          >
            <Plus className="w-3.5 h-3.5 mr-1" />
            Add Equipment Row
          </Button>
        </div>

        <div className="overflow-x-auto">
          <Table>
            <TableHeader className="bg-zinc-100/70 border-b border-zinc-200">
              <TableRow>
                <TableHead className="w-8 text-center font-semibold text-zinc-600 text-[11px]">#</TableHead>
                <TableHead className="w-72 font-semibold text-zinc-600 text-[11px]">
                  Item Name / Master Catalog *
                </TableHead>
                <TableHead className="w-36 font-semibold text-zinc-600 text-[11px]">Item Model (SKU)</TableHead>
                <TableHead className="w-20 text-center font-semibold text-zinc-600 text-[11px]">Qty *</TableHead>
                <TableHead className="w-44 font-semibold text-zinc-600 text-[11px]">Base Warranty</TableHead>
                <TableHead className="w-32 text-right font-semibold text-zinc-900 text-[11px]">Price (₹) *</TableHead>
                <TableHead className="w-44 font-semibold text-zinc-600 text-[11px]">Extended Warranty</TableHead>
                <TableHead className="w-32 text-right font-semibold text-zinc-900 text-[11px]">
                  Ext. Warranty Price (₹)
                </TableHead>
                <TableHead className="w-36 text-right font-semibold text-zinc-900 text-[11px]">Line Total (₹)</TableHead>
                <TableHead className="w-16 text-center font-semibold text-zinc-600 text-[11px]">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {items.map((item, idx) => {
                const qty = Math.max(1, Number(item.quantity || 1))
                const unitPrice = Number(item.quoted_unit_price || 0)
                const extPrice = Number(item.extended_warranty_price || 0)
                const lineTotal = (unitPrice + extPrice) * qty

                return (
                  <TableRow key={item.id} className="hover:bg-zinc-50/50 transition-colors">
                    <TableCell className="text-center font-mono text-xs font-semibold text-zinc-500">
                      #{idx + 1}
                    </TableCell>

                    {/* Item Name / Catalog Select */}
                    <TableCell className="space-y-1 py-2">
                      <select
                        value={item.product_id || ''}
                        onChange={(e) => handleCatalogSelect(idx, Number(e.target.value))}
                        className="h-7 w-full px-2 text-[11px] border border-zinc-200 rounded bg-white text-zinc-700"
                      >
                        <option value="">-- Choose from Master Catalog (or enter manually) --</option>
                        {dbProducts.map((p: any) => (
                          <option key={p.id} value={p.id}>
                            {p.sku} &bull; {p.name}
                          </option>
                        ))}
                      </select>
                      <Input
                        value={item.product_name}
                        placeholder="e.g. AVO 60kVA 3-Phase Online UPS with 120V Lithium Rack"
                        onChange={(e) => handleItemChange(idx, 'product_name', e.target.value)}
                        className="h-7 text-xs bg-white font-medium"
                      />
                    </TableCell>

                    {/* Item Model / SKU */}
                    <TableCell className="py-2">
                      <Input
                        value={item.product_sku}
                        placeholder="e.g. PWR-UPS-60KVA"
                        onChange={(e) => handleItemChange(idx, 'product_sku', e.target.value)}
                        className="h-7 text-xs bg-white font-mono"
                      />
                    </TableCell>

                    {/* Quantity */}
                    <TableCell className="text-center py-2">
                      <Input
                        type="number"
                        min={1}
                        value={item.quantity || ''}
                        onChange={(e) => handleItemChange(idx, 'quantity', Math.max(1, Number(e.target.value)))}
                        className="h-7 text-xs bg-white font-mono text-center w-16 mx-auto"
                      />
                    </TableCell>

                    {/* Base Warranty */}
                    <TableCell className="py-2">
                      <select
                        value={item.warranty_terms}
                        onChange={(e) => handleItemChange(idx, 'warranty_terms', e.target.value)}
                        className="h-7 w-full px-2 text-[11px] border border-zinc-200 rounded bg-white text-zinc-700"
                      >
                        {WARRANTY_OPTIONS.map((w) => (
                          <option key={w} value={w}>
                            {w}
                          </option>
                        ))}
                      </select>
                    </TableCell>

                    {/* Price (Quoted Unit Price) */}
                    <TableCell className="text-right py-2">
                      <Input
                        type="number"
                        value={item.quoted_unit_price || ''}
                        placeholder="0"
                        onChange={(e) => handleItemChange(idx, 'quoted_unit_price', Number(e.target.value))}
                        className="h-7 text-xs bg-white font-mono font-bold text-right w-28 ml-auto"
                      />
                    </TableCell>

                    {/* Extended Warranty */}
                    <TableCell className="py-2">
                      <select
                        value={item.extended_warranty}
                        onChange={(e) => handleItemChange(idx, 'extended_warranty', e.target.value)}
                        className="h-7 w-full px-2 text-[11px] border border-zinc-200 rounded bg-white text-zinc-700"
                      >
                        {EXTENDED_WARRANTY_OPTIONS.map((ew) => (
                          <option key={ew} value={ew}>
                            {ew}
                          </option>
                        ))}
                      </select>
                    </TableCell>

                    {/* Extended Warranty Price */}
                    <TableCell className="text-right py-2">
                      <Input
                        type="number"
                        value={item.extended_warranty_price || ''}
                        placeholder="0"
                        disabled={item.extended_warranty === 'None'}
                        onChange={(e) => handleItemChange(idx, 'extended_warranty_price', Number(e.target.value))}
                        className={`h-7 text-xs bg-white font-mono text-right w-28 ml-auto ${
                          item.extended_warranty === 'None' ? 'opacity-40 bg-zinc-50' : ''
                        }`}
                      />
                    </TableCell>

                    {/* Line Total */}
                    <TableCell className="text-right font-mono font-bold text-xs text-zinc-900 py-2">
                      ₹{lineTotal.toLocaleString('en-IN')}
                    </TableCell>

                    {/* Actions */}
                    <TableCell className="text-center py-2">
                      <div className="flex items-center justify-center gap-1">
                        <button
                          type="button"
                          onClick={() => handleDuplicateItem(idx)}
                          title="Duplicate line"
                          className="p-1 rounded text-zinc-400 hover:text-zinc-800 hover:bg-zinc-100 cursor-pointer"
                        >
                          <Copy className="w-3.5 h-3.5" />
                        </button>
                        <button
                          type="button"
                          onClick={() => handleRemoveItem(idx)}
                          disabled={items.length <= 1}
                          title={items.length <= 1 ? 'At least one line is required' : 'Remove item'}
                          className="p-1 rounded text-zinc-400 hover:text-red-600 hover:bg-red-50 disabled:opacity-30 cursor-pointer"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </TableCell>
                  </TableRow>
                )
              })}
            </TableBody>
          </Table>
        </div>

        {/* Footer Summary */}
        <div className="px-4 py-2 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between text-xs text-zinc-500">
          <div className="font-mono">
            {items.length} {items.length === 1 ? 'item' : 'items'} configured
          </div>
          <div className="font-semibold text-zinc-900">
            Equipment Total: <span className="font-mono text-sm ml-1">₹{equipmentSubtotal.toLocaleString('en-IN')}</span>
          </div>
        </div>
      </Card>

      {/* Section 3: Installation, Commissioning & Extended AMC (Separate Data) */}
      <Card className="border-zinc-200 shadow-2xs">
        <div className="px-4 py-2.5 bg-zinc-50/80 border-b border-zinc-200 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Wrench className="w-4 h-4 text-zinc-600" />
            <h2 className="text-xs font-bold text-zinc-900 uppercase tracking-wide">
              3. Installation, Commissioning & Extended AMC Services (Separate Data)
            </h2>
          </div>
          <span className="text-[11px] font-mono font-medium text-zinc-600">
            Services Total: <strong className="text-zinc-900">₹{servicesSubtotal.toLocaleString('en-IN')}</strong>
          </span>
        </div>

        <CardContent className="p-4 space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {/* Service 1: Installation & Testing */}
            <div
              className={`p-3.5 rounded-lg border transition-all ${
                includeInstallation ? 'border-zinc-900 bg-zinc-50/50' : 'border-zinc-200 bg-white'
              }`}
            >
              <div className="flex items-center justify-between mb-2">
                <label className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={includeInstallation}
                    onChange={(e) => setIncludeInstallation(e.target.checked)}
                    className="w-4 h-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900 cursor-pointer"
                  />
                  <span className="text-xs font-bold text-zinc-900">
                    Add Installation & Commissioning
                  </span>
                </label>
                <span className="text-[10px] px-1.5 py-0.5 rounded font-mono font-semibold bg-zinc-100 text-zinc-700">
                  On-Site Engineering
                </span>
              </div>

              {includeInstallation ? (
                <div className="space-y-2.5 pt-2 border-t border-zinc-200/80 text-xs">
                  <div>
                    <Label className="text-[11px] text-zinc-600 block mb-1">Scope of Work</Label>
                    <Input
                      value={installationScope}
                      onChange={(e) => setInstallationScope(e.target.value)}
                      placeholder="e.g. Mechanical placement, cabling, DB termination & commissioning"
                      className="h-7 text-xs bg-white"
                    />
                  </div>
                  <div>
                    <Label className="text-[11px] text-zinc-600 block mb-1">Service Charges (₹)</Label>
                    <Input
                      type="number"
                      value={installationPrice || ''}
                      onChange={(e) => setInstallationPrice(Number(e.target.value))}
                      placeholder="5000"
                      className="h-7 text-xs bg-white font-mono font-bold text-right w-36"
                    />
                  </div>
                </div>
              ) : (
                <p className="text-[11px] text-zinc-400 mt-1">
                  Optional: Customer arranges local installation or standard supply-only terms.
                </p>
              )}
            </div>

            {/* Service 2: Extended AMC Service */}
            <div
              className={`p-3.5 rounded-lg border transition-all ${
                includeAmc ? 'border-zinc-900 bg-zinc-50/50' : 'border-zinc-200 bg-white'
              }`}
            >
              <div className="flex items-center justify-between mb-2">
                <label className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={includeAmc}
                    onChange={(e) => setIncludeAmc(e.target.checked)}
                    className="w-4 h-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900 cursor-pointer"
                  />
                  <span className="text-xs font-bold text-zinc-900">
                    Add Extended AMC Service
                  </span>
                </label>
                <span className="text-[10px] px-1.5 py-0.5 rounded font-mono font-semibold bg-zinc-100 text-zinc-700">
                  Post-Warranty SLA
                </span>
              </div>

              {includeAmc ? (
                <div className="space-y-2.5 pt-2 border-t border-zinc-200/80 text-xs">
                  <div>
                    <Label className="text-[11px] text-zinc-600 block mb-1">AMC Duration & SLA Level</Label>
                    <select
                      value={amcDuration}
                      onChange={(e) => setAmcDuration(e.target.value)}
                      className="h-7 w-full px-2 text-[11px] border border-zinc-200 rounded bg-white text-zinc-800"
                    >
                      <option value="1-Year Comprehensive AMC (Post-Warranty)">1-Year Comprehensive AMC (Post-Warranty)</option>
                      <option value="2-Year Comprehensive AMC (Post-Warranty)">2-Year Comprehensive AMC (Post-Warranty)</option>
                      <option value="3-Year Comprehensive AMC (Post-Warranty)">3-Year Comprehensive AMC (Post-Warranty)</option>
                      <option value="Non-Comprehensive Labor-Only AMC">Non-Comprehensive Labor-Only AMC</option>
                    </select>
                  </div>
                  <div>
                    <Label className="text-[11px] text-zinc-600 block mb-1">AMC Contract Value (₹)</Label>
                    <Input
                      type="number"
                      value={amcPrice || ''}
                      onChange={(e) => setAmcPrice(Number(e.target.value))}
                      placeholder="12000"
                      className="h-7 text-xs bg-white font-mono font-bold text-right w-36"
                    />
                  </div>
                </div>
              ) : (
                <p className="text-[11px] text-zinc-400 mt-1">
                  Optional: Customer will operate under standard warranty without dedicated post-warranty AMC.
                </p>
              )}
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Section 4: Commercial Terms & Pricing Breakdown */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {/* Left: Notes & Conditions */}
        <Card className="border-zinc-200 shadow-2xs">
          <div className="px-4 py-2.5 bg-zinc-50/80 border-b border-zinc-200">
            <h2 className="text-xs font-bold text-zinc-900 uppercase tracking-wide">
              4. Commercial Terms & Special Conditions
            </h2>
          </div>
          <CardContent className="p-4 space-y-3 text-xs">
            <div>
              <Label className="text-xs font-semibold text-zinc-700 block mb-1">Payment Terms</Label>
              <Input
                value={paymentTerms}
                onChange={(e) => setPaymentTerms(e.target.value)}
                placeholder="e.g. 30% Advance, 70% against PI before dispatch"
                className="h-8 text-xs bg-white"
              />
            </div>

            <div>
              <Label className="text-xs font-semibold text-zinc-700 block mb-1">Delivery Timeline</Label>
              <Input
                value={deliveryTimeline}
                onChange={(e) => setDeliveryTimeline(e.target.value)}
                placeholder="e.g. 2 to 3 Weeks from PO receipt"
                className="h-8 text-xs bg-white"
              />
            </div>

            <div>
              <Label className="text-xs font-semibold text-zinc-700 block mb-1">Special Commercial Notes</Label>
              <textarea
                rows={2}
                value={commercialNotes}
                onChange={(e) => setCommercialNotes(e.target.value)}
                placeholder="Add customer-specific notes, freight terms, or technical considerations..."
                className="w-full p-2 text-xs border border-zinc-200 rounded-md bg-white"
              />
            </div>
          </CardContent>
        </Card>

        {/* Right: Financial Breakdown Card with Smart Discount Input */}
        <Card className="border-zinc-200 shadow-2xs">
          <div className="px-4 py-2.5 bg-zinc-50/80 border-b border-zinc-200 flex items-center justify-between">
            <h2 className="text-xs font-bold text-zinc-900 uppercase tracking-wide">
              5. Commercial Economics & Grand Total
            </h2>
            <span className="text-[10px] font-mono text-zinc-500">Amounts in INR (₹)</span>
          </div>

          <CardContent className="p-4 space-y-3 text-xs">
            <div className="flex justify-between items-center py-1">
              <span className="text-zinc-600 font-medium">Equipment Items Subtotal:</span>
              <span className="font-mono font-bold text-zinc-900 text-sm">
                ₹{equipmentSubtotal.toLocaleString('en-IN')}
              </span>
            </div>

            <div className="flex justify-between items-center py-1 border-t border-zinc-100">
              <span className="text-zinc-600 font-medium">Installation & Services Subtotal:</span>
              <span className="font-mono font-semibold text-zinc-700 text-sm">
                ₹{servicesSubtotal.toLocaleString('en-IN')}
              </span>
            </div>

            <div className="flex justify-between items-center py-1 border-t border-zinc-100 bg-zinc-50 px-2 rounded">
              <span className="text-zinc-800 font-semibold">Gross Commercial Subtotal:</span>
              <span className="font-mono font-bold text-zinc-900 text-sm">
                ₹{grossSubtotal.toLocaleString('en-IN')}
              </span>
            </div>

            {/* Smart Discount Input: Type 5000 for flat, or 10% for percentage */}
            <div className="py-2 border-t border-zinc-100 space-y-1.5">
              <div className="flex justify-between items-center">
                <div>
                  <span className="text-zinc-800 font-semibold block">Commercial Discount / Concession:</span>
                  <span className="text-[10px] text-zinc-400 block">
                    Type flat number (e.g. <strong>5000</strong>) or percentage (e.g. <strong>10%</strong>)
                  </span>
                </div>
                <div className="flex items-center gap-1.5">
                  <Input
                    type="text"
                    value={discountInput}
                    placeholder="e.g. 5000 or 10%"
                    onChange={(e) => setDiscountInput(e.target.value)}
                    className="h-8 w-36 text-xs bg-white font-mono text-right font-medium"
                  />
                </div>
              </div>

              {/* Live Discount Detection Badge */}
              <div className="flex justify-end">
                {resolvedDiscount.amount > 0 ? (
                  <div className="inline-flex items-center gap-1.5 text-[11px] font-mono font-semibold px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <Sparkles className="w-3 h-3 text-emerald-600" />
                    <span>
                      {resolvedDiscount.isPercent
                        ? `${resolvedDiscount.percentValue}% of ₹${grossSubtotal.toLocaleString('en-IN')} = -₹${resolvedDiscount.amount.toLocaleString('en-IN')}`
                        : `Flat deduction: -₹${resolvedDiscount.amount.toLocaleString('en-IN')}`}
                    </span>
                  </div>
                ) : (
                  <span className="text-[10px] text-zinc-400 font-mono">No discount applied</span>
                )}
              </div>
            </div>

            <div className="flex justify-between items-center py-1 border-t border-zinc-100">
              <span className="text-zinc-800 font-semibold">Net Quoted Value (Excl. Taxes):</span>
              <span className="font-mono font-bold text-zinc-900 text-sm">
                ₹{finalQuoteAmount.toLocaleString('en-IN')}
              </span>
            </div>

            <div className="flex justify-between items-center py-1 border-t border-zinc-100 text-zinc-500 text-[11px]">
              <span>Estimated GST (18% Standard):</span>
              <span className="font-mono font-medium">₹{estimatedGst18.toLocaleString('en-IN')}</span>
            </div>

            <div className="flex justify-between items-center p-3 rounded-md bg-zinc-900 text-white font-bold mt-2">
              <span className="text-xs uppercase tracking-wider">Grand Total (Incl. GST):</span>
              <span className="font-mono text-base">
                ₹{grandTotalWithGst.toLocaleString('en-IN')}
              </span>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Section 5: Dynamic CPQ Floor MSP Governance Engine */}
      <Card
        className={`border shadow-2xs overflow-hidden ${
          isBelowMsp ? 'border-amber-300 bg-amber-50/20' : 'border-zinc-200 bg-white'
        }`}
      >
        <div className="px-4 py-2.5 bg-zinc-50/80 border-b border-zinc-200 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Calculator className="w-4 h-4 text-zinc-800" />
            <h2 className="text-xs font-bold text-zinc-900 uppercase tracking-wide">
              6. CPQ Floor MSP Governance & Pricing Policy
            </h2>
          </div>
          {isBelowMsp ? (
            <span className="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-300">
              &lt; FLOOR MSP: REQUIRES L3 VP APPROVAL
            </span>
          ) : (
            <span className="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-zinc-900 text-white">
              &ge; FLOOR MSP: AUTO-AUTHORIZED
            </span>
          )}
        </div>

        <CardContent className="p-4 space-y-3">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs font-mono">
            <div className="p-2.5 rounded bg-zinc-50 border border-zinc-200">
              <span className="text-zinc-500 text-[10px] block uppercase">Gross Subtotal</span>
              <span className="font-bold text-zinc-900 text-sm">₹{grossSubtotal.toLocaleString('en-IN')}</span>
            </div>

            <div className="p-2.5 rounded bg-zinc-50 border border-zinc-200">
              <span className="text-zinc-500 text-[10px] block uppercase">Final Net Quote</span>
              <span className={`font-bold text-sm ${isBelowMsp ? 'text-amber-800' : 'text-zinc-900'}`}>
                ₹{finalQuoteAmount.toLocaleString('en-IN')}
              </span>
            </div>

            <div className="p-2.5 rounded bg-zinc-50 border border-zinc-200">
              <span className="text-zinc-500 text-[10px] block uppercase">Total Floor MSP</span>
              <span className="font-bold text-zinc-700 text-sm">₹{totalMspFloor.toLocaleString('en-IN')}</span>
            </div>

            <div className="p-2.5 rounded bg-zinc-50 border border-zinc-200">
              <span className="text-zinc-500 text-[10px] block uppercase">Gross Margin</span>
              <span className="font-bold text-zinc-900 text-sm">{grossMarginPercent}%</span>
            </div>
          </div>

          {isBelowMsp ? (
            <div className="p-3 rounded-lg border border-amber-300 bg-amber-50 space-y-2">
              <div className="flex items-start gap-2 text-xs text-amber-900">
                <AlertTriangle className="w-4 h-4 text-amber-600 shrink-0 mt-0.5" />
                <div>
                  <span className="font-bold">Quotation Below Floor MSP: </span>
                  Net quote of ₹{finalQuoteAmount.toLocaleString('en-IN')} is ₹
                  {mspShortfall.toLocaleString('en-IN')} below the mandatory Minimum Selling Price of ₹
                  {totalMspFloor.toLocaleString('en-IN')}.
                  <span className="block text-[11px] text-amber-800 mt-0.5">
                    This quotation will be routed to the VP / Commercial Director (Level 3) for formal authorization before dispatch.
                  </span>
                </div>
              </div>

              <div>
                <Label className="text-xs font-bold text-amber-950 block mb-1">
                  Strategic Justification for L3 VP Approval *
                </Label>
                <textarea
                  rows={2}
                  value={approvalRemarks}
                  onChange={(e) => setApprovalRemarks(e.target.value)}
                  placeholder="Provide commercial rationale (e.g. Strategic account entry against competitor, multi-year recurring AMC annuity attached)..."
                  className="w-full p-2 text-xs border border-amber-300 rounded-md bg-white text-zinc-900"
                />
              </div>
            </div>
          ) : (
            <div className="flex items-center gap-2 p-2.5 rounded bg-emerald-50/70 border border-emerald-200 text-xs text-emerald-800">
              <ShieldCheck className="w-4 h-4 text-emerald-600 shrink-0" />
              <span>
                Pricing complies with CPQ Floor MSP policy. This quotation will be <strong>Auto-Approved</strong> immediately upon creation.
              </span>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Sticky Bottom Summary & Action Bar */}
      <div className="sticky bottom-0 z-20 bg-white/95 backdrop-blur border-t border-zinc-200 p-3 shadow-lg flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div className="flex items-center gap-4 text-xs font-mono">
          <div>
            <span className="text-zinc-500 block text-[10px]">ITEMS</span>
            <span className="font-bold text-zinc-900">{items.length} Lines</span>
          </div>
          <div>
            <span className="text-zinc-500 block text-[10px]">SUBTOTAL</span>
            <span className="font-bold text-zinc-900">₹{grossSubtotal.toLocaleString('en-IN')}</span>
          </div>
          <div>
            <span className="text-zinc-500 block text-[10px]">DISCOUNT</span>
            <span className="font-bold text-emerald-700">
              {resolvedDiscount.amount > 0 ? `-₹${resolvedDiscount.amount.toLocaleString('en-IN')}` : '₹0'}
            </span>
          </div>
          <div>
            <span className="text-zinc-500 block text-[10px]">FINAL NET QUOTE</span>
            <span className={`font-bold ${isBelowMsp ? 'text-amber-700' : 'text-zinc-900'}`}>
              ₹{finalQuoteAmount.toLocaleString('en-IN')}
            </span>
          </div>
          <div>
            <span className="text-zinc-500 block text-[10px]">INCL. 18% GST</span>
            <span className="font-bold text-zinc-900 text-sm">
              ₹{grandTotalWithGst.toLocaleString('en-IN')}
            </span>
          </div>
        </div>

        <div className="flex items-center gap-2">
          <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={() => navigate(isEditMode && quoteId ? `/sales/quotes/${quoteId}` : '/sales/quotes')}
            className="h-8 text-xs border-zinc-200"
          >
            Cancel
          </Button>
          <Button
            onClick={handleSubmit}
            disabled={isSubmitDisabled}
            size="sm"
            className="h-8 text-xs bg-zinc-900 text-white hover:bg-zinc-800 font-semibold shadow-2xs"
          >
            {saveMutation.isPending
              ? 'Saving Quotation...'
              : isEditMode
              ? `Update Quotation (₹${finalQuoteAmount.toLocaleString('en-IN')})`
              : `Generate Quotation (₹${finalQuoteAmount.toLocaleString('en-IN')})`}
          </Button>
        </div>
      </div>
    </div>
  )
}
