import { useState, useMemo, useEffect } from 'react'
import { useParams, useNavigate, Link, useSearchParams } from 'react-router-dom'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import {
  Briefcase,
  TrendingUp,
  Search,
  Plus,
  CheckCircle2,
  AlertTriangle,
  XCircle,
  Clock,
  Layers,
  MapPin,
  Building2,
  ShieldCheck,
  FileText,
  FileCheck2,
  Download,
  Send,
  Users,
  ChevronRight,
  ExternalLink,
  ShieldAlert,
  ArrowRight,
  Sparkles,
  Award,
  Edit2,
  Trash2,
  Eye,
  X,
} from 'lucide-react'
import { salesApi, identityApi, productApi, customerApi } from '@/api/services'
import { useAuthStore } from '@/store/authStore'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Dialog, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { AccessDeniedPage } from './AccessDeniedPage'
import { isValidPhone, isValidEmail, sanitizePhoneInput, getPhoneError, getEmailError } from '@/lib/validation'
import type {
  SalesLead,
  SalesVisit,
  SalesQuotation,
  TenderRecord,
  TenderGuarantee,
  SiRfpCluster,
  OrderWithMilestones,
  Customer,
} from '@/types'

export function SalesPage() {
  const queryClient = useQueryClient()
  const { user: currentUser, canAccessSales } = useAuthStore()
  const { tab } = useParams<{ tab?: string }>()
  const [searchParams, setSearchParams] = useSearchParams()
  const leadIdQuery = searchParams.get('lead_id') || ''
  const navigate = useNavigate()

  if (!canAccessSales()) {
    return (
      <AccessDeniedPage
        moduleName="Commercial, Quotations & Tender Platform"
        requiredRole="Sales Representative / Executive / Commercial Team"
      />
    )
  }

  // Active Tab from route parameter: leads | quotes | clustering | tenders | milestones
  const activeTab: 'leads' | 'quotes' | 'clustering' | 'tenders' | 'milestones' = useMemo(() => {
    if (tab === 'quotes' || tab === 'cpq') return 'quotes'
    if (tab === 'clustering' || tab === 'si-bids' || tab === 'si-clusters') return 'clustering'
    if (tab === 'tenders') return 'tenders'
    if (tab === 'milestones') return 'milestones'
    return 'leads'
  }, [tab])

  const setActiveTab = (newTab: 'leads' | 'quotes' | 'clustering' | 'tenders' | 'milestones') => {
    navigate(`/sales/${newTab}`)
  }

  const tabTitles = {
    leads: {
      title: 'Leads & Field Visits',
      desc: 'Commercial prospect directory and geotagged field sales check-in ledger',
    },
    quotes: {
      title: 'Quotation Builder & Below-MSP Gate',
      desc: 'Interactive CPQ simulator, cost-breakdown and L3 VP margin exception clearance',
    },
    clustering: {
      title: 'SI Multi-Bid RFP Clustering (MK-05)',
      desc: 'Hit-rate deduplication across competing System Integrators for shared PSU tenders',
    },
    tenders: {
      title: 'Tender Register & Bank Guarantee Ledger',
      desc: 'Government & enterprise procurement tracking, direct vs dealer bids, and active EMD/PBGs',
    },
    milestones: {
      title: 'Order Milestones & Audit Package',
      desc: '6-stage physical order milestone verification and 1-click customer audit ZIP generator',
    },
  }

  // Feedback toast
  const [feedback, setFeedback] = useState<{ type: 'success' | 'error'; message: string } | null>(null)
  const showFeedback = (type: 'success' | 'error', message: string) => {
    setFeedback({ type, message })
    setTimeout(() => setFeedback(null), 4000)
  }

  // L3 Authorization check
  const isL3VP =
    currentUser?.level === 'L3' ||
    currentUser?.role_type === 'Executive' ||
    currentUser?.role_type === 'Admin'

    // =========================================================================
    // DATA QUERIES
    // =========================================================================
    const { data: leads = [], isLoading: leadsLoading } = useQuery({
      queryKey: ['sales-leads'],
      queryFn: () => salesApi.getLeads(),
    })

    const { data: visits = [], isLoading: visitsLoading } = useQuery({
      queryKey: ['sales-visits'],
      queryFn: salesApi.getVisits,
    })

    const { data: quotes = [], isLoading: quotesLoading } = useQuery({
      queryKey: ['sales-quotes'],
      queryFn: () => salesApi.getQuotes(),
    })

    const { data: tenders = [], isLoading: tendersLoading } = useQuery({
      queryKey: ['sales-tenders'],
      queryFn: () => salesApi.getTenders(),
    })

    const { data: guarantees = [] } = useQuery({
      queryKey: ['sales-guarantees'],
      queryFn: salesApi.getGuarantees,
    })

    const { data: clusterData, isLoading: clustersLoading } = useQuery({
      queryKey: ['sales-clusters'],
      queryFn: salesApi.getClusters,
    })

    const { data: milestones = [], isLoading: milestonesLoading } = useQuery({
      queryKey: ['sales-milestones'],
      queryFn: salesApi.getOrderMilestones,
    })

    const { data: systemUsers = [] } = useQuery({
      queryKey: ['identity-users'],
      queryFn: identityApi.getUsers,
    })

    const { data: dbProducts = [] } = useQuery({
      queryKey: ['catalog-products'],
      queryFn: async () => {
        const res = await productApi.getAll({ all: true })
        return Array.isArray(res) ? res : (res as any)?.data || []
      },
    })

    const { data: rawCustomers = [] } = useQuery({
      queryKey: ['masters-customers'],
      queryFn: async () => {
        const res = await customerApi.getAll({ all: true })
        return Array.isArray(res) ? res : (res as any)?.data || []
      },
    })
    const customers: Customer[] = useMemo(() => {
      return Array.isArray(rawCustomers) ? rawCustomers : []
    }, [rawCustomers])

    // =========================================================================
    // MODAL STATES
    // =========================================================================
    const [isLeadModalOpen, setIsLeadModalOpen] = useState(false)
    const [isQuickAddCustomerOpen, setIsQuickAddCustomerOpen] = useState(false)
    const [selectedCustomerId, setSelectedCustomerId] = useState<string>('')
    const [quickCustomer, setQuickCustomer] = useState({
      name: '',
      company: '',
      email: '',
      phone: '',
      tax_number: '',
      credit_limit: 500000,
      address: '',
    })
    const [quickCustomerError, setQuickCustomerError] = useState<string | null>(null)
    const [isVisitModalOpen, setIsVisitModalOpen] = useState(false)
    const [isQuoteModalOpen, setIsQuoteModalOpen] = useState(false)
    const [isTenderModalOpen, setIsTenderModalOpen] = useState(false)
    const [isOutcomeModalOpen, setIsOutcomeModalOpen] = useState(false)
    const [isApprovalModalOpen, setIsApprovalModalOpen] = useState(false)
    const [isAuditModalOpen, setIsAuditModalOpen] = useState(false)

    // Selected item states
    const [selectedQuoteForApproval, setSelectedQuoteForApproval] = useState<SalesQuotation | null>(null)
    const [selectedTenderForOutcome, setSelectedTenderForOutcome] = useState<TenderRecord | null>(null)
    const [approvalRemarks, setApprovalRemarks] = useState('')
    const [auditPackageData, setAuditPackageData] = useState<any>(null)
    const [convertedSoModal, setConvertedSoModal] = useState<{
      quoteNumber: string
      orderNumber: string
      orderId: number
      customerName?: string
      grandTotal?: number
    } | null>(null)
    const [outcomeForm, setOutcomeForm] = useState({
      status: 'Awarded',
      l1_vendor_name: '',
      l1_price: 0,
      outcome_remarks: '',
    })

    // Filters
    const [leadSearch, setLeadSearch] = useState('')
    const [leadStatusFilter, setLeadStatusFilter] = useState('ALL')
    const [quoteSearch, setQuoteSearch] = useState('')
    const [selectedLeadFilter, setSelectedLeadFilter] = useState<string>(leadIdQuery)

    useEffect(() => {
      if (leadIdQuery) {
        setSelectedLeadFilter(leadIdQuery)
      }
    }, [leadIdQuery])

    const filteredQuotes = useMemo(() => {
      return quotes.filter((q: SalesQuotation) => {
        if (selectedLeadFilter && selectedLeadFilter !== 'ALL') {
          if (selectedLeadFilter === 'direct') {
            if (q.lead_id) return false
          } else if (String(q.lead_id) !== selectedLeadFilter) {
            return false
          }
        }
        if (quoteSearch.trim()) {
          const s = quoteSearch.toLowerCase()
          const matchQuote = q.quote_number?.toLowerCase().includes(s)
          const matchCustomer = q.customer_name?.toLowerCase().includes(s)
          const matchLead = q.lead_number?.toLowerCase().includes(s)
          if (!matchQuote && !matchCustomer && !matchLead) return false
        }
        return true
      })
    }, [quotes, selectedLeadFilter, quoteSearch])

    // Form: New Lead (Zero Mock Data - Blank by Default)
    const [newLead, setNewLead] = useState({
      company_name: '',
      contact_person: '',
      contact_phone: '',
      contact_email: '',
      city: '',
      region: '',
      sale_type: 'End_Customer' as const,
      product_class: '',
      estimated_value: 0,
      assigned_to_user_id: currentUser?.id || 1,
    })

    // Form: Field Visit (Zero Mock Data - Blank by Default)
    const [newVisit, setNewVisit] = useState({
      customer_name: '',
      location_name: '',
      contact_person_met: '',
      latitude: 0,
      longitude: 0,
      discussion_notes: '',
      next_follow_up: '',
    })

    // Form: Quotation Multi-Item (Zero Mock Data - Blank by Default)
    const createDefaultQuoteItem = (initialName = '') => ({
      id: `item-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`,
      product_id: undefined as number | undefined,
      product_name: initialName,
      product_sku: '',
      quantity: 1,
      unit_cost_price: 0,
      unit_msp_floor: 0,
      quoted_unit_price: 0,
    })

    const [newQuote, setNewQuote] = useState({
      customer_name: '',
      sale_type: 'End_Customer',
      lead_id: undefined as number | undefined,
      discount_amount: 0,
      approval_remarks: '',
      items: [
        {
          id: 'item-1',
          product_id: undefined as number | undefined,
          product_name: '',
          product_sku: '',
          quantity: 1,
          unit_cost_price: 0,
          unit_msp_floor: 0,
          quoted_unit_price: 0,
        },
      ],
    })

    // Form: Tender (Zero Mock Data - Blank by Default)
    const [newTender, setNewTender] = useState({
      tender_ref_no: '',
      issuing_authority: '',
      authority_type: 'PSU',
      tender_title: '',
      estimated_value: 0,
      quote_strategy: 'Direct_AVO',
      allocated_dealer_name: '',
      avo_quoted_price: 0,
      guarantee_type: 'EMD_BG',
      guarantee_amount: 0,
      issuing_bank: '',
    })

    // =========================================================================
    // MUTATIONS
    // =========================================================================
    const createLeadMutation = useMutation({
      mutationFn: salesApi.createLead,
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['sales-leads'] })
        setIsLeadModalOpen(false)
        showFeedback('success', 'Lead created successfully.')
      },
      onError: (err: any) => showFeedback('error', err?.response?.data?.message || 'Failed to create lead'),
    })

    const createQuickCustomerMutation = useMutation({
      mutationFn: customerApi.create,
      onSuccess: (newCust: Customer) => {
        queryClient.invalidateQueries({ queryKey: ['masters-customers'] })
        queryClient.invalidateQueries({ queryKey: ['customers'] })
        setIsQuickAddCustomerOpen(false)
        setQuickCustomer({
          name: '',
          company: '',
          email: '',
          phone: '',
          tax_number: '',
          credit_limit: 500000,
          address: '',
        })
        setQuickCustomerError(null)

        // Automatically select the new customer in the lead form and autofill details!
        if (newCust && newCust.id) {
          setSelectedCustomerId(String(newCust.id))
          setNewLead((prev) => ({
            ...prev,
            company_name: newCust.company || newCust.name,
            contact_person: newCust.name,
            contact_phone: newCust.phone || prev.contact_phone,
            contact_email: newCust.email || prev.contact_email,
          }))
        }
        showFeedback('success', `Customer "${newCust.name}" registered in Masters and selected!`)
      },
      onError: (err: any) => {
        setQuickCustomerError(err?.response?.data?.message || err?.message || 'Failed to create customer.')
      },
    })

    const handleCustomerSelect = (customerIdStr: string) => {
      setSelectedCustomerId(customerIdStr)
      if (!customerIdStr) return

      const cust = customers.find((c) => String(c.id) === customerIdStr)
      if (cust) {
        setNewLead((prev) => ({
          ...prev,
          company_name: cust.company || cust.name,
          contact_person: cust.name,
          contact_phone: cust.phone || prev.contact_phone,
          contact_email: cust.email || prev.contact_email,
        }))
      }
    }

    const createVisitMutation = useMutation({
      mutationFn: salesApi.createVisit,
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['sales-visits'] })
        setIsVisitModalOpen(false)
        showFeedback('success', 'Field visit logged with GPS coordinates.')
      },
      onError: (err: any) => showFeedback('error', err?.response?.data?.message || 'Failed to log visit'),
    })

    const createQuoteMutation = useMutation({
      mutationFn: (payload: any) => salesApi.createQuote(payload),
      onSuccess: (data: SalesQuotation) => {
        queryClient.invalidateQueries({ queryKey: ['sales-quotes'] })
        setIsQuoteModalOpen(false)
        if (data.is_below_msp) {
          showFeedback(
            'error',
            `Quotation ${data.quote_number} generated BELOW Floor MSP! Escalated to VP for L3 authorization.`
          )
        } else {
          showFeedback('success', `Quotation ${data.quote_number} AUTO-APPROVED (Complies with Floor MSP).`)
        }
      },
      onError: (err: any) => showFeedback('error', err?.response?.data?.message || 'Failed to generate quote'),
    })

    const approveQuoteMutation = useMutation({
      mutationFn: ({ id, approved, remarks }: { id: number; approved: boolean; remarks?: string }) =>
        salesApi.approveQuote(id, approved, remarks),
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['sales-quotes'] })
        setIsApprovalModalOpen(false)
        setSelectedQuoteForApproval(null)
        showFeedback('success', 'Quotation decision recorded and updated in database.')
      },
      onError: (err: any) => showFeedback('error', err?.response?.data?.message || 'Authorization failed'),
    })

    const convertToSOMutation = useMutation({
      mutationFn: (quoteId: number) => salesApi.convertQuoteToSO(quoteId),
      onSuccess: (data: any) => {
        queryClient.invalidateQueries({ queryKey: ['sales-quotes'] })
        queryClient.invalidateQueries({ queryKey: ['sales-milestones'] })
        setConvertedSoModal({
          quoteNumber: data.quote_number || 'Quotation',
          orderNumber: data.order_number,
          orderId: data.order_id,
          customerName: data.customer_name,
          grandTotal: data.grand_total,
        })
        showFeedback('success', data.message || `Quotation successfully converted to Sales Order ${data.order_number}!`)
      },
      onError: (err: any) => showFeedback('error', err?.response?.data?.message || 'Failed to convert to SO'),
    })

    const createTenderMutation = useMutation({
      mutationFn: salesApi.createTender,
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['sales-tenders'] })
        queryClient.invalidateQueries({ queryKey: ['sales-guarantees'] })
        setIsTenderModalOpen(false)
        showFeedback('success', 'Tender & Bank Guarantee registered.')
      },
      onError: (err: any) => showFeedback('error', err?.response?.data?.message || 'Failed to register tender'),
    })

    const updateTenderOutcomeMutation = useMutation({
      mutationFn: ({ id, data }: { id: number; data: any }) => salesApi.updateTenderOutcome(id, data),
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['sales-tenders'] })
        setIsOutcomeModalOpen(false)
        setSelectedTenderForOutcome(null)
        showFeedback('success', 'Tender outcome updated successfully.')
      },
      onError: (err: any) => showFeedback('error', err?.response?.data?.message || 'Failed to update tender outcome'),
    })

    // Filtered Leads
    const filteredLeads = useMemo(() => {
      return leads.filter((l) => {
        const matchSearch =
          !leadSearch ||
          l.company_name.toLowerCase().includes(leadSearch.toLowerCase()) ||
          l.contact_person.toLowerCase().includes(leadSearch.toLowerCase()) ||
          l.lead_number.toLowerCase().includes(leadSearch.toLowerCase())
        const matchStatus = leadStatusFilter === 'ALL' || l.status === leadStatusFilter
        return matchSearch && matchStatus
      })
    }, [leads, leadSearch, leadStatusFilter])

    // Calculation for Multi-Item CPQ Quote Builder Simulator
    const liveQuoteSubtotal = useMemo(() => {
      return (newQuote.items || []).reduce(
        (sum, item) => sum + (Number(item.quoted_unit_price || 0) * Math.max(1, Number(item.quantity || 1))),
        0
      )
    }, [newQuote.items])

    const liveQuoteCost = useMemo(() => {
      return (newQuote.items || []).reduce(
        (sum, item) => sum + (Number(item.unit_cost_price || 0) * Math.max(1, Number(item.quantity || 1))),
        0
      )
    }, [newQuote.items])

    const liveQuoteMsp = useMemo(() => {
      return (newQuote.items || []).reduce(
        (sum, item) => sum + (Number(item.unit_msp_floor || 0) * Math.max(1, Number(item.quantity || 1))),
        0
      )
    }, [newQuote.items])

    const liveFinalAmount = Math.max(0, liveQuoteSubtotal - (Number(newQuote.discount_amount) || 0))
    const liveIsBelowMsp = liveFinalAmount < liveQuoteMsp
    const liveMarginPct =
      liveFinalAmount > 0
        ? (((liveFinalAmount - liveQuoteCost) / liveFinalAmount) * 100).toFixed(1)
        : '0.0'

    const handleAddQuoteItem = () => {
      setNewQuote((prev) => ({
        ...prev,
        items: [...prev.items, createDefaultQuoteItem()],
      }))
    }

    const handleRemoveQuoteItem = (idx: number) => {
      if (newQuote.items.length <= 1) return
      setNewQuote((prev) => ({
        ...prev,
        items: prev.items.filter((_, i) => i !== idx),
      }))
    }

    const handleQuoteItemChange = (idx: number, field: string, val: any) => {
      setNewQuote((prev) => {
        const nextItems = [...prev.items]
        nextItems[idx] = { ...nextItems[idx], [field]: val }
        return { ...prev, items: nextItems }
      })
    }

    const handleSelectCatalogProduct = (idx: number, productId: number) => {
      const p = dbProducts.find((item: any) => item.id === productId)
      setNewQuote((prev) => {
        const nextItems = [...prev.items]
        if (p) {
          nextItems[idx] = {
            ...nextItems[idx],
            product_id: p.id,
            product_name: p.name,
            product_sku: p.sku || '',
            unit_cost_price: Number(p.cost_price || 0),
            unit_msp_floor: Number(p.selling_price || 0),
            quoted_unit_price: Number(p.selling_price || 0),
          }
        } else {
          nextItems[idx] = {
            ...nextItems[idx],
            product_id: undefined,
          }
        }
        return { ...prev, items: nextItems }
      })
    }

    return (
      <div className="space-y-4">
        {/* Feedback Alert */}
        {feedback && (
          <div
            className={`p-3 rounded-md text-xs font-medium flex items-center justify-between border ${feedback.type === 'success'
                ? 'bg-zinc-900 text-white border-zinc-800'
                : 'bg-zinc-100 text-zinc-900 border-zinc-300'
              }`}
          >
            <div className="flex items-center space-x-2">
              {feedback.type === 'success' ? (
                <CheckCircle2 className="w-4 h-4 text-emerald-400" />
              ) : (
                <AlertTriangle className="w-4 h-4 text-amber-500" />
              )}
              <span>{feedback.message}</span>
            </div>
            <button onClick={() => setFeedback(null)} className="text-zinc-400 hover:text-white text-xs">
              ✕
            </button>
          </div>
        )}

        {/* Top Banner & Module Header */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white p-4 rounded-lg border border-zinc-200 shadow-2xs">
          <div>
            <div className="flex items-center space-x-2">
              <div className="p-1.5 rounded-md bg-zinc-900 text-white">
                <Briefcase className="w-4 h-4" />
              </div>
              <h1 className="text-base font-bold tracking-tight text-zinc-900">
                Commercial, Tender & Bid Platform
              </h1>
              <span className="text-[10px] px-2 py-0.5 rounded-full font-mono font-semibold bg-zinc-100 text-zinc-800 border border-zinc-200">
                Module 06 / 09
              </span>
            </div>
            <p className="text-xs text-zinc-500 mt-1">
              End-to-end commercial pipeline &bull; Dynamic CPQ Floor MSP governance &bull; Streamlined Tender Register &bull; SI Multi-bid hit rate deduplication
            </p>
          </div>
        </div>

        {/* KPI Overview Cards */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
          <Card className="border-zinc-200 shadow-2xs">
            <CardContent className="p-3">
              <div className="flex items-center justify-between text-zinc-500 text-xs">
                <span>Active Leads</span>
                <Users className="w-3.5 h-3.5" />
              </div>
              <div className="text-xl font-bold font-mono text-zinc-900 mt-1">{leads.length}</div>
              <p className="text-[10px] text-zinc-400 mt-0.5">
                Pipeline: ₹{(leads.reduce((acc, l) => acc + Number(l.estimated_value), 0) / 100000).toFixed(1)} Lakh
              </p>
            </CardContent>
          </Card>

          <Card className="border-zinc-200 shadow-2xs">
            <CardContent className="p-3">
              <div className="flex items-center justify-between text-zinc-500 text-xs">
                <span>Floor MSP Alerts</span>
                <AlertTriangle className="w-3.5 h-3.5 text-zinc-700" />
              </div>
              <div className="text-xl font-bold font-mono text-zinc-900 mt-1">
                {quotes.filter((q) => q.approval_status === 'Pending_VP_Approval').length}
              </div>
              <p className="text-[10px] text-zinc-500 mt-0.5">
                {isL3VP ? 'Action needed by L3 VP' : 'Pending VP approval'}
              </p>
            </CardContent>
          </Card>

          <Card className="border-zinc-200 shadow-2xs">
            <CardContent className="p-3">
              <div className="flex items-center justify-between text-zinc-500 text-xs">
                <span>Active Tenders</span>
                <FileText className="w-3.5 h-3.5" />
              </div>
              <div className="text-xl font-bold font-mono text-zinc-900 mt-1">{tenders.length}</div>
              <p className="text-[10px] text-zinc-400 mt-0.5">
                Securities: {guarantees.length} EMD / PBGs Active
              </p>
            </CardContent>
          </Card>

          <Card className="border-zinc-200 shadow-2xs">
            <CardContent className="p-3">
              <div className="flex items-center justify-between text-zinc-500 text-xs">
                <span>Clustered Hit Rate</span>
                <TrendingUp className="w-3.5 h-3.5" />
              </div>
              <div className="text-xl font-bold font-mono text-zinc-900 mt-1">
                {clusterData?.analytics?.clustered_hit_rate_pct || 0}%
              </div>
              <p className="text-[10px] text-zinc-400 mt-0.5">
                vs {clusterData?.analytics?.diluted_raw_hit_rate_pct || 0}% raw diluted SI bids
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Active Section Header (Navigated via Sidebar Submenu) */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between pb-2 border-b border-zinc-200 gap-2">
          <div className="flex items-center space-x-2">
            <span className="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">Sub-module:</span>
            <span className="text-xs font-semibold text-zinc-900 bg-zinc-100 px-2.5 py-1 rounded-md border border-zinc-200">
              {tabTitles[activeTab]?.title || 'Leads & Field Visits'}
            </span>
            <span className="text-xs text-zinc-500 hidden md:inline">• {tabTitles[activeTab]?.desc}</span>
          </div>
        </div>

        {/* ===================================================================== */}
        {/* TAB 1: LEADS & PROSPECTS DIRECTORY + FIELD VISITS                    */}
        {/* ===================================================================== */}
        {activeTab === 'leads' && (
          <div className="space-y-4">
            {/* Controls Bar */}
            <Card className="border-zinc-200 shadow-2xs">
              <CardContent className="p-3">
                <div className="flex flex-col md:flex-row gap-2 justify-between">
                  <div className="flex flex-col sm:flex-row flex-1 gap-2">
                    <div className="relative flex-1">
                      <Search className="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-zinc-400" />
                      <Input
                        placeholder="Search by prospect company, contact, city..."
                        value={leadSearch}
                        onChange={(e) => setLeadSearch(e.target.value)}
                        className="h-8 pl-8 text-xs bg-zinc-50/50 border-zinc-200 focus:bg-white"
                      />
                    </div>

                    <select
                      value={leadStatusFilter}
                      onChange={(e) => setLeadStatusFilter(e.target.value)}
                      className="h-8 px-2.5 text-xs bg-zinc-50/50 border border-zinc-200 rounded-md text-zinc-700 focus:bg-white"
                    >
                      <option value="ALL">All Stages</option>
                      <option value="New">New</option>
                      <option value="Contacted">Contacted</option>
                      <option value="Qualified">Qualified</option>
                      <option value="Quoted">Quoted</option>
                      <option value="Negotiation">Negotiation</option>
                      <option value="Won">Won</option>
                      <option value="Lost">Lost</option>
                    </select>
                  </div>

                  <div className="flex flex-wrap sm:flex-nowrap gap-2">
                    <Button
                      onClick={() => setIsVisitModalOpen(true)}
                      variant="outline"
                      size="sm"
                      className="h-8 text-xs text-zinc-700 border-zinc-300"
                    >
                      <MapPin className="w-3.5 h-3.5 mr-1 text-zinc-500" />
                      Log Field Visit
                    </Button>
                    <Button
                      onClick={() => setIsLeadModalOpen(true)}
                      size="sm"
                      className="h-8 text-xs bg-zinc-900 text-white hover:bg-zinc-800"
                    >
                      <Plus className="w-3.5 h-3.5 mr-1" />
                      Capture Lead
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Leads Table */}
            <Card className="border-zinc-200 shadow-2xs overflow-hidden">
              <div className="p-3 border-b border-zinc-100 bg-zinc-50/60 flex items-center justify-between">
                <div>
                  <h3 className="text-xs font-semibold text-zinc-900">Lead & Prospect Pipeline</h3>
                  <p className="text-[10px] text-zinc-500">Live directory of active commercial leads with stage tracking</p>
                </div>
                <span className="text-[10px] font-mono text-zinc-500">{filteredLeads.length} leads</span>
              </div>

              <div className="overflow-x-auto">
                <Table className="min-w-[850px]">
                  <TableHeader className="bg-zinc-50/80 border-b border-zinc-200">
                    <TableRow>
                      <TableHead className="w-10 text-center font-semibold text-zinc-600 text-[11px]">#</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Lead #</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Company & Contact</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Sale Type</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Product Class</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px] text-right">Est. Value</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Assigned Rep</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px] text-center">Quotations</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px] text-center">Stage</TableHead>
                      <TableHead className="text-right font-semibold text-zinc-600 text-[11px]">Action</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {leadsLoading ? (
                      <TableRow>
                        <TableCell colSpan={10} className="text-center py-8 text-zinc-400 text-xs">
                          Loading leads...
                        </TableCell>
                      </TableRow>
                    ) : filteredLeads.length === 0 ? (
                      <TableRow>
                        <TableCell colSpan={10} className="text-center py-8 text-zinc-400 text-xs">
                          No leads found matching your search.
                        </TableCell>
                      </TableRow>
                    ) : (
                      filteredLeads.map((lead: SalesLead, idx: number) => (
                        <TableRow key={lead.id} className="hover:bg-zinc-50/60 transition-colors">
                          <TableCell className="text-center font-mono text-[11px] text-zinc-400 w-10">
                            {idx + 1}
                          </TableCell>
                          <TableCell className="font-mono text-xs font-semibold text-zinc-900">
                            {lead.lead_number}
                          </TableCell>
                          <TableCell>
                            <div className="font-medium text-zinc-900 text-xs">{lead.company_name}</div>
                            <div className="text-[10px] text-zinc-400">
                              {lead.contact_person} &bull; {lead.contact_phone}
                            </div>
                          </TableCell>
                          <TableCell>
                            <span className="text-[10px] px-1.5 py-0.5 rounded font-mono bg-zinc-100 text-zinc-800 border border-zinc-200">
                              {lead.sale_type.replace('_', ' ')}
                            </span>
                          </TableCell>
                          <TableCell className="text-xs text-zinc-600 whitespace-nowrap">
                            {lead.product_class}
                          </TableCell>
                          <TableCell className="text-xs font-mono font-semibold text-zinc-900 text-right">
                            ₹{Number(lead.estimated_value).toLocaleString('en-IN')}
                          </TableCell>
                          <TableCell className="text-xs text-zinc-700">
                            {lead.salesperson_name || 'Unassigned'}
                          </TableCell>
                          <TableCell className="text-center">
                            {Number(lead.quotes_count || 0) > 0 ? (
                              <div className="flex flex-col items-center">
                                <Link
                                  to={`/sales/quotes?lead_id=${lead.id}`}
                                  className="inline-flex items-center gap-1 font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-zinc-900 text-white hover:bg-zinc-800 transition-colors shadow-2xs"
                                  title={`View all ${lead.quotes_count} quotation options for this lead`}
                                >
                                  <span>{lead.quotes_count} {Number(lead.quotes_count) === 1 ? 'Quote' : 'Quotes'}</span>
                                  <ChevronRight className="w-2.5 h-2.5" />
                                </Link>
                                {lead.quote_numbers && (
                                  <span className="text-[9px] font-mono text-zinc-500 max-w-[130px] truncate mt-0.5" title={lead.quote_numbers}>
                                    {lead.quote_numbers}
                                  </span>
                                )}
                              </div>
                            ) : (
                              <span className="text-[10px] text-zinc-400 font-mono italic">
                                0 Quotes
                              </span>
                            )}
                          </TableCell>
                          <TableCell className="text-center">
                            <span
                              className={`text-[10px] px-2 py-0.5 rounded-full font-medium ${lead.status === 'Won'
                                  ? 'bg-zinc-900 text-white'
                                  : lead.status === 'Lost'
                                    ? 'bg-zinc-200 text-zinc-600 line-through'
                                    : lead.status === 'Quoted' || lead.status === 'Negotiation'
                                      ? 'bg-zinc-100 text-zinc-900 font-semibold border border-zinc-300'
                                      : 'bg-zinc-100 text-zinc-600'
                                }`}
                            >
                              {lead.status}
                            </span>
                          </TableCell>
                          <TableCell className="text-right">
                            <Button
                              onClick={() => navigate(`/sales/quotes/new?lead_id=${lead.id}`)}
                              variant="outline"
                              size="sm"
                              className="h-6 text-[11px] px-2 text-zinc-800 bg-white border-zinc-300 hover:bg-zinc-100 font-medium"
                              title={Number(lead.quotes_count || 0) > 0 ? "Generate another quotation option/revision for this lead" : "Create quotation for this lead"}
                            >
                              <Plus className="w-3 h-3 mr-0.5 text-zinc-500" />
                              {Number(lead.quotes_count || 0) > 0 ? 'Add Option Quote' : 'Create Quote'}
                            </Button>
                          </TableCell>
                        </TableRow>
                      ))
                    )}
                  </TableBody>
                </Table>
              </div>
            </Card>

            {/* Sales Field Visits Log (MK-09) */}
            <Card className="border-zinc-200 shadow-2xs overflow-hidden">
              <div className="p-3 border-b border-zinc-100 bg-zinc-50/60 flex items-center justify-between">
                <div>
                  <h3 className="text-xs font-semibold text-zinc-900">Sales Field Visits & GPS Check-In Log</h3>
                  <p className="text-[10px] text-zinc-500">
                    On-site customer visits verified via coordinates (MK-09)
                  </p>
                </div>
                <span className="text-[10px] font-mono text-zinc-500">{visits.length} logged visits</span>
              </div>

              <div className="overflow-x-auto">
                <Table className="min-w-[850px]">
                  <TableHeader className="bg-zinc-50/80 border-b border-zinc-200">
                    <TableRow>
                      <TableHead className="w-10 text-center font-semibold text-zinc-600 text-[11px]">#</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Visit Date</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Customer & Location</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Salesperson</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Contact Person Met</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">GPS Coordinates</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Discussion Notes</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Next Follow-up</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {visitsLoading ? (
                      <TableRow>
                        <TableCell colSpan={8} className="text-center py-6 text-zinc-400 text-xs">
                          Loading visit logs...
                        </TableCell>
                      </TableRow>
                    ) : visits.length === 0 ? (
                      <TableRow>
                        <TableCell colSpan={8} className="text-center py-6 text-zinc-400 text-xs">
                          No field visits recorded yet.
                        </TableCell>
                      </TableRow>
                    ) : (
                      visits.map((v: SalesVisit, idx: number) => (
                        <TableRow key={v.id} className="hover:bg-zinc-50/60 transition-colors">
                          <TableCell className="text-center font-mono text-[11px] text-zinc-400 w-10">
                            {idx + 1}
                          </TableCell>
                          <TableCell className="font-mono text-xs text-zinc-900">{v.visit_date}</TableCell>
                          <TableCell>
                            <div className="font-medium text-zinc-900 text-xs">{v.customer_name}</div>
                            <div className="text-[10px] text-zinc-400">{v.location_name}</div>
                          </TableCell>
                          <TableCell className="text-xs text-zinc-700">{v.salesperson_name}</TableCell>
                          <TableCell className="text-xs text-zinc-800">{v.contact_person_met}</TableCell>
                          <TableCell className="font-mono text-[10px] text-zinc-500">
                            {v.latitude && v.longitude ? `${v.latitude}, ${v.longitude}` : 'Manual site entry'}
                          </TableCell>
                          <TableCell className="text-xs text-zinc-600 max-w-64 truncate">
                            {v.discussion_notes}
                          </TableCell>
                          <TableCell className="font-mono text-xs text-zinc-600">
                            {v.next_follow_up || '—'}
                          </TableCell>
                        </TableRow>
                      ))
                    )}
                  </TableBody>
                </Table>
              </div>
            </Card>
          </div>
        )}

        {/* ===================================================================== */}
        {/* TAB 2: QUOTATION BUILDER & BELOW-MSP PRICING GATE                    */}
        {/* ===================================================================== */}
        {activeTab === 'quotes' && (
          <div className="space-y-4">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div>
                <h2 className="text-xs font-semibold text-zinc-900">Quotation Portfolio & Floor MSP Gate</h2>
                <p className="text-[11px] text-zinc-500">
                  Quotes $\ge$ Floor MSP auto-authorize. Quotes &lt; Floor MSP flag an approval request for L3 VP authorization.
                </p>
              </div>
              <Button
                onClick={() => navigate('/sales/quotes/new')}
                size="sm"
                className="h-8 text-xs bg-zinc-900 text-white hover:bg-zinc-800 shrink-0 self-start sm:self-auto"
              >
                <Plus className="w-3.5 h-3.5 mr-1" />
                Build New Quotation
              </Button>
            </div>

            {/* Filter & Search Bar for Quotations */}
            <Card className="border-zinc-200 shadow-2xs">
              <CardContent className="p-3">
                <div className="flex flex-col md:flex-row gap-2 justify-between items-center">
                  <div className="flex flex-col sm:flex-row flex-1 gap-2 w-full md:w-auto">
                    <div className="relative flex-1 max-w-sm">
                      <Search className="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-zinc-400" />
                      <Input
                        placeholder="Search by quote #, customer, or lead #..."
                        value={quoteSearch}
                        onChange={(e) => setQuoteSearch(e.target.value)}
                        className="pl-8 h-8 text-xs bg-white border-zinc-200"
                      />
                    </div>
                    <select
                      value={selectedLeadFilter}
                      onChange={(e) => {
                        setSelectedLeadFilter(e.target.value)
                        if (e.target.value) {
                          setSearchParams({ lead_id: e.target.value })
                        } else {
                          setSearchParams({})
                        }
                      }}
                      className="h-8 text-xs border border-zinc-200 rounded px-2.5 bg-white text-zinc-700"
                    >
                      <option value="">All Leads & Direct Quotes ({quotes.length})</option>
                      <option value="direct">Direct Commercial Only (No Lead)</option>
                      <optgroup label="Filter by Commercial Lead">
                        {leads.map((l: SalesLead) => (
                          <option key={l.id} value={String(l.id)}>
                            {l.lead_number} • {l.company_name} ({l.quotes_count || 0} quotes)
                          </option>
                        ))}
                      </optgroup>
                    </select>
                  </div>
                  {selectedLeadFilter && (
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => {
                        setSelectedLeadFilter('')
                        setSearchParams({})
                      }}
                      className="h-8 text-xs text-zinc-500 hover:text-zinc-900"
                    >
                      Clear Lead Filter
                    </Button>
                  )}
                </div>
              </CardContent>
            </Card>

            {/* Quotations Table */}
            <Card className="border-zinc-200 shadow-2xs overflow-hidden">
              <div className="overflow-x-auto">
                <Table className="min-w-[850px]">
                  <TableHeader className="bg-zinc-50/80 border-b border-zinc-200">
                    <TableRow>
                      <TableHead className="w-10 text-center font-semibold text-zinc-600 text-[11px]">#</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Quote #</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Customer & Lead</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Sale Type</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px] text-right">Final Amount</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px] text-right">Floor MSP</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px] text-right">Gross Margin</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px] text-center">MSP Floor Gate</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px] text-center">Approval Status</TableHead>
                      <TableHead className="text-right font-semibold text-zinc-600 text-[11px]">Action</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {quotesLoading ? (
                      <TableRow>
                        <TableCell colSpan={10} className="text-center py-8 text-zinc-400 text-xs">
                          Loading quotations...
                        </TableCell>
                      </TableRow>
                    ) : filteredQuotes.length === 0 ? (
                      <TableRow>
                        <TableCell colSpan={10} className="text-center py-8 text-zinc-400 text-xs">
                          {selectedLeadFilter || quoteSearch
                            ? 'No quotations match the active lead / search filter.'
                            : 'No quotations created yet. Click "Build New Quotation" to simulate.'}
                        </TableCell>
                      </TableRow>
                    ) : (
                      filteredQuotes.map((q: SalesQuotation, idx: number) => (
                        <TableRow key={q.id} className="hover:bg-zinc-50/60 transition-colors">
                          <TableCell className="text-center font-mono text-[11px] text-zinc-400 w-10">
                            {idx + 1}
                          </TableCell>
                          <TableCell className="font-mono text-xs font-semibold text-zinc-900">
                            <Link
                              to={`/sales/quotes/${q.id}`}
                              className="hover:underline hover:text-blue-600 transition-colors"
                            >
                              {q.quote_number}
                            </Link>
                            <div className="text-[10px] font-mono text-zinc-400 font-normal">
                              {q.quote_date || (q.created_at ? q.created_at.split(' ')[0] : '')}
                            </div>
                          </TableCell>
                          <TableCell>
                            <div className="font-medium text-zinc-900 text-xs">{q.customer_name}</div>
                            <div className="flex items-center gap-1.5 mt-0.5">
                              {q.lead_number ? (
                                <span className="inline-flex items-center gap-1 font-mono text-[10px] bg-blue-50 text-blue-700 border border-blue-200 px-1.5 py-0.2 rounded font-medium">
                                  Lead: {q.lead_number}
                                </span>
                              ) : (
                                <span className="font-mono text-[10px] text-zinc-400">
                                  Direct (No Lead)
                                </span>
                              )}
                            </div>
                            <div className="text-[10px] text-zinc-500 line-clamp-1 max-w-xs mt-0.5">
                              {q.items && q.items.length > 0
                                ? `${q.items.length} ${q.items.length === 1 ? 'item' : 'items'}: ${q.items.map((it: any) => `${it.quantity}x ${it.product_name}`).join(', ')}`
                                : `By ${q.salesperson_name}`}
                            </div>
                          </TableCell>
                          <TableCell>
                            <span className="text-[10px] px-1.5 py-0.5 rounded font-mono bg-zinc-100 text-zinc-800 border border-zinc-200">
                              {q.sale_type.replace('_', ' ')}
                            </span>
                          </TableCell>
                          <TableCell className="font-mono font-semibold text-xs text-zinc-900 text-right">
                            ₹{Number(q.final_quote_amount).toLocaleString('en-IN')}
                          </TableCell>
                          <TableCell className="font-mono text-xs text-zinc-500 text-right">
                            ₹{Number(q.total_msp_floor).toLocaleString('en-IN')}
                          </TableCell>
                          <TableCell className="font-mono text-xs font-medium text-zinc-900 text-right">
                            {q.gross_margin_percent}%
                          </TableCell>
                          <TableCell className="text-center">
                            {q.is_below_msp ? (
                              <span className="text-[10px] px-2 py-0.5 rounded font-bold font-mono bg-zinc-900 text-white">
                                BELOW MSP (-₹{Number(q.msp_shortfall_amount).toLocaleString('en-IN')})
                              </span>
                            ) : (
                              <span className="text-[10px] px-2 py-0.5 rounded font-medium bg-zinc-100 text-zinc-800 border border-zinc-200">
                                &ge; FLOOR MSP
                              </span>
                            )}
                          </TableCell>
                          <TableCell className="text-center">
                            {q.converted_order_number ? (
                              <span className="text-[10px] px-2 py-0.5 rounded-full font-medium bg-emerald-100 text-emerald-900 border border-emerald-300">
                                SO Created
                              </span>
                            ) : (
                              <span
                                className={`text-[10px] px-2 py-0.5 rounded-full font-medium ${q.approval_status === 'Auto_Approved' || q.approval_status === 'Approved'
                                    ? 'bg-zinc-900 text-white'
                                    : q.approval_status === 'Rejected'
                                      ? 'bg-zinc-200 text-zinc-600 line-through'
                                      : 'bg-amber-100 text-amber-900 border border-amber-300 font-semibold'
                                  }`}
                              >
                                {q.approval_status.replace(/_/g, ' ')}
                              </span>
                            )}
                          </TableCell>
                          <TableCell className="text-right">
                            <div className="flex items-center justify-end space-x-1.5">
                              {/* Dedicated View Page Button */}
                              <Button
                                onClick={() => navigate(`/sales/quotes/${q.id}`)}
                                variant="outline"
                                size="sm"
                                className="h-6 text-[11px] px-2 text-zinc-800 bg-white border-zinc-300 hover:bg-zinc-100 font-medium"
                                title="View Quotation"
                              >
                                <Eye className="w-3 h-3 mr-1 text-zinc-500" />
                                View
                              </Button>

                              {/* Dedicated Edit Page Button */}
                              <Button
                                onClick={() => navigate(`/sales/quotes/${q.id}/edit`)}
                                variant="outline"
                                size="sm"
                                className="h-6 text-[11px] px-2 text-zinc-800 bg-white border-zinc-300 hover:bg-zinc-100 font-medium"
                                title="Edit Quotation"
                              >
                                <Edit2 className="w-3 h-3 mr-1 text-zinc-500" />
                                Edit
                              </Button>

                              {/* If pending VP approval and current user is L3 */}
                              {q.approval_status === 'Pending_VP_Approval' && (
                                <Button
                                  onClick={() => {
                                    setSelectedQuoteForApproval(q)
                                    setIsApprovalModalOpen(true)
                                  }}
                                  size="sm"
                                  className="h-6 text-[11px] px-2 bg-zinc-900 text-white"
                                >
                                  {isL3VP ? 'Review & Authorize' : 'View Gate'}
                                </Button>
                              )}

                              {/* If converted, show link to SO in Milestones */}
                              {q.converted_order_number ? (
                                <Link
                                  to="/sales/milestones"
                                  className="inline-flex items-center gap-1 font-mono text-[10px] font-semibold px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-300 hover:bg-emerald-100 transition-colors shadow-2xs whitespace-nowrap"
                                  title={`Converted to Sales Order ${q.converted_order_number}. Click to track milestones.`}
                                >
                                  <CheckCircle2 className="w-3 h-3 text-emerald-600" />
                                  <span>SO: {q.converted_order_number}</span>
                                </Link>
                              ) : (q.approval_status === 'Auto_Approved' || q.approval_status === 'Approved') ? (
                                <Button
                                  onClick={() => convertToSOMutation.mutate(q.id)}
                                  variant="outline"
                                  size="sm"
                                  disabled={convertToSOMutation.isPending}
                                  className="h-6 text-[11px] px-2 text-zinc-900 bg-white border-zinc-300 hover:bg-zinc-100 font-medium whitespace-nowrap"
                                >
                                  {convertToSOMutation.isPending ? 'Converting...' : 'Convert to SO \u2192'}
                                </Button>
                              ) : null}
                            </div>
                          </TableCell>
                        </TableRow>
                      ))
                    )}
                  </TableBody>
                </Table>
              </div>
            </Card>
          </div>
        )}

        {/* ===================================================================== */}
        {/* TAB 3: SI MULTI-BID CLUSTERING (HIT RATE DEDUPLICATION)               */}
        {/* ===================================================================== */}
        {activeTab === 'clustering' && (
          <div className="space-y-4">
            <Card className="border-zinc-200 bg-zinc-50/50 shadow-2xs">
              <CardContent className="p-4">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                  <div className="space-y-1">
                    <div className="flex items-center space-x-2">
                      <Layers className="w-4 h-4 text-zinc-900" />
                      <h3 className="text-xs font-semibold text-zinc-900">
                        System Integrator (SI) Multi-Bid Hit Rate Deduplication (MK-05)
                      </h3>
                    </div>
                    <p className="text-xs text-zinc-600 leading-relaxed max-w-2xl">
                      In large enterprise and PSU tenders, multiple System Integrators (SIs) quote AVO UPS products on the same client RFP. Standard CRMs penalize sales reps by counting 1 Win + 2 Losses. AVO's clustering groups all SIs under one deal, calculating true company win/loss hit rates.
                    </p>
                  </div>

                  <div className="flex items-center space-x-6 border-l border-zinc-200 pl-6 shrink-0">
                    <div>
                      <span className="text-[10px] text-zinc-400 uppercase tracking-wider block font-semibold">
                        Clustered Win Rate
                      </span>
                      <span className="text-2xl font-bold font-mono text-zinc-900">
                        {clusterData?.analytics?.clustered_hit_rate_pct || 0}%
                      </span>
                      <span className="text-[10px] text-emerald-600 block">True Project Outcome</span>
                    </div>
                    <div>
                      <span className="text-[10px] text-zinc-400 uppercase tracking-wider block font-semibold">
                        Raw Diluted Rate
                      </span>
                      <span className="text-2xl font-bold font-mono text-zinc-400 line-through">
                        {clusterData?.analytics?.diluted_raw_hit_rate_pct || 0}%
                      </span>
                      <span className="text-[10px] text-zinc-400 block">Flawed Standard CRM Metric</span>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Clustered Deals Table */}
            <Card className="border-zinc-200 shadow-2xs overflow-hidden">
              <div className="overflow-x-auto">
                <Table className="min-w-[850px]">
                  <TableHeader className="bg-zinc-50/80 border-b border-zinc-200">
                    <TableRow>
                      <TableHead className="w-10 text-center font-semibold text-zinc-600 text-[11px]">#</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Cluster Code</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">End Client / PSU</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Project Scope</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px] text-right">Est. Deal Value</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Participating System Integrators</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px] text-center">Clustered Outcome</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {clustersLoading ? (
                      <TableRow>
                        <TableCell colSpan={7} className="text-center py-8 text-zinc-400 text-xs">
                          Loading clusters...
                        </TableCell>
                      </TableRow>
                    ) : !clusterData?.clusters?.length ? (
                      <TableRow>
                        <TableCell colSpan={7} className="text-center py-8 text-zinc-400 text-xs">
                          No SI RFP clusters registered yet.
                        </TableCell>
                      </TableRow>
                    ) : (
                      clusterData.clusters.map((c: SiRfpCluster, idx: number) => (
                        <TableRow key={c.id} className="hover:bg-zinc-50/60 transition-colors">
                          <TableCell className="text-center font-mono text-[11px] text-zinc-400 w-10">
                            {idx + 1}
                          </TableCell>
                          <TableCell className="font-mono text-xs font-semibold text-zinc-900">
                            {c.cluster_code}
                          </TableCell>
                          <TableCell className="font-medium text-xs text-zinc-900">{c.end_client_name}</TableCell>
                          <TableCell className="text-xs text-zinc-600">{c.project_scope}</TableCell>
                          <TableCell className="font-mono font-semibold text-xs text-zinc-900 text-right">
                            ₹{Number(c.est_total_deal_value).toLocaleString('en-IN')}
                          </TableCell>
                          <TableCell>
                            <div className="flex flex-wrap gap-1">
                              {c.bids?.map((b) => (
                                <span
                                  key={b.id}
                                  className={`text-[10px] px-1.5 py-0.5 rounded font-mono ${b.bid_status === 'L1'
                                      ? 'bg-zinc-900 text-white font-bold'
                                      : 'bg-zinc-100 text-zinc-700 border border-zinc-200'
                                    }`}
                                >
                                  {b.si_company_name} (₹{(b.quoted_amount / 100000).toFixed(1)}L)
                                </span>
                              ))}
                            </div>
                          </TableCell>
                          <TableCell className="text-center">
                            <span
                              className={`text-[10px] px-2 py-0.5 rounded-full font-medium ${c.cluster_status === 'Deal_Won'
                                  ? 'bg-zinc-900 text-white'
                                  : c.cluster_status === 'Deal_Lost'
                                    ? 'bg-zinc-200 text-zinc-600 line-through'
                                    : 'bg-zinc-100 text-zinc-800 border border-zinc-200'
                                }`}
                            >
                              {c.cluster_status === 'Deal_Won' ? `WON via ${c.winning_si_name}` : c.cluster_status.replace('_', ' ')}
                            </span>
                          </TableCell>
                        </TableRow>
                      ))
                    )}
                  </TableBody>
                </Table>
              </div>
            </Card>
          </div>
        )}

        {/* ===================================================================== */}
        {/* TAB 4: TENDER REGISTER & BANK GUARANTEES (CLEANED PER CLIENT PDF)     */}
        {/* ===================================================================== */}
        {activeTab === 'tenders' && (
          <div className="space-y-4">
            <div className="flex justify-between items-center">
              <div>
                <h2 className="text-xs font-semibold text-zinc-900">Tender Register & Bank Guarantee Ledger</h2>
                <p className="text-[11px] text-zinc-500">
                  Streamlined per Client PDF Directive §09.3: Tender log &rarr; Direct AVO Quote or Pass to Dealer &rarr; Outcome & L1 Price tracked
                </p>
              </div>
              <Button
                onClick={() => setIsTenderModalOpen(true)}
                size="sm"
                className="h-8 text-xs bg-zinc-900 text-white hover:bg-zinc-800"
              >
                <Plus className="w-3.5 h-3.5 mr-1" />
                Register Tender & EMD
              </Button>
            </div>

            {/* Tenders Table */}
            <Card className="border-zinc-200 shadow-2xs overflow-hidden">
              <div className="p-3 border-b border-zinc-100 bg-zinc-50/60 flex items-center justify-between">
                <h3 className="text-xs font-semibold text-zinc-900">Active Tenders & Bid Outcomes</h3>
                <span className="text-[10px] font-mono text-zinc-500">{tenders.length} records</span>
              </div>

              <div className="overflow-x-auto">
                <Table className="min-w-[850px]">
                  <TableHeader className="bg-zinc-50/80 border-b border-zinc-200">
                    <TableRow>
                      <TableHead className="w-10 text-center font-semibold text-zinc-600 text-[11px]">#</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Tender Ref #</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Issuing Authority & Title</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px] text-right">Est. Value</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Quote Strategy</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Submission Due</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px] text-center">Status</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">L1 Winner & Price</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px] text-right">Action</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {tendersLoading ? (
                      <TableRow>
                        <TableCell colSpan={9} className="text-center py-8 text-zinc-400 text-xs">
                          Loading tenders...
                        </TableCell>
                      </TableRow>
                    ) : tenders.length === 0 ? (
                      <TableRow>
                        <TableCell colSpan={9} className="text-center py-8 text-zinc-400 text-xs">
                          No tenders logged yet.
                        </TableCell>
                      </TableRow>
                    ) : (
                      tenders.map((t: TenderRecord, idx: number) => (
                        <TableRow key={t.id} className="hover:bg-zinc-50/60 transition-colors">
                          <TableCell className="text-center font-mono text-[11px] text-zinc-400 w-10 whitespace-nowrap">
                            {idx + 1}
                          </TableCell>
                          <TableCell className="font-mono text-xs font-semibold text-zinc-900 whitespace-nowrap">
                            {t.tender_ref_no}
                          </TableCell>
                          <TableCell className="whitespace-nowrap">
                            <div className="font-medium text-xs text-zinc-900">{t.issuing_authority}</div>
                            <div className="text-[10px] text-zinc-500 max-w-72 truncate">{t.tender_title}</div>
                          </TableCell>
                          <TableCell className="font-mono font-semibold text-xs text-zinc-900 text-right whitespace-nowrap">
                            ₹{Number(t.estimated_value).toLocaleString('en-IN')}
                          </TableCell>
                          <TableCell className="whitespace-nowrap">
                            <span
                              className={`text-[10px] px-1.5 py-0.5 rounded font-mono ${t.quote_strategy === 'Direct_AVO'
                                  ? 'bg-zinc-900 text-white'
                                  : 'bg-zinc-100 text-zinc-800 border border-zinc-200'
                                }`}
                            >
                              {t.quote_strategy.replace('_', ' ')}
                            </span>
                            {t.quote_strategy === 'Pass_To_Dealer' && t.allocated_dealer_name && (
                              <div className="text-[10px] text-zinc-500 font-medium mt-0.5 truncate max-w-44">
                                Dealer: {t.allocated_dealer_name}
                              </div>
                            )}
                            {t.avo_quoted_price ? (
                              <div className="text-[10px] font-mono text-zinc-500 mt-0.5">
                                Quote: ₹{Number(t.avo_quoted_price).toLocaleString('en-IN')}
                              </div>
                            ) : null}
                          </TableCell>
                          <TableCell className="font-mono text-xs text-zinc-700 whitespace-nowrap">
                            {t.bid_submission_deadline}
                          </TableCell>
                          <TableCell className="text-center whitespace-nowrap">
                            <span className={`text-[10px] px-2 py-0.5 rounded-full font-medium border ${
                              t.status === 'Awarded'
                                ? 'bg-zinc-900 text-white border-zinc-900'
                                : t.status === 'Lost'
                                ? 'bg-zinc-100 text-zinc-500 border-zinc-200 line-through'
                                : 'bg-zinc-100 text-zinc-800 border-zinc-200'
                            }`}>
                              {t.status.replace('_', ' ')}
                            </span>
                          </TableCell>
                          <TableCell className="text-xs text-zinc-700 whitespace-nowrap">
                            {t.l1_vendor_name ? (
                              <div>
                                <span className="font-medium text-zinc-900">{t.l1_vendor_name}</span> &bull; ₹
                                {Number(t.l1_price).toLocaleString('en-IN')}
                              </div>
                            ) : (
                              <span className="text-zinc-400 italic">Under Evaluation</span>
                            )}
                          </TableCell>
                          <TableCell className="text-right whitespace-nowrap">
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={() => {
                                setSelectedTenderForOutcome(t)
                                setOutcomeForm({
                                  status: t.status || 'Awarded',
                                  l1_vendor_name: t.l1_vendor_name || '',
                                  l1_price: t.l1_price || 0,
                                  outcome_remarks: t.outcome_remarks || '',
                                })
                                setIsOutcomeModalOpen(true)
                              }}
                              className="h-6.5 px-2 text-[11px] text-zinc-700 hover:text-black hover:bg-zinc-100"
                              title="Update Tender Outcome"
                            >
                              <Edit2 className="w-3 h-3 mr-1 text-zinc-500" />
                              Update Outcome
                            </Button>
                          </TableCell>
                        </TableRow>
                      ))
                    )}
                  </TableBody>
                </Table>
              </div>
            </Card>

            {/* Active Bank Guarantees & EMD Register */}
            <Card className="border-zinc-200 shadow-2xs overflow-hidden">
              <div className="p-3 border-b border-zinc-100 bg-zinc-50/60 flex items-center justify-between">
                <div>
                  <h3 className="text-xs font-semibold text-zinc-900">
                    Active Bank Guarantees & Earnest Money Deposit (EMD) Ledger
                  </h3>
                  <p className="text-[10px] text-zinc-500">
                    Zero locked capital overhang: Automated tracking of validity, claim dates & release auditing
                  </p>
                </div>
                <span className="text-[10px] font-mono text-zinc-500">{guarantees.length} active BGs</span>
              </div>

              <div className="overflow-x-auto">
                <Table className="min-w-[850px]">
                  <TableHeader className="bg-zinc-50/80 border-b border-zinc-200">
                    <TableRow>
                      <TableHead className="w-10 text-center font-semibold text-zinc-600 text-[11px]">#</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Tender Ref #</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Type</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px] text-right">BG Amount</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Issuing Bank & BG #</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Expiry Date</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px] text-center">Alert Status</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px] text-center">Status</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {guarantees.length === 0 ? (
                      <TableRow>
                        <TableCell colSpan={8} className="text-center py-6 text-zinc-400 text-xs">
                          No active bank guarantees found.
                        </TableCell>
                      </TableRow>
                    ) : (
                      guarantees.map((g: TenderGuarantee, idx: number) => (
                        <TableRow key={g.id} className="hover:bg-zinc-50/60 transition-colors">
                          <TableCell className="text-center font-mono text-[11px] text-zinc-400 w-10">
                            {idx + 1}
                          </TableCell>
                          <TableCell className="font-mono text-xs font-medium text-zinc-900">
                            {g.tender_ref_no}
                          </TableCell>
                          <TableCell>
                            <span className="text-[10px] px-1.5 py-0.5 rounded font-mono font-bold bg-zinc-900 text-white">
                              {g.guarantee_type}
                            </span>
                          </TableCell>
                          <TableCell className="font-mono font-semibold text-xs text-zinc-900 text-right">
                            ₹{Number(g.amount).toLocaleString('en-IN')}
                          </TableCell>
                          <TableCell>
                            <div className="font-medium text-xs text-zinc-900">{g.issuing_bank}</div>
                            <div className="text-[10px] font-mono text-zinc-400">{g.bg_number}</div>
                          </TableCell>
                          <TableCell className="font-mono text-xs text-zinc-700">{g.expiry_date}</TableCell>
                          <TableCell className="text-center">
                            <span
                              className={`text-[10px] px-2 py-0.5 rounded font-mono font-semibold ${g.alert_status === 'CRITICAL_7D'
                                  ? 'bg-zinc-900 text-white animate-pulse'
                                  : g.alert_status === 'WARNING_15D'
                                    ? 'bg-zinc-200 text-zinc-900'
                                    : 'bg-zinc-100 text-zinc-600'
                                }`}
                            >
                              {g.days_remaining}d remaining
                            </span>
                          </TableCell>
                          <TableCell className="text-center">
                            <span className="text-[10px] px-1.5 py-0.5 rounded font-medium bg-zinc-100 text-zinc-800">
                              {g.status}
                            </span>
                          </TableCell>
                        </TableRow>
                      ))
                    )}
                  </TableBody>
                </Table>
              </div>
            </Card>
          </div>
        )}

        {/* ===================================================================== */}
        {/* TAB 5: ORDER MILESTONES & 1-CLICK AUDIT PACKAGE                      */}
        {/* ===================================================================== */}
        {activeTab === 'milestones' && (
          <div className="space-y-4">
            <div>
              <h2 className="text-xs font-semibold text-zinc-900">
                Salesperson Order-to-Cash Milestone Tracker & Payment Audit Package (MK-11 / MK-12)
              </h2>
              <p className="text-[11px] text-zinc-500">
                Enables field reps to track orders through to installation and 1-click bundle Invoices + Signed POD + Field Service Reports.
              </p>
            </div>

            <Card className="border-zinc-200 shadow-2xs overflow-hidden">
              <div className="overflow-x-auto">
                <Table className="min-w-[850px]">
                  <TableHeader className="bg-zinc-50/80 border-b border-zinc-200">
                    <TableRow>
                      <TableHead className="w-10 text-center font-semibold text-zinc-600 text-[11px]">#</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Order #</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">Customer</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px] text-right">Grand Total</TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px]">6-Stage Milestone Progress</TableHead>
                      <TableHead className="text-right font-semibold text-zinc-600 text-[11px]">Action</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {milestonesLoading ? (
                      <TableRow>
                        <TableCell colSpan={6} className="text-center py-8 text-zinc-400 text-xs">
                          Loading order milestones...
                        </TableCell>
                      </TableRow>
                    ) : milestones.length === 0 ? (
                      <TableRow>
                        <TableCell colSpan={6} className="text-center py-8 text-zinc-400 text-xs">
                          No orders active in milestone pipeline.
                        </TableCell>
                      </TableRow>
                    ) : (
                      milestones.map((ord: OrderWithMilestones, idx: number) => (
                        <TableRow key={ord.id} className="hover:bg-zinc-50/60 transition-colors">
                          <TableCell className="text-center font-mono text-[11px] text-zinc-400 w-10">
                            {idx + 1}
                          </TableCell>
                          <TableCell className="font-mono text-xs font-semibold text-zinc-900">
                            {ord.order_number}
                          </TableCell>
                          <TableCell>
                            <div className="font-medium text-xs text-zinc-900">{ord.customer_name}</div>
                            <div className="text-[10px] text-zinc-400">Rep: {ord.rep_name || 'Self-raised'}</div>
                          </TableCell>
                          <TableCell className="font-mono font-semibold text-xs text-zinc-900 text-right">
                            ₹{Number(ord.grand_total).toLocaleString('en-IN')}
                          </TableCell>
                          <TableCell>
                            {/* 6-Stage Progress Bar */}
                            <div className="flex items-center space-x-1">
                              {ord.milestone_stages?.map((stg, sIdx) => (
                                <div key={stg.key} className="flex items-center">
                                  <div
                                    title={`${stg.title}: ${stg.completed ? 'Completed' : 'Pending'}`}
                                    className={`px-1.5 py-0.5 rounded text-[9px] font-mono flex items-center space-x-0.5 ${stg.completed
                                        ? 'bg-zinc-900 text-white font-semibold'
                                        : 'bg-zinc-100 text-zinc-400 border border-zinc-200'
                                      }`}
                                  >
                                    {stg.completed && <CheckCircle2 className="w-2.5 h-2.5 mr-0.5" />}
                                    <span>{stg.title}</span>
                                  </div>
                                  {sIdx < ord.milestone_stages.length - 1 && (
                                    <ChevronRight className="w-2.5 h-2.5 text-zinc-300 mx-0.5" />
                                  )}
                                </div>
                              ))}
                            </div>
                          </TableCell>
                          <TableCell className="text-right">
                            <Button
                              onClick={async () => {
                                try {
                                  const pkg = await salesApi.getAuditPackage(ord.id)
                                  setAuditPackageData(pkg)
                                  setIsAuditModalOpen(true)
                                } catch (err: any) {
                                  showFeedback('error', 'Failed to generate audit package')
                                }
                              }}
                              variant="outline"
                              size="sm"
                              className="h-6 text-[11px] px-2 text-zinc-800 border-zinc-300"
                            >
                              <Download className="w-3 h-3 mr-1" />
                              Audit ZIP
                            </Button>
                          </TableCell>
                        </TableRow>
                      ))
                    )}
                  </TableBody>
                </Table>
              </div>
            </Card>
          </div>
        )}

        {/* ===================================================================== */}
        {/* MODAL 1: NEW LEAD CAPTURE                                             */}
        {/* ===================================================================== */}
        <Dialog open={isLeadModalOpen} onClose={() => setIsLeadModalOpen(false)} maxWidth="max-w-lg">
          <div className="space-y-4">
            <DialogHeader>
              <DialogTitle className="text-sm font-bold text-zinc-900">
                Capture Commercial Lead
              </DialogTitle>
              <DialogDescription className="text-xs text-zinc-500">
                Low-friction lead entry with automatic deduplication (MK-01)
              </DialogDescription>
            </DialogHeader>

            <div className="space-y-3 text-xs">
              {/* Customer Master Selector & Quick Add */}
              <div className="p-2.5 rounded-lg bg-zinc-50 border border-zinc-200 space-y-1.5">
                <div className="flex items-center justify-between">
                  <label className="font-semibold text-zinc-800 text-[11px] flex items-center gap-1.5">
                    <Users className="w-3.5 h-3.5 text-zinc-600" />
                    <span>Customer Master (Existing Client)</span>
                  </label>
                  <Button
                    type="button"
                    size="sm"
                    variant="outline"
                    onClick={() => {
                      setQuickCustomerError(null)
                      setIsQuickAddCustomerOpen(true)
                    }}
                    className="h-6 px-2 text-[10px] font-medium text-zinc-800 border-zinc-300 hover:bg-zinc-100 flex items-center gap-1 cursor-pointer"
                  >
                    <Plus className="w-3 h-3 text-zinc-700" />
                    <span>+ Add New Customer</span>
                  </Button>
                </div>

                <select
                  value={selectedCustomerId}
                  onChange={(e) => handleCustomerSelect(e.target.value)}
                  className="h-8 w-full px-2 text-xs bg-white border border-zinc-200 rounded-md text-zinc-800 focus:outline-none focus:ring-1 focus:ring-zinc-950"
                >
                  <option value="">-- Select Existing Customer (or type manually below) --</option>
                  {customers.map((c) => (
                    <option key={c.id} value={String(c.id)}>
                      {c.name} {c.company ? `(${c.company})` : ''} {c.phone ? `— ${c.phone}` : ''}
                    </option>
                  ))}
                </select>
                <p className="text-[10px] text-zinc-500">
                  Selecting a customer automatically autofills company name, contact person, phone and email.
                </p>
              </div>

              <div>
                <label className="font-semibold text-zinc-700 block mb-1">Company / Organization Name *</label>
                <Input
                  value={newLead.company_name}
                  onChange={(e) => setNewLead({ ...newLead, company_name: e.target.value })}
                  placeholder="e.g. Larsen & Toubro Heavy Engineering"
                  className="h-8 text-xs"
                />
              </div>

              <div className="grid grid-cols-2 gap-2">
                <div>
                  <label className="font-semibold text-zinc-700 block mb-1">Contact Person *</label>
                  <Input
                    value={newLead.contact_person}
                    onChange={(e) => setNewLead({ ...newLead, contact_person: e.target.value })}
                    placeholder="e.g. Ramesh Patel"
                    className="h-8 text-xs"
                  />
                </div>
                <div>
                  <label className="font-semibold text-zinc-700 block mb-1">Phone Number *</label>
                  <Input
                    value={newLead.contact_phone}
                    maxLength={16}
                    onChange={(e) => setNewLead({ ...newLead, contact_phone: sanitizePhoneInput(e.target.value) })}
                    placeholder="e.g. +91 98200 12345"
                    className={`h-8 text-xs font-mono ${
                      newLead.contact_phone && getPhoneError(newLead.contact_phone) ? 'border-red-500 focus:ring-red-400' : ''
                    }`}
                  />
                  {newLead.contact_phone && getPhoneError(newLead.contact_phone) && (
                    <p className="text-[10px] text-red-600 mt-1 font-medium">{getPhoneError(newLead.contact_phone)}</p>
                  )}
                </div>
              </div>

              <div>
                <label className="font-semibold text-zinc-700 block mb-1">Email Address</label>
                <Input
                  type="email"
                  value={newLead.contact_email}
                  onChange={(e) => setNewLead({ ...newLead, contact_email: e.target.value })}
                  placeholder="e.g. contact@clientcompany.com"
                  className={`h-8 text-xs font-mono ${
                    newLead.contact_email && getEmailError(newLead.contact_email, false) ? 'border-red-500 focus:ring-red-400' : ''
                  }`}
                />
                {newLead.contact_email && getEmailError(newLead.contact_email, false) && (
                  <p className="text-[10px] text-red-600 mt-1 font-medium">{getEmailError(newLead.contact_email, false)}</p>
                )}
              </div>

              <div className="grid grid-cols-2 gap-2">
                <div>
                  <label className="font-semibold text-zinc-700 block mb-1">Sale Type *</label>
                  <select
                    value={newLead.sale_type}
                    onChange={(e) => setNewLead({ ...newLead, sale_type: e.target.value as any })}
                    className="h-8 w-full px-2 text-xs border border-zinc-200 rounded-md"
                  >
                    <option value="End_Customer">End Customer (Direct)</option>
                    <option value="SI_Bid">System Integrator (SI) Bid</option>
                    <option value="Distributor_Dealer">Distributor / Dealer</option>
                    <option value="Direct_Bid">Government / PSU Direct Bid</option>
                  </select>
                </div>
                <div>
                  <label className="font-semibold text-zinc-700 block mb-1">Estimated Value (₹)</label>
                  <Input
                    type="number"
                    value={newLead.estimated_value || ''}
                    placeholder="0"
                    onChange={(e) => setNewLead({ ...newLead, estimated_value: Number(e.target.value) })}
                    className="h-8 text-xs font-mono"
                  />
                </div>
              </div>

              <div>
                <label className="font-semibold text-zinc-700 block mb-1">Product Requirement</label>
                <Input
                  value={newLead.product_class}
                  onChange={(e) => setNewLead({ ...newLead, product_class: e.target.value })}
                  placeholder="e.g. AVO 60kVA 3-Phase Online UPS + Lithium Rack"
                  className="h-8 text-xs"
                />
              </div>
            </div>

            <DialogFooter className="flex justify-end gap-2 pt-2 border-t border-zinc-100">
              <Button
                variant="outline"
                size="sm"
                onClick={() => setIsLeadModalOpen(false)}
                className="h-8 text-xs"
              >
                Cancel
              </Button>
              <Button
                size="sm"
                onClick={() => createLeadMutation.mutate(newLead)}
                disabled={
                  !newLead.company_name ||
                  !isValidPhone(newLead.contact_phone) ||
                  (!!newLead.contact_email && !isValidEmail(newLead.contact_email)) ||
                  createLeadMutation.isPending
                }
                className="h-8 text-xs bg-zinc-900 text-white"
              >
                Save to Database
              </Button>
            </DialogFooter>
          </div>
        </Dialog>

        {/* ===================================================================== */}
        {/* MODAL: QUICK ADD CUSTOMER TO MASTERS                                   */}
        {/* ===================================================================== */}
        <Dialog
          open={isQuickAddCustomerOpen}
          onClose={() => setIsQuickAddCustomerOpen(false)}
          maxWidth="max-w-md"
          zIndex="z-[60]"
        >
          <div className="space-y-3.5">
            <DialogHeader>
              <DialogTitle className="text-sm font-bold text-zinc-900 flex items-center gap-2">
                <Users className="w-4 h-4 text-zinc-700" />
                <span>Add Customer to System Masters</span>
              </DialogTitle>
              <DialogDescription className="text-xs text-zinc-500">
                Immediately registers into Customer Masters and auto-populates the lead without page refresh.
              </DialogDescription>
            </DialogHeader>

            {quickCustomerError && (
              <div className="p-2 rounded text-xs bg-red-50 border border-red-200 text-red-700">
                {quickCustomerError}
              </div>
            )}

            <div className="space-y-2.5 text-xs">
              <div className="grid grid-cols-2 gap-2">
                <div>
                  <label className="font-semibold text-zinc-700 block mb-1">Customer Name *</label>
                  <Input
                    required
                    placeholder="e.g. Ramesh Patel"
                    value={quickCustomer.name}
                    onChange={(e) => setQuickCustomer({ ...quickCustomer, name: e.target.value })}
                    className="h-8 text-xs"
                  />
                </div>
                <div>
                  <label className="font-semibold text-zinc-700 block mb-1">Company / Organization</label>
                  <Input
                    placeholder="e.g. Larsen & Toubro"
                    value={quickCustomer.company}
                    onChange={(e) => setQuickCustomer({ ...quickCustomer, company: e.target.value })}
                    className="h-8 text-xs"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-2">
                <div>
                  <label className="font-semibold text-zinc-700 block mb-1">Email Address *</label>
                  <Input
                    required
                    type="email"
                    placeholder="e.g. ramesh@lt.com"
                    value={quickCustomer.email}
                    onChange={(e) => setQuickCustomer({ ...quickCustomer, email: e.target.value })}
                    className="h-8 text-xs font-mono"
                  />
                </div>
                <div>
                  <label className="font-semibold text-zinc-700 block mb-1">Phone Number</label>
                  <Input
                    placeholder="e.g. 9820012345"
                    value={quickCustomer.phone}
                    onChange={(e) => setQuickCustomer({ ...quickCustomer, phone: sanitizePhoneInput(e.target.value) })}
                    className="h-8 text-xs font-mono"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-2">
                <div>
                  <label className="font-semibold text-zinc-700 block mb-1">Tax ID / GSTIN</label>
                  <Input
                    placeholder="e.g. 27AABCU9603R1ZM"
                    value={quickCustomer.tax_number}
                    onChange={(e) => setQuickCustomer({ ...quickCustomer, tax_number: e.target.value.toUpperCase() })}
                    className="h-8 text-xs font-mono uppercase"
                  />
                </div>
                <div>
                  <label className="font-semibold text-zinc-700 block mb-1">Credit Limit (₹)</label>
                  <Input
                    type="number"
                    min="0"
                    step="1000"
                    placeholder="500000"
                    value={quickCustomer.credit_limit || ''}
                    onChange={(e) => setQuickCustomer({ ...quickCustomer, credit_limit: Number(e.target.value) })}
                    className="h-8 text-xs font-mono"
                  />
                </div>
              </div>

              <div>
                <label className="font-semibold text-zinc-700 block mb-1">Billing / Shipping Address</label>
                <textarea
                  rows={2}
                  placeholder="e.g. Powai Campus, Gate No. 5, Saki Vihar Road, Mumbai 400072"
                  value={quickCustomer.address}
                  onChange={(e) => setQuickCustomer({ ...quickCustomer, address: e.target.value })}
                  className="w-full text-xs p-2 rounded-md border border-zinc-200 bg-white focus:outline-none focus:ring-1 focus:ring-zinc-950 resize-none"
                />
              </div>
            </div>

            <DialogFooter className="flex justify-end gap-2 pt-2 border-t border-zinc-100">
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={() => setIsQuickAddCustomerOpen(false)}
                className="h-7.5 text-xs border-zinc-200 text-zinc-700"
              >
                Cancel
              </Button>
              <Button
                type="button"
                size="sm"
                disabled={
                  !quickCustomer.name.trim() ||
                  !quickCustomer.email.trim() ||
                  (quickCustomer.phone ? !isValidPhone(quickCustomer.phone) : false) ||
                  createQuickCustomerMutation.isPending
                }
                onClick={() => {
                  createQuickCustomerMutation.mutate({
                    name: quickCustomer.name.trim(),
                    company: quickCustomer.company.trim() || undefined,
                    email: quickCustomer.email.trim(),
                    phone: quickCustomer.phone.trim() || undefined,
                    tax_number: quickCustomer.tax_number.trim() || undefined,
                    credit_limit: quickCustomer.credit_limit || 0,
                    address: quickCustomer.address.trim() || undefined,
                  })
                }}
                className="h-7.5 text-xs bg-zinc-900 text-white"
              >
                {createQuickCustomerMutation.isPending ? 'Registering...' : 'Save to Masters & Select'}
              </Button>
            </DialogFooter>
          </div>
        </Dialog>

        {/* ===================================================================== */}
        {/* MODAL 2: FIELD VISIT GPS CHECK-IN                                    */}
        {/* ===================================================================== */}
        <Dialog open={isVisitModalOpen} onClose={() => setIsVisitModalOpen(false)} maxWidth="max-w-lg">
          <div className="space-y-4">
            <DialogHeader>
              <DialogTitle className="text-sm font-bold text-zinc-900">
                Log Customer Field Visit (MK-09)
              </DialogTitle>
              <DialogDescription className="text-xs text-zinc-500">
                Records geotagged check-in coordinates and discussion notes
              </DialogDescription>
            </DialogHeader>

            <div className="space-y-3 text-xs">
              <div>
                <label className="font-semibold text-zinc-700 block mb-1">Customer / Organization *</label>
                <Input
                  value={newVisit.customer_name}
                  onChange={(e) => setNewVisit({ ...newVisit, customer_name: e.target.value })}
                  placeholder="e.g. Reliance Jamnagar Refinery"
                  className="h-8 text-xs"
                />
              </div>

              <div className="grid grid-cols-2 gap-2">
                <div>
                  <label className="font-semibold text-zinc-700 block mb-1">Contact Person Met *</label>
                  <Input
                    value={newVisit.contact_person_met}
                    onChange={(e) => setNewVisit({ ...newVisit, contact_person_met: e.target.value })}
                    placeholder="e.g. S. K. Roy (DGM Electrical)"
                    className="h-8 text-xs"
                  />
                </div>
                <div>
                  <label className="font-semibold text-zinc-700 block mb-1">Location / Site Address</label>
                  <Input
                    value={newVisit.location_name}
                    onChange={(e) => setNewVisit({ ...newVisit, location_name: e.target.value })}
                    placeholder="e.g. Sector 18 Complex, Jamnagar"
                    className="h-8 text-xs"
                  />
                </div>
              </div>

              <div className="p-2.5 rounded bg-zinc-50 border border-zinc-200 space-y-1.5">
                <div className="flex items-center justify-between text-[11px] font-semibold text-zinc-800">
                  <span className="flex items-center">
                    <MapPin className="w-3 h-3 mr-1 text-zinc-500" /> Geotagged Location Coordinates
                  </span>
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => {
                      if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                          (pos) => {
                            setNewVisit((prev) => ({
                              ...prev,
                              latitude: Number(pos.coords.latitude.toFixed(6)),
                              longitude: Number(pos.coords.longitude.toFixed(6)),
                            }))
                            showFeedback('success', 'GPS coordinates acquired from your device.')
                          },
                          (err) => {
                            showFeedback('error', 'Could not get device GPS coordinates: ' + err.message)
                          }
                        )
                      }
                    }}
                    className="h-6 text-[10px] px-2 text-zinc-700 border-zinc-300"
                  >
                    <MapPin className="w-2.5 h-2.5 mr-1 text-zinc-500" />
                    Detect Live GPS
                  </Button>
                </div>
                <div className="grid grid-cols-2 gap-2 font-mono text-xs">
                  <div>
                    <label className="text-[10px] text-zinc-400 block">Latitude</label>
                    <Input
                      type="number"
                      step="any"
                      placeholder="0.000000"
                      value={newVisit.latitude || ''}
                      onChange={(e) => setNewVisit({ ...newVisit, latitude: Number(e.target.value) })}
                      className="h-7 text-xs bg-white font-mono"
                    />
                  </div>
                  <div>
                    <label className="text-[10px] text-zinc-400 block">Longitude</label>
                    <Input
                      type="number"
                      step="any"
                      placeholder="0.000000"
                      value={newVisit.longitude || ''}
                      onChange={(e) => setNewVisit({ ...newVisit, longitude: Number(e.target.value) })}
                      className="h-7 text-xs bg-white font-mono"
                    />
                  </div>
                </div>
              </div>

              <div>
                <label className="font-semibold text-zinc-700 block mb-1">Discussion Summary & Action Items</label>
                <textarea
                  rows={3}
                  value={newVisit.discussion_notes}
                  onChange={(e) => setNewVisit({ ...newVisit, discussion_notes: e.target.value })}
                  placeholder="Record load measurement results, battery space availability, and quotation timeline..."
                  className="w-full p-2 text-xs border border-zinc-200 rounded-md"
                />
              </div>
            </div>

            <DialogFooter className="flex justify-end gap-2 pt-2 border-t border-zinc-100">
              <Button
                variant="outline"
                size="sm"
                onClick={() => setIsVisitModalOpen(false)}
                className="h-8 text-xs"
              >
                Cancel
              </Button>
              <Button
                size="sm"
                onClick={() => createVisitMutation.mutate(newVisit)}
                disabled={!newVisit.customer_name || !newVisit.contact_person_met || createVisitMutation.isPending}
                className="h-8 text-xs bg-zinc-900 text-white"
              >
                Log Check-In
              </Button>
            </DialogFooter>
          </div>
        </Dialog>

        {/* Quotation creation is hosted on dedicated enterprise page at /sales/quotes/new */}

        {/* ===================================================================== */}
        {/* MODAL 4: L3 VP BELOW-MSP APPROVAL DIALOG                             */}
        {/* ===================================================================== */}
        {selectedQuoteForApproval && (
          <Dialog open={isApprovalModalOpen} onClose={() => setIsApprovalModalOpen(false)} maxWidth="max-w-md">
            <div className="space-y-4">
              <DialogHeader>
                <DialogTitle className="text-sm font-bold text-zinc-900">
                  L3 VP Pricing Authorization Gate
                </DialogTitle>
                <DialogDescription className="text-xs text-zinc-500">
                  Authoritative decision for Quotation {selectedQuoteForApproval.quote_number}
                </DialogDescription>
              </DialogHeader>

              <div className="p-3 rounded bg-zinc-50 border border-zinc-200 space-y-2 text-xs">
                <div className="flex justify-between">
                  <span className="text-zinc-500">Customer:</span>
                  <span className="font-semibold text-zinc-900">{selectedQuoteForApproval.customer_name}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-zinc-500">Quoted Amount:</span>
                  <span className="font-mono font-bold text-zinc-900">
                    ₹{Number(selectedQuoteForApproval.final_quote_amount).toLocaleString('en-IN')}
                  </span>
                </div>
                <div className="flex justify-between">
                  <span className="text-zinc-500">Floor MSP Required:</span>
                  <span className="font-mono font-bold text-zinc-900">
                    ₹{Number(selectedQuoteForApproval.total_msp_floor).toLocaleString('en-IN')}
                  </span>
                </div>
                <div className="flex justify-between border-t border-zinc-200 pt-1 text-zinc-900">
                  <span className="font-semibold">Shortfall Below MSP:</span>
                  <span className="font-mono font-bold">
                    -₹{Number(selectedQuoteForApproval.msp_shortfall_amount).toLocaleString('en-IN')}
                  </span>
                </div>
                {selectedQuoteForApproval.approval_remarks && (
                  <div className="pt-1 text-[11px] text-zinc-600 bg-white p-2 rounded border border-zinc-100">
                    <strong>Sales Rep Note:</strong> {selectedQuoteForApproval.approval_remarks}
                  </div>
                )}

                {selectedQuoteForApproval.items && selectedQuoteForApproval.items.length > 0 && (
                  <div className="border border-zinc-200 rounded p-2 bg-white space-y-1">
                    <div className="text-[10px] font-bold text-zinc-700">Line Items Breakdown:</div>
                    <div className="max-h-28 overflow-y-auto space-y-1">
                      {selectedQuoteForApproval.items.map((it: any, i: number) => (
                        <div key={i} className="flex justify-between items-center text-[10px] border-b border-zinc-100 pb-0.5">
                          <span className="text-zinc-800 font-medium">{it.quantity}x {it.product_name}</span>
                          <span className="font-mono text-zinc-700">₹{Number(it.total_price || (it.quoted_unit_price * it.quantity)).toLocaleString('en-IN')}</span>
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </div>

              <div>
                <label className="text-xs font-semibold text-zinc-700 block mb-1">VP Approval Remarks</label>
                <textarea
                  rows={2}
                  value={approvalRemarks}
                  onChange={(e) => setApprovalRemarks(e.target.value)}
                  placeholder="Record strategic authorization rationale..."
                  className="w-full p-2 text-xs border border-zinc-200 rounded-md"
                />
              </div>

              <DialogFooter className="flex justify-end gap-2 pt-2 border-t border-zinc-100">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setIsApprovalModalOpen(false)}
                  className="h-8 text-xs"
                >
                  Cancel
                </Button>
                {isL3VP && (
                  <>
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() =>
                        approveQuoteMutation.mutate({
                          id: selectedQuoteForApproval.id,
                          approved: false,
                          remarks: approvalRemarks,
                        })
                      }
                      className="h-8 text-xs text-zinc-600 border-zinc-300"
                    >
                      Reject
                    </Button>
                    <Button
                      size="sm"
                      onClick={() =>
                        approveQuoteMutation.mutate({
                          id: selectedQuoteForApproval.id,
                          approved: true,
                          remarks: approvalRemarks,
                        })
                      }
                      className="h-8 text-xs bg-zinc-900 text-white"
                    >
                      Authorize Below-MSP
                    </Button>
                  </>
                )}
              </DialogFooter>
            </div>
          </Dialog>
        )}

        {/* ===================================================================== */}
        {/* RIGHT SIDEBAR DRAWER: REGISTER TENDER & BANK GUARANTEE (50% WIDTH)   */}
        {/* ===================================================================== */}
        {isTenderModalOpen && (
          <div className="fixed inset-0 z-50 overflow-hidden">
            {/* Dark Backdrop */}
            <div
              onClick={() => setIsTenderModalOpen(false)}
              className="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity duration-300 animate-in fade-in"
            />

            <div className="fixed inset-y-0 right-0 max-w-full flex">
              <div className="w-screen w-full md:w-[60vw] lg:w-[50vw] max-w-none bg-white shadow-2xl border-l border-zinc-200 flex flex-col h-full transform transition-transform duration-300 ease-in-out animate-in slide-in-from-right">
                {/* Header */}
                <div className="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-zinc-200 bg-zinc-50/90 flex items-center justify-between">
                  <div>
                    <div className="flex items-center gap-2">
                      <span className="text-[10px] font-mono font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-zinc-900 text-white">
                        Directive §09.3 & TB-01/08
                      </span>
                      <span className="text-xs text-zinc-400 font-mono">Module 06 / 09</span>
                    </div>
                    <h2 className="text-base font-bold text-zinc-900 mt-1">
                      Register Tender & Bank Guarantee
                    </h2>
                    <p className="text-xs text-zinc-500">
                      Government & enterprise procurement tracking, direct vs dealer bids, and active EMD/PBGs
                    </p>
                  </div>

                  <button
                    type="button"
                    onClick={() => setIsTenderModalOpen(false)}
                    className="w-8 h-8 rounded-md hover:bg-zinc-200/70 flex items-center justify-center text-zinc-500 hover:text-zinc-900 transition-colors"
                    title="Close Drawer"
                  >
                    <X className="w-4 h-4" />
                  </button>
                </div>

                {/* Scrollable Form Body */}
                <div className="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 sm:space-y-5">
                  {/* Card 1: Tender Identification */}
                  <div className="border border-zinc-200 rounded-lg p-4 bg-white shadow-2xs space-y-4">
                    <div className="flex items-center gap-2 pb-2 border-b border-zinc-100">
                      <FileText className="w-4 h-4 text-zinc-600" />
                      <h3 className="text-xs font-bold text-zinc-900 uppercase tracking-wide">
                        1. Tender Identification & Authority Classification
                      </h3>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div>
                        <label className="text-xs font-semibold text-zinc-700 block mb-1">
                          Tender Reference Number *
                        </label>
                        <Input
                          value={newTender.tender_ref_no}
                          onChange={(e) => setNewTender({ ...newTender, tender_ref_no: e.target.value })}
                          placeholder="e.g. NTPC/DEL/2026/04"
                          className="h-8 text-xs font-mono bg-white"
                          required
                        />
                      </div>

                      <div>
                        <label className="text-xs font-semibold text-zinc-700 block mb-1">
                          Authority Classification *
                        </label>
                        <select
                          value={newTender.authority_type}
                          onChange={(e) => setNewTender({ ...newTender, authority_type: e.target.value })}
                          className="h-8 w-full px-2.5 text-xs border border-zinc-200 rounded-md bg-white text-zinc-800 font-medium"
                        >
                          <option value="PSU">Public Sector Undertaking (PSU)</option>
                          <option value="Central_Govt">Central Government</option>
                          <option value="State_Govt">State Government</option>
                          <option value="Defense">Defense Procurement</option>
                          <option value="Banking">Banking & Financial</option>
                        </select>
                      </div>
                    </div>

                    <div>
                      <label className="text-xs font-semibold text-zinc-700 block mb-1">
                        Issuing Authority / Client Name *
                      </label>
                      <Input
                        value={newTender.issuing_authority}
                        onChange={(e) => setNewTender({ ...newTender, issuing_authority: e.target.value })}
                        placeholder="e.g. National Thermal Power Corporation Limited"
                        className="h-8 text-xs bg-white"
                        required
                      />
                    </div>

                    <div>
                      <label className="text-xs font-semibold text-zinc-700 block mb-1">
                        Tender Scope / Procurement Title *
                      </label>
                      <Input
                        value={newTender.tender_title}
                        onChange={(e) => setNewTender({ ...newTender, tender_title: e.target.value })}
                        placeholder="e.g. Turnkey Supply & Commissioning of 3x 120kVA Online UPS"
                        className="h-8 text-xs bg-white"
                        required
                      />
                    </div>

                    <div>
                      <label className="text-xs font-semibold text-zinc-700 block mb-1">
                        Estimated Tender Value (₹)
                      </label>
                      <Input
                        type="number"
                        value={newTender.estimated_value || ''}
                        placeholder="0"
                        onChange={(e) => setNewTender({ ...newTender, estimated_value: Number(e.target.value) })}
                        className="h-8 text-xs font-mono bg-white"
                      />
                    </div>
                  </div>

                  {/* Card 2: Commercial Bidding Strategy */}
                  <div className="border border-zinc-200 rounded-lg p-4 bg-white shadow-2xs space-y-4">
                    <div className="flex items-center gap-2 pb-2 border-b border-zinc-100">
                      <Briefcase className="w-4 h-4 text-zinc-600" />
                      <h3 className="text-xs font-bold text-zinc-900 uppercase tracking-wide">
                        2. Commercial Bidding Strategy (Directive §09.3)
                      </h3>
                    </div>

                    <div>
                      <label className="text-xs font-semibold text-zinc-700 block mb-1">
                        Quote Channel Strategy
                      </label>
                      <select
                        value={newTender.quote_strategy}
                        onChange={(e) => setNewTender({ ...newTender, quote_strategy: e.target.value })}
                        className="h-8 w-full px-2.5 text-xs border border-zinc-200 rounded-md bg-white text-zinc-800 font-medium"
                      >
                        <option value="Direct_AVO">AVO Quotes Directly to Client</option>
                        <option value="Pass_To_Dealer">Pass to Registered Dealer / System Integrator (SI)</option>
                      </select>
                    </div>

                    {newTender.quote_strategy === 'Pass_To_Dealer' ? (
                      <div className="p-3.5 rounded-lg bg-zinc-50 border border-zinc-200 space-y-3">
                        <span className="text-[10px] font-mono font-semibold uppercase text-zinc-500">
                          Registered Dealer / SI Route Allocation
                        </span>
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                          <div>
                            <label className="text-xs font-semibold text-zinc-700 block mb-1">
                              Allocated Dealer / SI Name *
                            </label>
                            <Input
                              value={newTender.allocated_dealer_name}
                              onChange={(e) => setNewTender({ ...newTender, allocated_dealer_name: e.target.value })}
                              placeholder="e.g. Apex Power Systems & Solutions"
                              className="h-8 text-xs bg-white"
                            />
                          </div>
                          <div>
                            <label className="text-xs font-semibold text-zinc-700 block mb-1">
                              AVO Transfer / Quote Price (₹)
                            </label>
                            <Input
                              type="number"
                              value={newTender.avo_quoted_price || ''}
                              placeholder="0"
                              onChange={(e) => setNewTender({ ...newTender, avo_quoted_price: Number(e.target.value) })}
                              className="h-8 text-xs font-mono bg-white"
                            />
                          </div>
                        </div>
                      </div>
                    ) : (
                      <div>
                        <label className="text-xs font-semibold text-zinc-700 block mb-1">
                          AVO Direct Bid Quoted Price (₹)
                        </label>
                        <Input
                          type="number"
                          value={newTender.avo_quoted_price || ''}
                          placeholder="0"
                          onChange={(e) => setNewTender({ ...newTender, avo_quoted_price: Number(e.target.value) })}
                          className="h-8 text-xs font-mono bg-white"
                        />
                      </div>
                    )}
                  </div>

                  {/* Card 3: Earnest Money Deposit (EMD) Guarantee */}
                  <div className="border border-zinc-200 rounded-lg p-4 bg-white shadow-2xs space-y-4">
                    <div className="flex items-center gap-2 pb-2 border-b border-zinc-100">
                      <ShieldCheck className="w-4 h-4 text-zinc-600" />
                      <h3 className="text-xs font-bold text-zinc-900 uppercase tracking-wide">
                        3. Earnest Money Deposit (EMD) Guarantee Ledger
                      </h3>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                      <div>
                        <label className="text-xs font-semibold text-zinc-700 block mb-1">
                          Guarantee Type
                        </label>
                        <select
                          value={newTender.guarantee_type}
                          onChange={(e) => setNewTender({ ...newTender, guarantee_type: e.target.value })}
                          className="h-8 w-full px-2 text-xs border border-zinc-200 rounded-md bg-white text-zinc-800"
                        >
                          <option value="EMD_BG">Bank Guarantee (BG)</option>
                          <option value="EMD_FDR">Fixed Deposit (FDR)</option>
                          <option value="PBG">Performance BG (PBG)</option>
                        </select>
                      </div>

                      <div>
                        <label className="text-xs font-semibold text-zinc-700 block mb-1">
                          EMD Amount (₹)
                        </label>
                        <Input
                          type="number"
                          value={newTender.guarantee_amount || ''}
                          placeholder="0"
                          onChange={(e) => setNewTender({ ...newTender, guarantee_amount: Number(e.target.value) })}
                          className="h-8 text-xs bg-white font-mono"
                        />
                      </div>

                      <div>
                        <label className="text-xs font-semibold text-zinc-700 block mb-1">
                          Issuing Bank
                        </label>
                        <Input
                          value={newTender.issuing_bank}
                          placeholder="e.g. State Bank of India"
                          onChange={(e) => setNewTender({ ...newTender, issuing_bank: e.target.value })}
                          className="h-8 text-xs bg-white"
                        />
                      </div>
                    </div>
                  </div>
                </div>

                {/* Footer */}
                <div className="px-6 py-4 border-t border-zinc-200 bg-zinc-50 flex items-center justify-between">
                  <p className="text-xs text-zinc-500">
                    {!newTender.tender_ref_no || !newTender.issuing_authority
                      ? '* Tender Ref No & Issuing Authority are required.'
                      : 'All mandatory fields satisfied.'}
                  </p>

                  <div className="flex items-center gap-2">
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => setIsTenderModalOpen(false)}
                      className="h-8 text-xs text-zinc-700 bg-white"
                    >
                      Cancel
                    </Button>
                    <Button
                      size="sm"
                      onClick={() => createTenderMutation.mutate(newTender)}
                      disabled={!newTender.tender_ref_no || !newTender.issuing_authority || createTenderMutation.isPending}
                      className="h-8 text-xs bg-zinc-900 text-white hover:bg-zinc-800 font-medium shadow-2xs"
                    >
                      {createTenderMutation.isPending ? 'Saving...' : 'Save to Register'}
                    </Button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* ===================================================================== */}
        {/* MODAL 5B: UPDATE TENDER OUTCOME (PDF 09.3)                            */}
        {/* ===================================================================== */}
        <Dialog open={isOutcomeModalOpen} onClose={() => setIsOutcomeModalOpen(false)} maxWidth="max-w-md">
          <div className="space-y-4">
            <DialogHeader>
              <DialogTitle className="text-sm font-bold text-zinc-900">
                Track Tender Outcome
              </DialogTitle>
              <DialogDescription className="text-xs text-zinc-500">
                Tender: {selectedTenderForOutcome?.tender_ref_no} &bull; {selectedTenderForOutcome?.issuing_authority}
              </DialogDescription>
            </DialogHeader>

            <div className="space-y-3 text-xs">
              <div>
                <label className="font-semibold text-zinc-700 block mb-1">Final Outcome Status *</label>
                <select
                  value={outcomeForm.status}
                  onChange={(e) => setOutcomeForm({ ...outcomeForm, status: e.target.value })}
                  className="h-8 w-full px-2 text-xs border border-zinc-200 rounded-md bg-white"
                >
                  <option value="Awarded">Awarded (Won by AVO / Dealer)</option>
                  <option value="Lost">Lost to Competitor</option>
                  <option value="Technical_Eval">Under Technical / Financial Evaluation</option>
                  <option value="Disqualified">Disqualified on Technical Terms</option>
                  <option value="Dropped">Scrapped / Retendered / Dropped</option>
                </select>
              </div>

              <div>
                <label className="font-semibold text-zinc-700 block mb-1">L1 Winning Vendor Name</label>
                <Input
                  value={outcomeForm.l1_vendor_name}
                  onChange={(e) => setOutcomeForm({ ...outcomeForm, l1_vendor_name: e.target.value })}
                  placeholder="e.g. AVO Electro Power / Competitor Name"
                  className="h-8 text-xs bg-white"
                />
              </div>

              <div>
                <label className="font-semibold text-zinc-700 block mb-1">L1 Final Price (₹)</label>
                <Input
                  type="number"
                  value={outcomeForm.l1_price || ''}
                  onChange={(e) => setOutcomeForm({ ...outcomeForm, l1_price: Number(e.target.value) })}
                  placeholder="0"
                  className="h-8 text-xs font-mono bg-white"
                />
              </div>

              <div>
                <label className="font-semibold text-zinc-700 block mb-1">Outcome Remarks & Competitor Intelligence</label>
                <textarea
                  rows={3}
                  value={outcomeForm.outcome_remarks}
                  onChange={(e) => setOutcomeForm({ ...outcomeForm, outcome_remarks: e.target.value })}
                  placeholder="e.g. Lost on commercial bid by 2.4%; competitor offered 3-year battery warranty bundle."
                  className="w-full p-2 text-xs border border-zinc-200 rounded-md bg-white focus:outline-hidden"
                />
              </div>
            </div>

            <DialogFooter className="flex justify-end gap-2 pt-2 border-t border-zinc-100">
              <Button
                variant="outline"
                size="sm"
                onClick={() => setIsOutcomeModalOpen(false)}
                className="h-8 text-xs"
              >
                Cancel
              </Button>
              <Button
                size="sm"
                disabled={updateTenderOutcomeMutation.isPending}
                onClick={() => {
                  if (selectedTenderForOutcome) {
                    updateTenderOutcomeMutation.mutate({
                      id: selectedTenderForOutcome.id,
                      data: outcomeForm,
                    })
                  }
                }}
                className="h-8 text-xs bg-zinc-900 text-white"
              >
                {updateTenderOutcomeMutation.isPending ? 'Updating...' : 'Save Outcome'}
              </Button>
            </DialogFooter>
          </div>
        </Dialog>

        {/* ===================================================================== */}
        {/* MODAL 6: 1-CLICK AUDIT PACKAGE VIEWER                                 */}
        {/* ===================================================================== */}
        {auditPackageData && (
          <Dialog open={isAuditModalOpen} onClose={() => setIsAuditModalOpen(false)} maxWidth="max-w-md">
            <div className="space-y-4">
              <DialogHeader>
                <DialogTitle className="text-sm font-bold text-zinc-900">
                  Payment Audit Package (MK-12)
                </DialogTitle>
                <DialogDescription className="text-xs text-zinc-500">
                  Bundled compliance dossier for Order {auditPackageData.order_number}
                </DialogDescription>
              </DialogHeader>

              <div className="space-y-2 text-xs">
                <p className="text-zinc-600">
                  Ready to download 1-click ZIP package containing verified customer audit artifacts:
                </p>
                <div className="space-y-1.5">
                  {auditPackageData.documents?.map((doc: any, dIdx: number) => (
                    <div
                      key={dIdx}
                      className="p-2.5 rounded bg-zinc-50 border border-zinc-200 flex items-center justify-between"
                    >
                      <div className="flex items-center space-x-2">
                        <FileCheck2 className="w-4 h-4 text-zinc-900" />
                        <div>
                          <div className="font-medium text-zinc-900 text-xs">{doc.name}</div>
                          <div className="text-[10px] text-zinc-400">{doc.status} &bull; {doc.size_kb} KB</div>
                        </div>
                      </div>
                      <span className="text-[10px] px-1.5 py-0.5 rounded font-mono font-medium bg-zinc-200 text-zinc-800">
                        {doc.type}
                      </span>
                    </div>
                  ))}
                </div>
              </div>

              <DialogFooter className="flex justify-end gap-2 pt-2 border-t border-zinc-100">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setIsAuditModalOpen(false)}
                  className="h-8 text-xs"
                >
                  Close
                </Button>
                <Button
                  size="sm"
                  onClick={() => {
                    showFeedback('success', `Initiated download for ${auditPackageData.download_bundle_name}`)
                    setIsAuditModalOpen(false)
                  }}
                  className="h-8 text-xs bg-zinc-900 text-white"
                >
                  <Download className="w-3.5 h-3.5 mr-1" />
                  Download Bundle (.zip)
                </Button>
              </DialogFooter>
            </div>
          </Dialog>
        )}

        {/* ===================================================================== */}
        {/* MODAL 7: CONVERTED TO SALES ORDER SUCCESS CONFIRMATION                 */}
        {/* ===================================================================== */}
        {convertedSoModal && (
          <Dialog open={!!convertedSoModal} onClose={() => setConvertedSoModal(null)} maxWidth="max-w-md">
            <div className="space-y-4">
              <DialogHeader>
                <div className="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center mx-auto mb-2">
                  <CheckCircle2 className="w-6 h-6 text-emerald-600" />
                </div>
                <DialogTitle className="text-center text-sm font-bold text-zinc-900">
                  Quotation Converted to Sales Order!
                </DialogTitle>
                <DialogDescription className="text-center text-xs text-zinc-500">
                  Commercial terms, bill-of-materials and pricing have been formally transitioned into the operational order ledger.
                </DialogDescription>
              </DialogHeader>

              <div className="bg-zinc-50 border border-zinc-200 rounded-md p-3 space-y-2 text-xs">
                <div className="flex justify-between items-center pb-2 border-b border-zinc-200 font-mono">
                  <span className="text-zinc-500">Sales Order No:</span>
                  <span className="font-bold text-zinc-900 text-sm bg-white px-2 py-0.5 rounded border border-zinc-300">
                    {convertedSoModal.orderNumber}
                  </span>
                </div>
                <div className="flex justify-between items-center text-zinc-600">
                  <span className="text-zinc-500">Origin Quotation:</span>
                  <span className="font-mono font-medium text-zinc-900">{convertedSoModal.quoteNumber}</span>
                </div>
                {convertedSoModal.customerName && (
                  <div className="flex justify-between items-center text-zinc-600">
                    <span className="text-zinc-500">Customer:</span>
                    <span className="font-medium text-zinc-900">{convertedSoModal.customerName}</span>
                  </div>
                )}
                {convertedSoModal.grandTotal && (
                  <div className="flex justify-between items-center text-zinc-600">
                    <span className="text-zinc-500">Grand Total (incl. GST):</span>
                    <span className="font-mono font-bold text-zinc-900">
                      ₹{Number(convertedSoModal.grandTotal).toLocaleString('en-IN')}
                    </span>
                  </div>
                )}
                <div className="flex justify-between items-center text-zinc-600 pt-1">
                  <span className="text-zinc-500">Milestone Stage:</span>
                  <span className="text-[10px] font-mono px-2 py-0.5 rounded bg-blue-50 text-blue-800 border border-blue-200 font-medium">
                    Stage 1: SO Raised & Approved
                  </span>
                </div>
              </div>

              <DialogFooter className="flex flex-col sm:flex-row gap-2 pt-2 border-t border-zinc-100">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setConvertedSoModal(null)}
                  className="h-8 text-xs text-zinc-700 w-full sm:w-auto"
                >
                  Stay in Quotations
                </Button>
                <Button
                  size="sm"
                  onClick={() => {
                    setConvertedSoModal(null)
                    navigate('/sales/milestones')
                  }}
                  className="h-8 text-xs bg-zinc-900 text-white hover:bg-zinc-800 font-medium w-full sm:w-auto"
                >
                  Track in Milestones &rarr;
                </Button>
              </DialogFooter>
            </div>
          </Dialog>
        )}
      </div>
    )
  }
