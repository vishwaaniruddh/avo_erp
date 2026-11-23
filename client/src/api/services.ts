import { api } from './client'
import type {
  ApiResponse,
  ArchetypeRole,
  AuditLog,
  CityMaster,
  CountryMaster,
  CourierMaster,
  Customer,
  EmailSetting,
  EmailTemplate,
  Invoice,
  Order,
  OrderWithMilestones,
  PaginatedResult,
  PermissionMatrixItem,
  Product,
  RfpBid,
  RoleType,
  SalesLead,
  SalesQuotation,
  SalesVisit,
  ScrapRecord,
  SiRfpCluster,
  StateMaster,
  TenderGuarantee,
  TenderRecord,
  User,
  ZoneMaster,
} from '@/types'

export const authApi = {
  login: async (credentials: { email: string; password: string }) => {
    const res = (await api.post('/auth/login', credentials)) as unknown as ApiResponse<{
      token: string
      user: User
    }>
    return res.data!
  },
  me: async () => {
    const res = (await api.get('/auth/me')) as unknown as ApiResponse<User>
    return res.data!
  },
}

export const identityApi = {
  getRoles: async () => {
    const res = (await api.get('/identity/roles')) as unknown as ApiResponse<{
      role_types: {
        key: RoleType
        name: string
        screens: string[]
        description: string
      }[]
      archetypes: ArchetypeRole[]
      formula: string
    }>
    return res.data!
  },
  getUsers: async () => {
    const res = (await api.get('/users')) as unknown as ApiResponse<User[]>
    return res.data!
  },
  createUser: async (data: Partial<User> & { password?: string }) => {
    const res = (await api.post('/users', data)) as unknown as ApiResponse<User>
    return res.data!
  },
  updateUser: async (id: number, data: Partial<User> & { password?: string }) => {
    const res = (await api.put(`/users/${id}`, data)) as unknown as ApiResponse<User>
    return res.data!
  },
  deleteUser: async (id: number) => {
    const res = (await api.delete(`/users/${id}`)) as unknown as ApiResponse<null>
    return res
  },
  getMatrix: async () => {
    const res = (await api.get('/identity/matrix')) as unknown as ApiResponse<PermissionMatrixItem[]>
    return res.data!
  },
  updateMatrix: async (id: number, data: Partial<PermissionMatrixItem>) => {
    const res = (await api.put(`/identity/matrix/${id}`, data)) as unknown as ApiResponse<boolean>
    return res.data!
  },
  bulkUpdateRole: async (role: string, preset: 'all' | 'read_only' | 'standard' | 'none') => {
    const res = (await api.put(`/identity/matrix/role/${encodeURIComponent(role)}`, { preset })) as unknown as ApiResponse<boolean>
    return res.data!
  },
}

export const biddingApi = {
  getBids: async () => {
    const res = (await api.get('/rfp-bids')) as unknown as ApiResponse<{
      bids: RfpBid[]
      view_mode: 'executive_unified' | 'salesperson_isolated'
      chinese_wall_active: boolean
      total_bids?: number
      unique_rfp_count?: number
      deduplication_rate?: number
      salesperson_id?: number
      notice?: string
    }>
    return res.data!
  },
  submitBid: async (data: {
    rfp_number: string
    rfp_title?: string
    end_client?: string
    integrator_name: string
    quote_amount: number
    cost_amount?: number
  }) => {
    const res = (await api.post('/rfp-bids', data)) as unknown as ApiResponse<RfpBid>
    return res.data!
  },
}

export const scrapApi = {
  getLedger: async () => {
    const res = (await api.get('/scrap')) as unknown as ApiResponse<{
      records: ScrapRecord[]
      summary: {
        total_entries: number
        total_weight_kg: number
        total_recovery_value: number
        material_breakdown: { material: string; weight: number; value: number }[]
      }
      gate_status: string
    }>
    return res.data!
  },
  submitDischarge: async (data: {
    material_type: string
    weight_kg: number
    serial_number?: string
    defect_reason: string
    warehouse_branch?: string
  }) => {
    const res = (await api.post('/scrap', data)) as unknown as ApiResponse<{
      id: number
      status: string
      message: string
      timestamp: string
    }>
    return res.data!
  },
}

export const dashboardApi = {
  getMetrics: async () => {
    const res = (await api.get('/dashboard')) as unknown as ApiResponse<{
      kpis: {
        total_revenue: number
        pending_approvals: number
        low_stock_count: number
        total_orders: number
      }
      status_distribution: { status: string; count: number }[]
      category_breakdown: {
        category: string
        item_count: number
        total_stock: number
        inventory_value: number
      }[]
      recent_invoices: Invoice[]
      low_stock_items: Product[]
    }>
    return res.data!
  },
}

export const productApi = {
  getAll: async (params?: { page?: number; limit?: number; search?: string; all?: boolean }) => {
    const res = (await api.get('/products', { params })) as unknown as ApiResponse<
      PaginatedResult<Product> | Product[]
    >
    return res.data!
  },
  getById: async (id: number) => {
    const res = (await api.get(`/products/${id}`)) as unknown as ApiResponse<Product>
    return res.data!
  },
  create: async (data: Partial<Product>) => {
    const res = (await api.post('/products', data)) as unknown as ApiResponse<Product>
    return res.data!
  },
  update: async (id: number, data: Partial<Product>) => {
    const res = (await api.put(`/products/${id}`, data)) as unknown as ApiResponse<Product>
    return res.data!
  },
  adjustStock: async (id: number, delta: number, reason: string) => {
    const res = (await api.patch(`/products/${id}/stock`, {
      delta,
      reason,
    })) as unknown as ApiResponse<Product>
    return res.data!
  },
  delete: async (id: number) => {
    const res = (await api.delete(`/products/${id}`)) as unknown as ApiResponse<null>
    return res.data
  },
}

export const customerApi = {
  getAll: async (params?: { page?: number; limit?: number; search?: string; all?: boolean }) => {
    const res = (await api.get('/customers', { params })) as unknown as ApiResponse<
      PaginatedResult<Customer> | Customer[]
    >
    return res.data!
  },
  getById: async (id: number) => {
    const res = (await api.get(`/customers/${id}`)) as unknown as ApiResponse<Customer>
    return res.data!
  },
  create: async (data: Partial<Customer>) => {
    const res = (await api.post('/customers', data)) as unknown as ApiResponse<Customer>
    return res.data!
  },
}

export const orderApi = {
  getAll: async (params?: { page?: number; limit?: number; status?: string; search?: string }) => {
    const res = (await api.get('/orders', { params })) as unknown as ApiResponse<
      PaginatedResult<Order>
    >
    return res.data!
  },
  getById: async (id: number) => {
    const res = (await api.get(`/orders/${id}`)) as unknown as ApiResponse<Order>
    return res.data!
  },
  create: async (data: {
    customer_id: number
    items: { product_id: number; quantity: number; unit_price?: number }[]
    discount_amount?: number
    notes?: string
  }) => {
    const res = (await api.post('/orders', data)) as unknown as ApiResponse<Order>
    return res.data!
  },
  approve: async (id: number) => {
    const res = (await api.post(`/orders/${id}/approve`)) as unknown as ApiResponse<Order>
    return res.data!
  },
  reject: async (id: number, reason: string) => {
    const res = (await api.post(`/orders/${id}/reject`, { reason })) as unknown as ApiResponse<Order>
    return res.data!
  },
}

export const invoiceApi = {
  getAll: async (params?: { page?: number; limit?: number; status?: string; search?: string }) => {
    const res = (await api.get('/invoices', { params })) as unknown as ApiResponse<
      PaginatedResult<Invoice>
    >
    return res.data!
  },
  getById: async (id: number) => {
    const res = (await api.get(`/invoices/${id}`)) as unknown as ApiResponse<Invoice>
    return res.data!
  },
  generate: async (orderId: number, dueDays: number = 30) => {
    const res = (await api.post('/invoices', {
      order_id: orderId,
      due_days: dueDays,
    })) as unknown as ApiResponse<Invoice>
    return res.data!
  },
  pay: async (id: number) => {
    const res = (await api.post(`/invoices/${id}/pay`)) as unknown as ApiResponse<Invoice>
    return res.data!
  },
}

export const auditLogApi = {
  getAll: async (params?: {
    page?: number
    limit?: number
    entity_type?: string
    search?: string
  }) => {
    const res = (await api.get('/audit-logs', { params })) as unknown as ApiResponse<
      PaginatedResult<AuditLog>
    >
    return res.data!
  },
}

export const salesApi = {
  // Leads
  getLeads: async (params?: { status?: string; search?: string; sale_type?: string }) => {
    const res = (await api.get('/sales/leads', { params })) as unknown as ApiResponse<SalesLead[]>
    return res.data || []
  },
  createLead: async (data: Partial<SalesLead>) => {
    const res = (await api.post('/sales/leads', data)) as unknown as ApiResponse<SalesLead>
    return res.data!
  },
  updateLeadStatus: async (id: number, status: string, lostReason?: string) => {
    const res = (await api.put(`/sales/leads/${id}/status`, {
      status,
      lost_reason: lostReason,
    })) as unknown as ApiResponse<null>
    return res
  },

  // Field Visits
  getVisits: async () => {
    const res = (await api.get('/sales/visits')) as unknown as ApiResponse<SalesVisit[]>
    return res.data || []
  },
  createVisit: async (data: Partial<SalesVisit>) => {
    const res = (await api.post('/sales/visits', data)) as unknown as ApiResponse<SalesVisit>
    return res.data!
  },

  // Quotes
  getQuotes: async (params?: { approval_status?: string }) => {
    const res = (await api.get('/sales/quotes', { params })) as unknown as ApiResponse<SalesQuotation[]>
    return res.data || []
  },
  getQuoteById: async (id: number) => {
    const res = (await api.get(`/sales/quotes/${id}`)) as unknown as ApiResponse<SalesQuotation>
    return res.data!
  },
  createQuote: async (data: any) => {
    const res = (await api.post('/sales/quotes', data)) as unknown as ApiResponse<SalesQuotation>
    return res.data!
  },
  updateQuote: async (id: number, data: any) => {
    const res = (await api.put(`/sales/quotes/${id}`, data)) as unknown as ApiResponse<SalesQuotation>
    return res.data!
  },
  approveQuote: async (id: number, approved: boolean, remarks?: string) => {
    const res = (await api.post(`/sales/quotes/${id}/approve`, {
      approved,
      remarks,
    })) as unknown as ApiResponse<SalesQuotation>
    return res.data!
  },
  convertQuoteToSO: async (id: number) => {
    const res = (await api.post(`/sales/quotes/${id}/convert-so`)) as unknown as ApiResponse<{
      order_id: number
      order_number: string
      message: string
    }>
    return res.data!
  },

  // Tenders & Guarantees
  getTenders: async (params?: { status?: string; authority_type?: string }) => {
    const res = (await api.get('/sales/tenders', { params })) as unknown as ApiResponse<TenderRecord[]>
    return res.data || []
  },
  createTender: async (data: any) => {
    const res = (await api.post('/sales/tenders', data)) as unknown as ApiResponse<TenderRecord>
    return res.data!
  },
  updateTenderOutcome: async (id: number, data: any) => {
    const res = (await api.put(`/sales/tenders/${id}/outcome`, data)) as unknown as ApiResponse<null>
    return res
  },
  getGuarantees: async () => {
    const res = (await api.get('/sales/tenders/guarantees')) as unknown as ApiResponse<TenderGuarantee[]>
    return res.data || []
  },

  // SI Multi-Bid RFP Clusters
  getClusters: async () => {
    const res = (await api.get('/sales/rfp-clusters')) as unknown as ApiResponse<{
      clusters: SiRfpCluster[]
      analytics: {
        total_clusters: number
        deals_won: number
        clustered_hit_rate_pct: number
        total_raw_si_bids: number
        diluted_raw_hit_rate_pct: number
      }
    }>
    return res.data!
  },
  createCluster: async (data: any) => {
    const res = (await api.post('/sales/rfp-clusters', data)) as unknown as ApiResponse<any>
    return res.data!
  },

  // Milestones & Audit
  getOrderMilestones: async () => {
    const res = (await api.get('/sales/orders/milestones')) as unknown as ApiResponse<OrderWithMilestones[]>
    return res.data || []
  },
  getAuditPackage: async (orderId: number) => {
    const res = (await api.get(`/sales/orders/${orderId}/audit-package`)) as unknown as ApiResponse<any>
    return res.data!
  },
}

export const emailApi = {
  getSettings: async () => {
    const res = (await api.get('/email/settings')) as unknown as ApiResponse<EmailSetting[]>
    return res.data || []
  },
  createSetting: async (data: Partial<EmailSetting>) => {
    const res = (await api.post('/email/settings', data)) as unknown as ApiResponse<EmailSetting>
    return res.data!
  },
  updateSetting: async (id: number, data: Partial<EmailSetting>) => {
    const res = (await api.put(`/email/settings/${id}`, data)) as unknown as ApiResponse<EmailSetting>
    return res.data!
  },
  deleteSetting: async (id: number) => {
    const res = (await api.delete(`/email/settings/${id}`)) as unknown as ApiResponse<null>
    return res
  },
  testConnection: async (data: { setting_id?: number; host?: string; port?: number; encryption?: string; test_recipient?: string }) => {
    const res = (await api.post('/email/settings/test', data)) as unknown as ApiResponse<any>
    return res
  },
  getTemplates: async (params?: { category?: string; search?: string }) => {
    const res = (await api.get('/email/templates', { params })) as unknown as ApiResponse<EmailTemplate[]>
    return res.data || []
  },
  getTemplate: async (id: number) => {
    const res = (await api.get(`/email/templates/${id}`)) as unknown as ApiResponse<EmailTemplate>
    return res.data!
  },
  createTemplate: async (data: Partial<EmailTemplate>) => {
    const res = (await api.post('/email/templates', data)) as unknown as ApiResponse<EmailTemplate>
    return res.data!
  },
  updateTemplate: async (id: number, data: Partial<EmailTemplate>) => {
    const res = (await api.put(`/email/templates/${id}`, data)) as unknown as ApiResponse<EmailTemplate>
    return res.data!
  },
  deleteTemplate: async (id: number) => {
    const res = (await api.delete(`/email/templates/${id}`)) as unknown as ApiResponse<null>
    return res
  },
}

export const mastersApi = {
  // Countries
  getCountries: async (params?: { search?: string }) => {
    const res = (await api.get('/masters/countries', { params })) as unknown as ApiResponse<CountryMaster[]>
    return res.data || []
  },
  createCountry: async (data: Partial<CountryMaster>) => {
    const res = (await api.post('/masters/countries', data)) as unknown as ApiResponse<CountryMaster>
    return res.data!
  },
  updateCountry: async (id: number, data: Partial<CountryMaster>) => {
    const res = (await api.put(`/masters/countries/${id}`, data)) as unknown as ApiResponse<CountryMaster>
    return res.data!
  },
  deleteCountry: async (id: number) => {
    const res = (await api.delete(`/masters/countries/${id}`)) as unknown as ApiResponse<null>
    return res
  },

  // States
  getStates: async (params?: { search?: string; country_id?: number }) => {
    const res = (await api.get('/masters/states', { params })) as unknown as ApiResponse<StateMaster[]>
    return res.data || []
  },
  createState: async (data: Partial<StateMaster>) => {
    const res = (await api.post('/masters/states', data)) as unknown as ApiResponse<StateMaster>
    return res.data!
  },
  updateState: async (id: number, data: Partial<StateMaster>) => {
    const res = (await api.put(`/masters/states/${id}`, data)) as unknown as ApiResponse<StateMaster>
    return res.data!
  },
  deleteState: async (id: number) => {
    const res = (await api.delete(`/masters/states/${id}`)) as unknown as ApiResponse<null>
    return res
  },

  // Cities
  getCities: async (params?: { search?: string; state_id?: number }) => {
    const res = (await api.get('/masters/cities', { params })) as unknown as ApiResponse<CityMaster[]>
    return res.data || []
  },
  createCity: async (data: Partial<CityMaster>) => {
    const res = (await api.post('/masters/cities', data)) as unknown as ApiResponse<CityMaster>
    return res.data!
  },
  updateCity: async (id: number, data: Partial<CityMaster>) => {
    const res = (await api.put(`/masters/cities/${id}`, data)) as unknown as ApiResponse<CityMaster>
    return res.data!
  },
  deleteCity: async (id: number) => {
    const res = (await api.delete(`/masters/cities/${id}`)) as unknown as ApiResponse<null>
    return res
  },

  // Zones
  getZones: async (params?: { search?: string }) => {
    const res = (await api.get('/masters/zones', { params })) as unknown as ApiResponse<ZoneMaster[]>
    return res.data || []
  },
  createZone: async (data: Partial<ZoneMaster>) => {
    const res = (await api.post('/masters/zones', data)) as unknown as ApiResponse<ZoneMaster>
    return res.data!
  },
  updateZone: async (id: number, data: Partial<ZoneMaster>) => {
    const res = (await api.put(`/masters/zones/${id}`, data)) as unknown as ApiResponse<ZoneMaster>
    return res.data!
  },
  deleteZone: async (id: number) => {
    const res = (await api.delete(`/masters/zones/${id}`)) as unknown as ApiResponse<null>
    return res
  },

  // Couriers
  getCouriers: async (params?: { search?: string }) => {
    const res = (await api.get('/masters/couriers', { params })) as unknown as ApiResponse<CourierMaster[]>
    return res.data || []
  },
  createCourier: async (data: Partial<CourierMaster>) => {
    const res = (await api.post('/masters/couriers', data)) as unknown as ApiResponse<CourierMaster>
    return res.data!
  },
  updateCourier: async (id: number, data: Partial<CourierMaster>) => {
    const res = (await api.put(`/masters/couriers/${id}`, data)) as unknown as ApiResponse<CourierMaster>
    return res.data!
  },
  deleteCourier: async (id: number) => {
    const res = (await api.delete(`/masters/couriers/${id}`)) as unknown as ApiResponse<null>
    return res
  },
}
