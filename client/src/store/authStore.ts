import { create } from 'zustand'
import type { User, UserRole } from '@/types'

interface AuthState {
  user: User | null
  token: string | null
  isAuthenticated: boolean
  setAuth: (user: User, token: string) => void
  logout: () => void
  hasRole: (roles: UserRole | UserRole[]) => boolean
  canApproveOrders: () => boolean
  canGenerateInvoices: () => boolean
  canManageProducts: () => boolean
  canDeleteProducts: () => boolean
  canViewAuditLogs: () => boolean
  canAccessUserModule: () => boolean
  canAccessSuperadmin: () => boolean
  canAccessSales: () => boolean
  canCreateSalesQuotes: () => boolean
}

const savedUser = localStorage.getItem('avo_user')
const savedToken = localStorage.getItem('avo_token')

let initialUser: User | null = null
if (savedUser) {
  try {
    initialUser = JSON.parse(savedUser)
  } catch {
    initialUser = null
  }
}

export const useAuthStore = create<AuthState>((set, get) => ({
  user: initialUser,
  token: savedToken,
  isAuthenticated: !!savedToken && !!initialUser,

  setAuth: (user: User, token: string) => {
    localStorage.setItem('avo_user', JSON.stringify(user))
    localStorage.setItem('avo_token', token)
    set({ user, token, isAuthenticated: true })
  },

  logout: () => {
    localStorage.removeItem('avo_user')
    localStorage.removeItem('avo_token')
    set({ user: null, token: null, isAuthenticated: false })
  },

  hasRole: (roles: UserRole | UserRole[]) => {
    const { user } = get()
    if (!user) return false
    if (user.role === 'admin') return true
    const roleList = Array.isArray(roles) ? roles : [roles]
    return roleList.includes(user.role)
  },

  // Permissions helpers for UI visibility (React enforces visibility, PHP enforces access)
  canApproveOrders: () => {
    const { user } = get()
    return !!user && (user.role === 'admin' || user.role === 'manager' || user.level === 'L2' || user.level === 'L3')
  },

  canGenerateInvoices: () => {
    const { user } = get()
    return !!user && (['admin', 'manager', 'accountant'].includes(user.role) || user.role_type === 'Finance' || user.role_type === 'Executive')
  },

  canManageProducts: () => {
    const { user } = get()
    return !!user && (['admin', 'manager', 'inventory_clerk'].includes(user.role) || ['Operations/Warehouse', 'Executive'].includes(user.role_type))
  },

  canDeleteProducts: () => {
    const { user } = get()
    return !!user && (user.role === 'admin' || user.role_type === 'Admin')
  },

  canViewAuditLogs: () => {
    const { user } = get()
    return !!user && (user.role === 'admin' || user.role_type === 'Admin' || user.role_type === 'Executive' || user.level === 'L3')
  },

  // User Module (Users, Roles, Permissions) is strictly restricted to Admin, Executive, and L3 Seniority
  canAccessUserModule: () => {
    const { user } = get()
    if (!user) return false
    return (
      user.role === 'admin' ||
      user.role_type === 'Admin' ||
      user.role_type === 'Executive' ||
      user.level === 'L3'
    )
  },

  canAccessSuperadmin: () => {
    const { user } = get()
    if (!user) return false
    return (
      user.role === 'admin' ||
      user.role_type === 'Admin' ||
      user.role_type === 'Executive' ||
      user.level === 'L3'
    )
  },

  canAccessSales: () => {
    const { user } = get()
    if (!user) return false
    if (user.role === 'admin') return true
    return ['Sales', 'Executive', 'Admin', 'Finance', 'Project'].includes(user.role_type)
  },

  canCreateSalesQuotes: () => {
    const { user } = get()
    if (!user) return false
    if (user.role === 'admin') return true
    return ['Sales', 'Executive', 'Admin'].includes(user.role_type)
  },
}))
