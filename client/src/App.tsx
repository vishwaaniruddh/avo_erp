import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { DashboardLayout } from '@/layouts/DashboardLayout'
import { Login } from '@/pages/Login'
import { UsersPage } from '@/pages/UsersPage'
import { RolesPage } from '@/pages/RolesPage'
import { PermissionsPage } from '@/pages/PermissionsPage'
import { SalesPage } from '@/pages/SalesPage'
import { EmailSettingsPage } from '@/pages/EmailSettingsPage'
import { EmailTemplatesPage } from '@/pages/EmailTemplatesPage'
import { MastersPage } from '@/pages/MastersPage'
import { CreateQuotationPage } from '@/pages/CreateQuotationPage'
import { ViewQuotationPage } from '@/pages/ViewQuotationPage'
import { Dashboard } from '@/pages/Dashboard'
import { AccessDeniedPage } from '@/pages/AccessDeniedPage'
import { useAuthStore } from '@/store/authStore'

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnWindowFocus: false,
      retry: 1,
      staleTime: 5000,
    },
  },
})

// RBAC Guard for Superadmin Modules (Users, Roles, Permissions, Email, Masters)
function RequireSuperadmin({ children }: { children: React.ReactNode }) {
  const { canAccessSuperadmin } = useAuthStore()
  if (!canAccessSuperadmin()) {
    return (
      <AccessDeniedPage
        moduleName="Superadmin & System Governance"
        requiredRole="Executive Owner / System Admin (Level 3)"
      />
    )
  }
  return <>{children}</>
}

// RBAC Guard for Commercial & Bidding Modules (Sales, Leads, Quotes, Tenders)
function RequireSales({ children }: { children: React.ReactNode }) {
  const { canAccessSales } = useAuthStore()
  if (!canAccessSales()) {
    return (
      <AccessDeniedPage
        moduleName="Commercial, Quotations & Tender Platform"
        requiredRole="Sales / Executive / Commercial Project Team"
      />
    )
  }
  return <>{children}</>
}

// Intelligent Landing Redirect based on User Archetype
function AppIndexRedirect() {
  const { user, canAccessSuperadmin, canAccessSales } = useAuthStore()
  if (!user) return <Navigate to="/login" replace />
  if (canAccessSuperadmin()) return <Navigate to="/users" replace />
  if (canAccessSales()) return <Navigate to="/sales/leads" replace />
  return <Navigate to="/dashboard" replace />
}

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <Routes>
          <Route path="/login" element={<Login />} />
          <Route path="/" element={<DashboardLayout />}>
            <Route index element={<AppIndexRedirect />} />

            {/* Workspace & Analytics */}
            <Route path="dashboard" element={<Dashboard />} />

            {/* Superadmin - Users & Access */}
            <Route
              path="users"
              element={
                <RequireSuperadmin>
                  <UsersPage />
                </RequireSuperadmin>
              }
            />
            <Route
              path="roles"
              element={
                <RequireSuperadmin>
                  <RolesPage />
                </RequireSuperadmin>
              }
            />
            <Route
              path="permissions"
              element={
                <RequireSuperadmin>
                  <PermissionsPage />
                </RequireSuperadmin>
              }
            />

            {/* Superadmin - Email Credentials & Templates */}
            <Route path="email" element={<Navigate to="/email/settings" replace />} />
            <Route
              path="email/settings"
              element={
                <RequireSuperadmin>
                  <EmailSettingsPage />
                </RequireSuperadmin>
              }
            />
            <Route
              path="email/templates"
              element={
                <RequireSuperadmin>
                  <EmailTemplatesPage />
                </RequireSuperadmin>
              }
            />

            {/* Superadmin - Masters */}
            <Route path="masters" element={<Navigate to="/masters/customers" replace />} />
            <Route
              path="masters/:entity"
              element={
                <RequireSuperadmin>
                  <MastersPage />
                </RequireSuperadmin>
              }
            />

            {/* Commercial & Bidding (Strictly Guarded for Sales/Commercial Roles) */}
            <Route path="sales" element={<Navigate to="/sales/leads" replace />} />
            <Route
              path="sales/quotes/new"
              element={
                <RequireSales>
                  <CreateQuotationPage />
                </RequireSales>
              }
            />
            <Route
              path="sales/quotes/:id"
              element={
                <RequireSales>
                  <ViewQuotationPage />
                </RequireSales>
              }
            />
            <Route
              path="sales/quotes/:id/edit"
              element={
                <RequireSales>
                  <CreateQuotationPage />
                </RequireSales>
              }
            />
            <Route
              path="sales/:tab"
              element={
                <RequireSales>
                  <SalesPage />
                </RequireSales>
              }
            />

            {/* 403 Forbidden Error Landing */}
            <Route path="forbidden" element={<AccessDeniedPage />} />
          </Route>
          <Route path="*" element={<AppIndexRedirect />} />
        </Routes>
      </BrowserRouter>
    </QueryClientProvider>
  )
}
