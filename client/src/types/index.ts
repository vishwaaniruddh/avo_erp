export type UserRole = 'admin' | 'manager' | 'accountant' | 'sales_rep' | 'inventory_clerk'

export type RoleType =
  | 'Executive'
  | 'Finance'
  | 'Sales'
  | 'Service Field'
  | 'Service Desk'
  | 'Repair'
  | 'Operations/Warehouse'
  | 'Project'
  | 'Admin'
  | 'Customer'

export type ScopeType = 'own' | 'region' | 'branch' | 'global'
export type LevelType = 'L1' | 'L2' | 'L3'

export interface User {
  id: number
  name: string
  email: string
  role: UserRole
  role_type: RoleType
  scope: ScopeType
  level: LevelType
  designation: string
  region?: string
  branch?: string
  department?: string
  reporting_manager_id?: number | null
  reporting_manager_name?: string | null
  avatar?: string
  created_at?: string
}

export interface ArchetypeRole {
  title: string
  role_type: RoleType
  scope: ScopeType
  level: LevelType
  department: string
  sample_name: string
  email: string
}

export interface PermissionMatrixItem {
  id: number
  role_type: RoleType
  min_level: LevelType
  module_key: string
  module_name: string
  can_view: number
  can_create: number
  can_edit: number
  can_approve: number
  can_delete: number
}

export interface RfpBid {
  id: number
  rfp_number: string
  normalized_rfp: string
  rfp_title: string
  end_client: string
  integrator_name: string
  salesperson_id: number
  salesperson_name: string
  quote_amount: number
  cost_amount: number
  est_margin_pct: number
  status: string
  silent_link_id?: string | null
  created_at: string
}

export interface ScrapRecord {
  id: number
  material_type: string
  weight_kg: number
  serial_number?: string
  defect_reason: string
  warehouse_branch: string
  operator_id: number
  operator_name: string
  est_recovery_value: number
  created_at: string
}

export interface Product {
  id: number
  sku: string
  name: string
  description?: string
  category: string
  cost_price: number
  selling_price: number
  tax_rate: number
  stock_quantity: number
  reorder_level: number
  unit: string
  created_at: string
  updated_at: string
}

export interface Customer {
  id: number
  name: string
  company?: string
  email: string
  phone?: string
  tax_number?: string
  credit_limit: number
  outstanding_balance: number
  address?: string
  created_at: string
}

export interface OrderItem {
  id?: number
  order_id?: number
  product_id: number
  product_name: string
  sku: string
  quantity: number
  unit_price: number
  tax_rate: number
  tax_amount: number
  line_total: number
}

export type OrderStatus = 'draft' | 'pending_approval' | 'approved' | 'rejected' | 'invoiced'

export interface Order {
  id: number
  order_number: string
  customer_id: number
  customer_name: string
  status: OrderStatus
  subtotal: number
  tax_amount: number
  discount_amount: number
  grand_total: number
  created_by_id: number
  created_by_name: string
  approved_by_id?: number | null
  approved_by_name?: string | null
  notes?: string
  rejection_reason?: string
  created_at: string
  updated_at: string
  items?: OrderItem[]
}

export interface Invoice {
  id: number
  invoice_number: string
  order_id: number
  customer_id: number
  customer_name: string
  subtotal: number
  tax_amount: number
  total_amount: number
  status: 'unpaid' | 'paid' | 'overdue'
  due_date: string
  checksum: string
  issued_at: string
  paid_at?: string | null
  notes?: string
  order?: Order
  customer?: Customer
}

export interface AuditLog {
  id: number
  user_id?: number
  user_name: string
  user_role: string
  action: string
  entity_type: string
  entity_id: string
  old_values?: string | null
  new_values?: string | null
  ip_address: string
  created_at: string
}

export interface PaginationMeta {
  total: number
  page: number
  limit: number
  total_pages: number
}

export interface PaginatedResult<T> {
  data: T[]
  pagination: PaginationMeta
}

export interface ApiResponse<T = any> {
  success: boolean
  status: number
  message: string
  data?: T
  errors?: any
}

// =============================================================================
// MODULE 06 / 09: SALES, MARKETING, TENDER & BID PLATFORM
// =============================================================================

export interface SalesLead {
  id: number
  lead_number: string
  company_name: string
  contact_person: string
  contact_email?: string | null
  contact_phone: string
  city?: string
  state?: string
  region: string
  sale_type: 'Distributor_Dealer' | 'SI_Bid' | 'Direct_Bid' | 'End_Customer'
  product_class: string
  estimated_value: number
  source: string
  assigned_to_user_id: number
  salesperson_name?: string
  salesperson_email?: string
  status: 'New' | 'Contacted' | 'Qualified' | 'Quoted' | 'Negotiation' | 'Won' | 'Lost'
  lost_reason?: string | null
  quotes_count?: number
  quote_numbers?: string
  created_at: string
  updated_at: string
}

export interface SalesVisit {
  id: number
  lead_id?: number | null
  customer_name: string
  salesperson_id: number
  salesperson_name?: string
  visit_date: string
  check_in_time: string
  check_out_time?: string | null
  latitude?: number | null
  longitude?: number | null
  location_name?: string | null
  contact_person_met: string
  discussion_notes?: string
  next_follow_up?: string | null
  created_at: string
}

export interface QuotationItem {
  id?: number
  quotation_id?: number
  product_id?: number | null
  product_name: string
  product_sku?: string
  warranty_terms?: string
  extended_warranty?: string
  extended_warranty_price?: number
  quantity: number
  unit_cost_price: number
  unit_msp_floor: number
  quoted_unit_price: number
  total_price: number
}

export interface SalesQuotation {
  id: number
  quote_number: string
  quote_date?: string
  lead_id?: number | null
  lead_number?: string | null
  lead_company_name?: string | null
  customer_name: string
  contact_person?: string | null
  contact_phone?: string | null
  contact_email?: string | null
  sale_type: 'Distributor_Dealer' | 'SI_Bid' | 'Direct_Bid' | 'End_Customer'
  salesperson_id: number
  salesperson_name?: string
  version: string
  subtotal_amount: number
  discount_amount: number
  discount_input?: string
  installation_charges?: number
  amc_charges?: number
  total_cost_price: number
  total_msp_floor: number
  final_quote_amount: number
  gross_margin_percent: number
  is_below_msp: number
  msp_shortfall_amount: number
  approval_status: 'Draft' | 'Auto_Approved' | 'Pending_VP_Approval' | 'Approved' | 'Rejected'
  converted_order_id?: number | null
  converted_order_number?: string | null
  approved_by_user_id?: number | null
  approved_by_name?: string | null
  approval_remarks?: string | null
  valid_until?: string
  created_at: string
  items?: QuotationItem[]
}

export interface TenderGuarantee {
  id: number
  tender_id: number
  tender_ref_no?: string
  issuing_authority?: string
  tender_title?: string
  guarantee_type: 'EMD_FDR' | 'EMD_BG' | 'PBG'
  amount: number
  issuing_bank: string
  bg_number?: string
  effective_date: string
  expiry_date: string
  claim_expiry_date: string
  status: 'Active' | 'Expired' | 'Claimed' | 'Released'
  days_remaining?: number
  alert_status?: 'NORMAL' | 'CAUTION_30D' | 'WARNING_15D' | 'CRITICAL_7D' | 'EXPIRED'
  release_date?: string | null
  created_at: string
}

export interface TenderRecord {
  id: number
  tender_ref_no: string
  issuing_authority: string
  authority_type: 'PSU' | 'Central_Govt' | 'State_Govt' | 'Defense' | 'Banking' | 'Private'
  tender_title: string
  estimated_value: number
  published_date: string
  bid_submission_deadline: string
  quote_strategy: 'Direct_AVO' | 'Pass_To_Dealer'
  allocated_dealer_name?: string | null
  assigned_officer_id: number
  assigned_officer_name?: string
  status: 'Identified' | 'Under_Review' | 'Bid_Submitted' | 'Technical_Eval' | 'Financial_Eval' | 'Awarded' | 'Lost' | 'Dropped'
  l1_vendor_name?: string | null
  l1_price?: number | null
  avo_quoted_price?: number | null
  outcome_remarks?: string | null
  created_at: string
  guarantees?: TenderGuarantee[]
}

export interface SiRfpBid {
  id: number
  cluster_id: number
  si_company_name: string
  quotation_id?: number | null
  salesperson_name: string
  quoted_amount: number
  bid_status: 'Submitted' | 'Disqualified' | 'L1' | 'L2_Plus'
}

export interface SiRfpCluster {
  id: number
  cluster_code: string
  end_client_name: string
  project_scope: string
  est_total_deal_value: number
  cluster_status: 'Active_Bidding' | 'Deal_Won' | 'Deal_Lost'
  winning_si_name?: string | null
  created_at: string
  bids?: SiRfpBid[]
}

export interface OrderMilestoneStage {
  key: string
  title: string
  completed: boolean
  date?: string | null
}

export interface OrderWithMilestones {
  id: number
  order_number: string
  customer_name: string
  status: string
  subtotal: number
  tax_amount: number
  discount_amount: number
  grand_total: number
  invoice_number?: string | null
  invoice_status?: string | null
  rep_name?: string | null
  created_at: string
  updated_at: string
  milestone_stages: OrderMilestoneStage[]
}

// ==========================================
// EMAIL & CREDENTIALS MANAGEMENT
// ==========================================
export interface EmailSetting {
  id: number
  title: string
  smtp_host: string
  smtp_port: number
  smtp_username: string
  smtp_password?: string
  smtp_encryption: 'none' | 'ssl' | 'tls'
  from_name: string
  from_email: string
  reply_to?: string | null
  is_active: number | boolean
  created_at?: string
  updated_at?: string
}

export interface EmailTemplate {
  id: number
  template_key: string
  template_name: string
  subject: string
  body_html: string
  body_text?: string | null
  available_variables?: string | null
  category: string
  is_active: number | boolean
  created_at?: string
  updated_at?: string
}

// ==========================================
// SUPERADMIN MASTERS
// ==========================================
export interface CountryMaster {
  id: number
  name: string
  code: string
  phone_code?: string | null
  currency?: string | null
  is_active: number | boolean
  created_at?: string
  updated_at?: string
}

export interface StateMaster {
  id: number
  country_id: number
  name: string
  code?: string | null
  country_name?: string
  is_active: number | boolean
  created_at?: string
  updated_at?: string
}

export interface CityMaster {
  id: number
  state_id: number
  name: string
  pincode?: string | null
  state_name?: string
  country_name?: string
  is_active: number | boolean
  created_at?: string
  updated_at?: string
}

export interface ZoneMaster {
  id: number
  code: string
  name: string
  description?: string | null
  is_active: number | boolean
  created_at?: string
  updated_at?: string
}

export interface CourierMaster {
  id: number
  name: string
  tracking_url_template?: string | null
  contact_number?: string | null
  is_active: number | boolean
  created_at?: string
  updated_at?: string
}
