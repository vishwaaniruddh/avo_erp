import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import {
  ShieldCheck,
  Users,
  Grid3X3,
  Lock,
  Eye,
  AlertTriangle,
  Plus,
  Building,
  CheckCircle2,
  AlertCircle,
  TrendingUp,
  FileCheck2,
  Trash2,
  Layers,
  ArrowRight,
  ShieldAlert,
} from 'lucide-react'
import { identityApi, biddingApi, scrapApi, authApi } from '@/api/services'
import { useAuthStore } from '@/store/authStore'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Dialog, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { formatCurrency, formatDate } from '@/lib/utils'
import { isValidEmail, getEmailError } from '@/lib/validation'
import type { RoleType, ScopeType, LevelType, User } from '@/types'

export function Identity() {
  const queryClient = useQueryClient()
  const { user, setAuth } = useAuthStore()

  const [activeTab, setActiveTab] = useState<
    'users' | 'matrix' | 'bidding' | 'scrap' | 'traceability'
  >('users')

  // Modals state
  const [isUserModalOpen, setIsUserModalOpen] = useState(false)
  const [editingUser, setEditingUser] = useState<User | null>(null)
  const [isRfpModalOpen, setIsRfpModalOpen] = useState(false)
  const [isScrapModalOpen, setIsScrapModalOpen] = useState(false)

  // Feedback banner
  const [feedback, setFeedback] = useState<{ type: 'success' | 'error'; message: string } | null>(
    null
  )

  // User form inputs
  const [userName, setUserName] = useState('')
  const [userEmail, setUserEmail] = useState('')
  const [userDesignation, setUserDesignation] = useState('')
  const [userRoleType, setUserRoleType] = useState<RoleType>('Sales')
  const [userScope, setUserScope] = useState<ScopeType>('own')
  const [userLevel, setUserLevel] = useState<LevelType>('L1')
  const [userDepartment, setUserDepartment] = useState('Commercial Sales')
  const [userRegion, setUserRegion] = useState('North')
  const [userReportingId, setUserReportingId] = useState<number | undefined>(undefined)

  // RFP Form inputs
  const [rfpNumber, setRfpNumber] = useState('2026-NTPC-09')
  const [rfpTitle, setRfpTitle] = useState('50MW Solar Substation SCADA & UPS Deployment')
  const [endClient, setEndClient] = useState('NTPC Renewable Energy Corporation')
  const [integratorName, setIntegratorName] = useState('Integrator Gamma Systems')
  const [quoteAmount, setQuoteAmount] = useState(495000)

  // Scrap Form inputs
  const [scrapMaterial, setScrapMaterial] = useState('Copper Wire Coils')
  const [scrapWeight, setScrapWeight] = useState(50)
  const [scrapDefect, setScrapDefect] = useState('Coil winding insulation fracture')
  const [scrapSerial, setScrapSerial] = useState('')

  // Queries
  const { data: rolesData } = useQuery({
    queryKey: ['identity-roles'],
    queryFn: identityApi.getRoles,
  })

  const { data: usersList, isLoading: usersLoading } = useQuery({
    queryKey: ['identity-users'],
    queryFn: identityApi.getUsers,
  })

  const { data: matrixList, isLoading: matrixLoading } = useQuery({
    queryKey: ['identity-matrix'],
    queryFn: identityApi.getMatrix,
  })

  const { data: biddingData, isLoading: biddingLoading, refetch: refetchBids } = useQuery({
    queryKey: ['bidding-bids', user?.id],
    queryFn: biddingApi.getBids,
  })

  const {
    data: scrapData,
    isLoading: scrapLoading,
    error: scrapError,
    refetch: refetchScrap,
  } = useQuery({
    queryKey: ['scrap-ledger', user?.id],
    queryFn: scrapApi.getLedger,
    retry: false,
  })

  // Mutations
  const userSaveMutation = useMutation({
    mutationFn: (data: any) =>
      editingUser
        ? identityApi.updateUser(editingUser.id, data)
        : identityApi.createUser(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['identity-users'] })
      setIsUserModalOpen(false)
      setEditingUser(null)
      setFeedback({ type: 'success', message: 'User provisioned with 3-attribute tuple!' })
      setTimeout(() => setFeedback(null), 4000)
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.message || 'Failed to save user' })
    },
  })

  const matrixUpdateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: any }) =>
      identityApi.updateMatrix(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['identity-matrix'] })
      setFeedback({
        type: 'success',
        message: 'Permission matrix updated in DB without code changes (SS-36)!',
      })
      setTimeout(() => setFeedback(null), 3000)
    },
  })

  const rfpSubmitMutation = useMutation({
    mutationFn: biddingApi.submitBid,
    onSuccess: () => {
      refetchBids()
      setIsRfpModalOpen(false)
      setFeedback({
        type: 'success',
        message: 'RFP quote registered. Silent deduplication indexed in background (RA-05)!',
      })
      setTimeout(() => setFeedback(null), 4000)
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.message || 'Failed to submit quote' })
    },
  })

  const scrapSubmitMutation = useMutation({
    mutationFn: scrapApi.submitDischarge,
    onSuccess: (res) => {
      refetchScrap()
      setIsScrapModalOpen(false)
      setFeedback({
        type: 'success',
        message: res.message || 'Scrap discharge logged. Blind entry active (running total masked).',
      })
      setTimeout(() => setFeedback(null), 5000)
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.message || 'Failed to record scrap discharge' })
    },
  })

  // 1-Click Role Switcher for Archetypes
  const handleQuickSwitch = async (email: string) => {
    try {
      const auth = await authApi.login({ email, password: 'admin123' })
      setAuth(auth.user, auth.token)
      setFeedback({
        type: 'success',
        message: `Switched to role: ${auth.user.designation} (${auth.user.role_type} | ${auth.user.scope} | ${auth.user.level})`,
      })
      setTimeout(() => setFeedback(null), 4000)
    } catch (e: any) {
      setFeedback({ type: 'error', message: e.message || 'Could not switch role' })
    }
  }

  const openCreateUserModal = () => {
    setEditingUser(null)
    setUserName('')
    setUserEmail('')
    setUserDesignation('Senior Field Engineer')
    setUserRoleType('Service Field')
    setUserScope('own')
    setUserLevel('L1')
    setUserDepartment('Field Engineering')
    setUserRegion('North Zone')
    setUserReportingId(undefined)
    setIsUserModalOpen(true)
  }

  const openEditUserModal = (u: User) => {
    setEditingUser(u)
    setUserName(u.name)
    setUserEmail(u.email)
    setUserDesignation(u.designation)
    setUserRoleType(u.role_type)
    setUserScope(u.scope)
    setUserLevel(u.level)
    setUserDepartment(u.department || 'Operations')
    setUserRegion(u.region || 'North Zone')
    setUserReportingId(u.reporting_manager_id || undefined)
    setIsUserModalOpen(true)
  }

  const handleSaveUser = () => {
    userSaveMutation.mutate({
      name: userName,
      email: userEmail,
      designation: userDesignation,
      role_type: userRoleType,
      scope: userScope,
      level: userLevel,
      department: userDepartment,
      region: userRegion,
      reporting_manager_id: userReportingId,
    })
  }

  return (
    <div className="space-y-6 max-w-7xl mx-auto">
      {/* Hero Section from sarsspl.com/avo/identity.html */}
      <div className="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs">
        <div className="flex flex-wrap gap-2 items-center mb-3">
          <Badge variant="default" className="bg-slate-900 text-white">
            Release 1 Scope
          </Badge>
          <Badge variant="secondary" className="bg-slate-100 text-slate-700">
            Shared Service 6 (SS6)
          </Badge>
          <Badge variant="success" className="bg-emerald-50 text-emerald-700 border-emerald-200">
            16 / 16 Reqs Tracked
          </Badge>
          <Badge variant="outline" className="border-slate-300 text-slate-600">
            Light Theme · Shadcn UI
          </Badge>
        </div>

        <h1 className="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
          Identity, Roles & Access Control (SS6)
        </h1>
        <p className="text-sm text-slate-500 max-w-4xl mt-2 leading-relaxed">
          Enterprise authorization framework for AVO Ops Platform. Powered by a decoupled 3-attribute
          tuple, strict Chinese-wall bidding confidentiality, scrap-entry blind isolation, and
          zero-trust API enforcement.
        </p>

        {/* Decoupled Access Formula Callout */}
        <div className="mt-5 p-4 bg-slate-50 border border-dashed border-slate-300 rounded-xl flex flex-wrap items-center justify-between gap-4">
          <div className="flex items-center space-x-2 text-sm font-semibold text-slate-800">
            <span>Decoupled Access Formula:</span>
            <code className="bg-white px-3 py-1 rounded border border-slate-200 text-blue-600 font-mono text-xs font-bold">
              User Access = Role Type + Scope + Level
            </code>
          </div>
          <span className="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
            Replaces Rigid Job Titles (SS-23)
          </span>
        </div>

        {/* 4 Core Metrics */}
        <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-5 pt-5 border-t border-slate-100">
          <div className="p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
            <div className="text-2xl font-bold text-slate-900">10</div>
            <div className="text-[11px] font-semibold text-slate-500 uppercase mt-0.5 tracking-wider">
              Core Role Archetypes
            </div>
          </div>
          <div className="p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
            <div className="text-2xl font-bold text-blue-600">Strict</div>
            <div className="text-[11px] font-semibold text-slate-500 uppercase mt-0.5 tracking-wider">
              Chinese-Wall Isolation
            </div>
          </div>
          <div className="p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
            <div className="text-2xl font-bold text-amber-600">Blind</div>
            <div className="text-[11px] font-semibold text-slate-500 uppercase mt-0.5 tracking-wider">
              Scrap Entry Gate
            </div>
          </div>
          <div className="p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
            <div className="text-2xl font-bold text-emerald-600">Zero-Trust</div>
            <div className="text-[11px] font-semibold text-slate-500 uppercase mt-0.5 tracking-wider">
              API-Layer Enforced
            </div>
          </div>
        </div>
      </div>

      {/* Feedback Banner */}
      {feedback && (
        <div
          className={`p-3.5 rounded-xl text-xs flex items-center shadow-xs ${
            feedback.type === 'success'
              ? 'bg-emerald-50 text-emerald-900 border border-emerald-200'
              : 'bg-rose-50 text-rose-900 border border-rose-200'
          }`}
        >
          {feedback.type === 'success' ? (
            <CheckCircle2 className="w-4 h-4 mr-2 shrink-0 text-emerald-600" />
          ) : (
            <AlertCircle className="w-4 h-4 mr-2 shrink-0 text-rose-600" />
          )}
          <span className="font-medium">{feedback.message}</span>
        </div>
      )}

      {/* Segmented Shadcn Tabs */}
      <div className="flex border border-slate-200 rounded-xl p-1 bg-slate-100 overflow-x-auto gap-1">
        {[
          { id: 'users', label: '1. 3-Tuple User Directory & Provisioning', icon: Users },
          { id: 'matrix', label: '2. Configurable Permission Matrix', icon: Grid3X3 },
          { id: 'bidding', label: '3. Chinese-Wall Bidding & RFP Deduplication', icon: Lock },
          { id: 'scrap', label: '4. Warehouse Scrap Blind-Entry Gate', icon: Eye },
          { id: 'traceability', label: '5. Requirements Traceability (16)', icon: FileCheck2 },
        ].map((tab) => {
          const Icon = tab.icon
          const isActive = activeTab === tab.id
          return (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id as any)}
              className={`flex items-center px-4 py-2 rounded-lg text-xs font-semibold whitespace-nowrap transition-all cursor-pointer ${
                isActive
                  ? 'bg-white text-slate-900 shadow-xs font-bold'
                  : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60'
              }`}
            >
              <Icon className="w-3.5 h-3.5 mr-2 shrink-0" />
              {tab.label}
            </button>
          )
        })}
      </div>

      {/* =========================================================================
          TAB 1: 3-TUPLE USER DIRECTORY & PROVISIONING (SS-23, SS-33, SS-35)
          ========================================================================= */}
      {activeTab === 'users' && (
        <div className="space-y-6">
          {/* Quick 14-Archetype Switcher Banner */}
          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="text-base flex items-center justify-between">
                <span className="flex items-center">
                  <Layers className="w-4 h-4 mr-2 text-blue-600" />
                  14 Named Organizational Archetype Templates (SS-35)
                </span>
                <span className="text-xs text-slate-400 font-normal">
                  Click any role to simulate identity immediately
                </span>
              </CardTitle>
              <CardDescription>
                Every named organizational title is mapped as a decoupled combination of Role Type,
                Scope, and Level (Zero-Code Setup).
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                {rolesData?.archetypes.map((arch) => {
                  const isCurrent = user?.email === arch.email
                  return (
                    <button
                      key={arch.email}
                      onClick={() => handleQuickSwitch(arch.email)}
                      className={`text-left p-2.5 rounded-lg border text-xs transition-all cursor-pointer ${
                        isCurrent
                          ? 'border-blue-600 bg-blue-50/80 shadow-xs ring-1 ring-blue-600'
                          : 'border-slate-200 bg-white hover:bg-slate-50'
                      }`}
                    >
                      <div className="font-bold text-slate-900 truncate">{arch.title}</div>
                      <div className="text-[11px] text-slate-500 flex items-center gap-1.5 mt-1">
                        <span className="px-1.5 py-0.2 rounded bg-slate-100 font-medium text-slate-700">
                          {arch.role_type}
                        </span>
                        <span>•</span>
                        <span className="capitalize">{arch.scope}</span>
                        <span>•</span>
                        <span className="font-semibold text-blue-700">{arch.level}</span>
                      </div>
                    </button>
                  )
                })}
              </div>
            </CardContent>
          </Card>

          {/* User Directory Table */}
          <Card>
            <CardHeader className="flex flex-row items-center justify-between">
              <div>
                <CardTitle className="text-base">Active User Directory (SS-33)</CardTitle>
                <CardDescription>
                  Decoupled authorization model: Functional Screen Access (Role Type) + Data
                  Boundary (Scope) + Signing Authority (Level)
                </CardDescription>
              </div>
              <Button onClick={openCreateUserModal}>
                <Plus className="w-4 h-4 mr-2" />
                Provision User (SS-36)
              </Button>
            </CardHeader>
            <CardContent className="p-0">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>User & Designation</TableHead>
                    <TableHead>1. Role Type (Screens)</TableHead>
                    <TableHead>2. Scope (Data Boundary)</TableHead>
                    <TableHead>3. Level (Authority)</TableHead>
                    <TableHead>Department / Region</TableHead>
                    <TableHead>Reporting Manager</TableHead>
                    <TableHead className="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {usersLoading ? (
                    <TableRow>
                      <TableCell colSpan={7} className="h-32 text-center text-slate-500">
                        Loading user directory...
                      </TableCell>
                    </TableRow>
                  ) : (
                    usersList?.map((u) => (
                      <TableRow key={u.id}>
                        <TableCell>
                          <div className="font-semibold text-slate-900">{u.name}</div>
                          <div className="text-xs text-slate-500 font-medium">{u.designation}</div>
                          <div className="text-[11px] text-slate-400 font-mono">{u.email}</div>
                        </TableCell>
                        <TableCell>
                          <Badge variant="secondary" className="font-semibold">
                            {u.role_type}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          <span
                            className={`text-xs px-2 py-0.5 rounded font-mono font-medium ${
                              u.scope === 'global'
                                ? 'bg-purple-100 text-purple-800'
                                : u.scope === 'region'
                                ? 'bg-blue-100 text-blue-800'
                                : u.scope === 'branch'
                                ? 'bg-amber-100 text-amber-800'
                                : 'bg-slate-100 text-slate-800'
                            }`}
                          >
                            {u.scope.toUpperCase()}
                          </span>
                        </TableCell>
                        <TableCell>
                          <span
                            className={`text-xs px-2 py-0.5 rounded font-bold ${
                              u.level === 'L3'
                                ? 'bg-rose-100 text-rose-800'
                                : u.level === 'L2'
                                ? 'bg-amber-100 text-amber-800'
                                : 'bg-emerald-100 text-emerald-800'
                            }`}
                          >
                            {u.level}
                          </span>
                        </TableCell>
                        <TableCell className="text-xs">
                          <div className="font-medium text-slate-800">{u.department}</div>
                          <div className="text-slate-400">{u.region}</div>
                        </TableCell>
                        <TableCell className="text-xs font-medium text-slate-700">
                          {u.reporting_manager_name ? (
                            <span className="flex items-center">
                              <ArrowRight className="w-3 h-3 mr-1 text-slate-400" />
                              {u.reporting_manager_name}
                            </span>
                          ) : (
                            <span className="text-slate-400 italic">Self / Board</span>
                          )}
                        </TableCell>
                        <TableCell className="text-right">
                          <Button variant="outline" size="sm" onClick={() => openEditUserModal(u)}>
                            Edit 3D Tuple
                          </Button>
                        </TableCell>
                      </TableRow>
                    ))
                  )}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </div>
      )}

      {/* =========================================================================
          TAB 2: CONFIGURABLE PERMISSION MATRIX (SS-25, RA-09)
          ========================================================================= */}
      {activeTab === 'matrix' && (
        <Card>
          <CardHeader>
            <CardTitle className="text-base flex items-center justify-between">
              <span>Configurable Permission Matrix (RA-09, SS-25)</span>
              <span className="text-xs px-2.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">
                Zero-Code Provisioning
              </span>
            </CardTitle>
            <CardDescription>
              Role screen sets and action authorization are delivered as a database-driven editable
              matrix without requiring code changes.
            </CardDescription>
          </CardHeader>
          <CardContent className="p-0">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Role Type</TableHead>
                  <TableHead>Target Module / Screen</TableHead>
                  <TableHead className="text-center">View (Screen)</TableHead>
                  <TableHead className="text-center">Create</TableHead>
                  <TableHead className="text-center">Edit</TableHead>
                  <TableHead className="text-center">Approve</TableHead>
                  <TableHead className="text-center">Delete</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {matrixLoading ? (
                  <TableRow>
                    <TableCell colSpan={7} className="h-32 text-center text-slate-500">
                      Loading permission matrix...
                    </TableCell>
                  </TableRow>
                ) : (
                  matrixList?.map((row) => (
                    <TableRow key={row.id}>
                      <TableCell className="font-bold text-xs text-slate-900">
                        {row.role_type}
                      </TableCell>
                      <TableCell className="text-xs font-medium text-slate-800">
                        {row.module_name}
                      </TableCell>
                      <TableCell className="text-center">
                        <input
                          type="checkbox"
                          checked={row.can_view === 1}
                          onChange={(e) =>
                            matrixUpdateMutation.mutate({
                              id: row.id,
                              data: { ...row, can_view: e.target.checked ? 1 : 0 },
                            })
                          }
                          className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                        />
                      </TableCell>
                      <TableCell className="text-center">
                        <input
                          type="checkbox"
                          checked={row.can_create === 1}
                          onChange={(e) =>
                            matrixUpdateMutation.mutate({
                              id: row.id,
                              data: { ...row, can_create: e.target.checked ? 1 : 0 },
                            })
                          }
                          className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                        />
                      </TableCell>
                      <TableCell className="text-center">
                        <input
                          type="checkbox"
                          checked={row.can_edit === 1}
                          onChange={(e) =>
                            matrixUpdateMutation.mutate({
                              id: row.id,
                              data: { ...row, can_edit: e.target.checked ? 1 : 0 },
                            })
                          }
                          className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                        />
                      </TableCell>
                      <TableCell className="text-center">
                        <input
                          type="checkbox"
                          checked={row.can_approve === 1}
                          onChange={(e) =>
                            matrixUpdateMutation.mutate({
                              id: row.id,
                              data: { ...row, can_approve: e.target.checked ? 1 : 0 },
                            })
                          }
                          className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                        />
                      </TableCell>
                      <TableCell className="text-center">
                        <input
                          type="checkbox"
                          checked={row.can_delete === 1}
                          onChange={(e) =>
                            matrixUpdateMutation.mutate({
                              id: row.id,
                              data: { ...row, can_delete: e.target.checked ? 1 : 0 },
                            })
                          }
                          className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                        />
                      </TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      )}

      {/* =========================================================================
          TAB 3: CHINESE-WALL BIDDING & SILENT RFP DEDUPLICATION (RA-04, RA-05)
          ========================================================================= */}
      {activeTab === 'bidding' && (
        <div className="space-y-6">
          <Card className="border-blue-200 bg-blue-50/40">
            <CardHeader className="pb-3">
              <div className="flex items-center justify-between">
                <CardTitle className="text-base text-blue-900 flex items-center">
                  <Lock className="w-4 h-4 mr-2 text-blue-600" />
                  Chinese-Wall Bidding Isolation (RA-04, RA-05)
                </CardTitle>
                <Badge
                  variant={
                    biddingData?.view_mode === 'executive_unified' ? 'default' : 'secondary'
                  }
                >
                  {biddingData?.view_mode === 'executive_unified'
                    ? 'OWNER / EXECUTIVE VIEW (ALL QUOTES)'
                    : 'SALESPERSON ISOLATED VIEW'}
                </Badge>
              </div>
              <CardDescription className="text-blue-800/80">
                Guarantees commercial secrecy between internal salespeople quoting competing System
                Integrators for the same RFP. Lateral quote visibility is strictly sealed.
              </CardDescription>
            </CardHeader>
            <CardContent>
              {biddingData?.view_mode === 'executive_unified' ? (
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3 bg-white border border-blue-200 rounded-xl text-xs">
                  <div>
                    <span className="text-slate-500 font-semibold">Total Quotes Submitted:</span>
                    <div className="text-lg font-bold text-slate-900">
                      {biddingData.total_bids} Quotes
                    </div>
                  </div>
                  <div>
                    <span className="text-slate-500 font-semibold">Unique Tender Opportunities:</span>
                    <div className="text-lg font-bold text-blue-700">
                      {biddingData.unique_rfp_count} Tenders
                    </div>
                  </div>
                  <div>
                    <span className="text-slate-500 font-semibold">Deduplicated Hit-Rate Base:</span>
                    <div className="text-lg font-bold text-emerald-700">
                      Counts ONCE in Hit-Rate (RA-05)
                    </div>
                  </div>
                </div>
              ) : (
                <div className="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-center">
                  <AlertTriangle className="w-4 h-4 mr-2 shrink-0 text-amber-600" />
                  <span>
                    You are logged in as{' '}
                    <span className="font-bold">{user?.name}</span> (Sales L1). You can only view
                    quotes authored by yourself. Competing internal quotes are completely masked.
                  </span>
                </div>
              )}
            </CardContent>
          </Card>

          {/* Quotes Table */}
          <Card>
            <CardHeader className="flex flex-row items-center justify-between">
              <div>
                <CardTitle className="text-base">Tender Quotation Register</CardTitle>
                <CardDescription>
                  Normalized RFP Hash indexing creates silent links without alerting competing sales
                  representatives
                </CardDescription>
              </div>
              <Button onClick={() => setIsRfpModalOpen(true)}>
                <Plus className="w-4 h-4 mr-2" />
                Submit RFP Quote
              </Button>
            </CardHeader>
            <CardContent className="p-0">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>RFP Ref #</TableHead>
                    <TableHead>Tender Title & Client</TableHead>
                    <TableHead>System Integrator</TableHead>
                    <TableHead>Quoted By</TableHead>
                    <TableHead>Quote Amount</TableHead>
                    <TableHead>Est Margin %</TableHead>
                    <TableHead>Chinese-Wall Link Status</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {biddingLoading ? (
                    <TableRow>
                      <TableCell colSpan={7} className="h-32 text-center text-slate-500">
                        Querying tender database...
                      </TableCell>
                    </TableRow>
                  ) : !biddingData?.bids || biddingData.bids.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={7} className="h-32 text-center text-slate-500">
                        No quotes registered under your identity scope.
                      </TableCell>
                    </TableRow>
                  ) : (
                    biddingData.bids.map((bid) => (
                      <TableRow key={bid.id}>
                        <TableCell className="font-mono text-xs font-bold text-blue-700">
                          {bid.rfp_number}
                        </TableCell>
                        <TableCell>
                          <div className="font-semibold text-slate-900">{bid.rfp_title}</div>
                          <div className="text-xs text-slate-500">{bid.end_client}</div>
                        </TableCell>
                        <TableCell className="font-medium text-slate-800">
                          {bid.integrator_name}
                        </TableCell>
                        <TableCell className="text-xs text-slate-600">
                          {bid.salesperson_name}
                        </TableCell>
                        <TableCell className="font-bold text-slate-900">
                          {formatCurrency(bid.quote_amount)}
                        </TableCell>
                        <TableCell>
                          <Badge variant="success">{bid.est_margin_pct}% Margin</Badge>
                        </TableCell>
                        <TableCell>
                          {bid.silent_link_id ? (
                            biddingData.view_mode === 'executive_unified' ? (
                              <Badge variant="warning" className="font-mono text-[10px]">
                                SILENT LINK: {bid.silent_link_id}
                              </Badge>
                            ) : (
                              <Badge variant="outline" className="text-[10px]">
                                SEALED TENDER
                              </Badge>
                            )
                          ) : (
                            <Badge variant="secondary" className="text-[10px]">
                              STANDARD BID
                            </Badge>
                          )}
                        </TableCell>
                      </TableRow>
                    ))
                  )}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </div>
      )}

      {/* =========================================================================
          TAB 4: WAREHOUSE SCRAP BLIND-ENTRY GATE (RA-07, RA-08)
          ========================================================================= */}
      {activeTab === 'scrap' && (
        <div className="space-y-6">
          <Card className="border-amber-200 bg-amber-50/30">
            <CardHeader className="pb-3">
              <div className="flex items-center justify-between">
                <CardTitle className="text-base text-amber-900 flex items-center">
                  <Eye className="w-4 h-4 mr-2 text-amber-600" />
                  Warehouse Scrap Blind-Entry & Anti-Collusion Gate (RA-07, RA-08)
                </CardTitle>
                <Badge variant={scrapError ? 'destructive' : 'success'}>
                  {scrapError ? 'BLIND ENTRY ACTIVE (READ FORBIDDEN 403)' : 'EXECUTIVE AUDIT UNLOCKED'}
                </Badge>
              </div>
              <CardDescription className="text-amber-800/80">
                High-value salvage items (copper, tested lithium cells, PCBs) represent serious fraud
                risk. Warehouse staff can submit physical discharge entries, but past cumulative
                ledger totals are masked.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="flex flex-col sm:flex-row items-center justify-between gap-4">
                <p className="text-xs text-slate-600 leading-relaxed">
                  Current Session: <span className="font-bold text-slate-900">{user?.name}</span> (
                  {user?.designation}). Write-Permission: <span className="text-emerald-700 font-bold">Enabled</span>.
                  Cumulative Read-Permission:{' '}
                  {scrapError ? (
                    <span className="text-rose-700 font-bold">Blocked (RA-07 Zero-Trust)</span>
                  ) : (
                    <span className="text-emerald-700 font-bold">Authorized (RA-08 Executive)</span>
                  )}
                </p>
                <Button onClick={() => setIsScrapModalOpen(true)}>
                  <Plus className="w-4 h-4 mr-2" />
                  Log Scrap Discharge
                </Button>
              </div>
            </CardContent>
          </Card>

          {/* If Blind Entry Gate is hit (HTTP 403 Forbidden for Warehouse Operator) */}
          {scrapError ? (
            <Card className="border-rose-200 bg-rose-50/40">
              <CardContent className="p-8 text-center space-y-3">
                <div className="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                  <ShieldAlert className="w-6 h-6" />
                </div>
                <h3 className="text-base font-bold text-rose-900">
                  HTTP 403 Forbidden — Blind Entry Gate Enforced (RA-07)
                </h3>
                <p className="text-xs text-rose-700 max-w-lg mx-auto">
                  Warehouse operators may record daily scrap disposal entries, but cumulative
                  historical records and recovered dollar valuations are strictly masked to eliminate
                  inventory collusion.
                </p>
                <p className="text-xs text-slate-500">
                  To view the full scrap ledger, switch to <span className="font-semibold">CFO (Arthur Wellesley)</span> or <span className="font-semibold">Executive Owner</span> in the top bar.
                </p>
              </CardContent>
            </Card>
          ) : (
            <>
              {/* Executive Scrap Summary Cards (RA-08) */}
              {scrapData && (
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <Card>
                    <CardHeader className="pb-2">
                      <CardTitle className="text-xs font-semibold uppercase text-slate-500">
                        Total Recovered Valuation
                      </CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="text-2xl font-bold text-emerald-600">
                        {formatCurrency(scrapData.summary.total_recovery_value)}
                      </div>
                      <p className="text-xs text-slate-400 mt-0.5">Copper, Lithium & PCB salvage</p>
                    </CardContent>
                  </Card>
                  <Card>
                    <CardHeader className="pb-2">
                      <CardTitle className="text-xs font-semibold uppercase text-slate-500">
                        Cumulative Scrap Weight
                      </CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="text-2xl font-bold text-slate-900">
                        {scrapData.summary.total_weight_kg} kg
                      </div>
                      <p className="text-xs text-slate-400 mt-0.5">Physical material weight</p>
                    </CardContent>
                  </Card>
                  <Card>
                    <CardHeader className="pb-2">
                      <CardTitle className="text-xs font-semibold uppercase text-slate-500">
                        Total Disposal Entries
                      </CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="text-2xl font-bold text-blue-600">
                        {scrapData.summary.total_entries} Discharges
                      </div>
                      <p className="text-xs text-slate-400 mt-0.5">Executive View (RA-08)</p>
                    </CardContent>
                  </Card>
                </div>
              )}

              {/* Scrap Ledger Table */}
              <Card>
                <CardHeader>
                  <CardTitle className="text-base">Executive Scrap Recovery Ledger (RA-08)</CardTitle>
                  <CardDescription>
                    Full historical audit ledger visible only to Owner, CFO, and Executive Leadership
                  </CardDescription>
                </CardHeader>
                <CardContent className="p-0">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Serial #</TableHead>
                        <TableHead>Material Type</TableHead>
                        <TableHead>Weight</TableHead>
                        <TableHead>Estimated Value</TableHead>
                        <TableHead>Defect Reason</TableHead>
                        <TableHead>Discharged By</TableHead>
                        <TableHead>Branch / Date</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {scrapLoading ? (
                        <TableRow>
                          <TableCell colSpan={7} className="h-32 text-center text-slate-500">
                            Loading scrap ledger...
                          </TableCell>
                        </TableRow>
                      ) : (
                        scrapData?.records.map((r) => (
                          <TableRow key={r.id}>
                            <TableCell className="font-mono text-xs font-bold text-slate-900">
                              {r.serial_number}
                            </TableCell>
                            <TableCell className="font-semibold text-xs text-slate-900">
                              {r.material_type}
                            </TableCell>
                            <TableCell className="font-bold text-xs">{r.weight_kg} kg</TableCell>
                            <TableCell className="font-bold text-xs text-emerald-700">
                              {formatCurrency(r.est_recovery_value)}
                            </TableCell>
                            <TableCell className="text-xs text-slate-600 max-w-xs truncate">
                              {r.defect_reason}
                            </TableCell>
                            <TableCell className="text-xs text-slate-800">
                              {r.operator_name}
                            </TableCell>
                            <TableCell className="text-xs text-slate-400">
                              {r.warehouse_branch} · {formatDate(r.created_at)}
                            </TableCell>
                          </TableRow>
                        ))
                      )}
                    </TableBody>
                  </Table>
                </CardContent>
              </Card>
            </>
          )}
        </div>
      )}

      {/* =========================================================================
          TAB 5: REQUIREMENTS TRACEABILITY (16/16 REQS TRACKED)
          ========================================================================= */}
      {activeTab === 'traceability' && (
        <Card>
          <CardHeader>
            <CardTitle className="text-base flex items-center justify-between">
              <span>Identity & Access Requirements Traceability Matrix</span>
              <Badge variant="success">16 / 16 Requirements Tracked</Badge>
            </CardTitle>
            <CardDescription>
              Directly aligned with AVO Ops Platform FRS Tab 02 Identity, Roles & Access Control
            </CardDescription>
          </CardHeader>
          <CardContent className="p-0">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead className="w-12">#</TableHead>
                  <TableHead className="w-20">Req ID</TableHead>
                  <TableHead className="w-1/3">Requirement Specification</TableHead>
                  <TableHead>Scope</TableHead>
                  <TableHead>Implementation Strategy & Proof</TableHead>
                  <TableHead className="text-right">Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {[
                  {
                    num: 1,
                    id: 'RA-01',
                    desc: 'Role & Level Access: Field Engineer sees own work; Coordinator sees assigned region engineers; Ops Manager sees assigned warehouses/regions.',
                    scope: 'Release 1 (M)',
                    impl: 'Scoped SQL filters injected via user JWT token context (own, region, branch, global).',
                  },
                  {
                    num: 2,
                    id: 'RA-02',
                    desc: 'Department Delegation: Department heads can create role-based access within their department.',
                    scope: 'Release 1 (M)',
                    impl: 'Delegated permission admin interface restricted by user department identifier.',
                  },
                  {
                    num: 3,
                    id: 'RA-03',
                    desc: 'Reporting Hierarchy: System Admin/Owner assigns salespersons to managers and reassigns dynamically.',
                    scope: 'Release 1 (M)',
                    impl: 'Dynamic manager-subordinate relationship hierarchy stored in users database schema.',
                  },
                  {
                    num: 4,
                    id: 'RA-04',
                    desc: 'Chinese-Wall Bidding: Where AVO quotes competing integrators for same RFP, salesperson sees only own quote; only Owner sees all.',
                    scope: 'Release 1 (M)',
                    impl: 'Lateral deal isolation filter blocking quote view across salespeople in BiddingService.',
                  },
                  {
                    num: 5,
                    id: 'RA-05',
                    desc: 'Silent RFP Linking & Hit-Rate: Where two salespersons enter same RFP, system links silently; surfaced only in Owner view; hit-rate counts once.',
                    scope: 'Release 1 (M)',
                    impl: 'Hash-based background indexing on normalized RFP reference numbers without salesperson alert.',
                  },
                  {
                    num: 6,
                    id: 'RA-06',
                    desc: 'Customer Portal Sandbox: Status visibility only (log non-repeat calls, track progress; no edit, no history, no cost data).',
                    scope: 'Release 1 (M)',
                    impl: 'Customer role type sandbox completely isolated from internal financial costing data.',
                  },
                  {
                    num: 7,
                    id: 'RA-07',
                    desc: 'Blind Scrap Entry: Warehouse user may make scrap-discharge entries but cannot view past scrap records.',
                    scope: 'Release 1 (M)',
                    impl: 'Write-only scrap permission gate. GET /api/scrap returns HTTP 403 Forbidden to prevent inventory collusion.',
                  },
                  {
                    num: 8,
                    id: 'RA-08',
                    desc: 'Scrap Executive View: Scrap discharge visible only to Owner / CFO / COO.',
                    scope: 'Release 1 (M)',
                    impl: 'Table-level read restriction unlocked strictly for Executive and Finance Level L3 authorization.',
                  },
                  {
                    num: 9,
                    id: 'RA-09',
                    desc: 'Configurable Permission Matrix: Role screen set and field-level visibility delivered as editable matrix without code change.',
                    scope: 'Release 1 (M)',
                    impl: 'Visual screen/action permission manager stored in permission_matrix database table.',
                  },
                  {
                    num: 10,
                    id: 'SS-23',
                    desc: '3-Attribute Model: Model users as role type + scope + level, not one role per job title.',
                    scope: 'Release 1 (M)',
                    impl: 'Eliminates brittle hardcoding of changing job designations via 3-tuple evaluation.',
                  },
                  {
                    num: 11,
                    id: 'SS-24',
                    desc: 'API-Layer Enforcement: Scope enforced in API layer for every request, never by hiding interface elements only.',
                    scope: 'Release 1 (M)',
                    impl: 'Zero-trust backend authorization guard executed in CasbinMiddleware before route handler.',
                  },
                  {
                    num: 12,
                    id: 'SS-25',
                    desc: 'Unified Matrix: One permission matrix, admin-configurable, covering screens, fields and actions.',
                    scope: 'Release 1 (M)',
                    impl: 'Covers Screens, Actions (Create/Edit/Approve/Delete), and Level thresholds in unified UI.',
                  },
                  {
                    num: 13,
                    id: 'SS-33',
                    desc: 'Tuple Governance: Role type determines functions, scope determines visible data, level determines approval authority.',
                    scope: 'Release 1 (M)',
                    impl: 'Mathematical foundation for authorization engine: User Access = Role Type + Scope + Level.',
                  },
                  {
                    num: 14,
                    id: 'SS-34',
                    desc: '10 Role Types: Executive, Finance, Sales, Service Field, Service Desk, Repair, Operations/Warehouse, Project, Admin, Customer.',
                    scope: 'Release 1 (M)',
                    impl: 'Base standard role types defined in database seed data and IdentityService.',
                  },
                  {
                    num: 15,
                    id: 'SS-35',
                    desc: '14-Role Mapping: Every named role in original 14-role list expressed as combination of the 3 attributes, not separate code.',
                    scope: 'Release 1 (M)',
                    impl: 'All 14 organizational roles seeded as attribute templates without code modification.',
                  },
                  {
                    num: 16,
                    id: 'SS-36',
                    desc: 'Zero-Code Role Setup: Adding a designation or changing approval routing is a configuration change, not development.',
                    scope: 'Release 1 (M)',
                    impl: 'Instant administrative provisioning via web portal in 60 seconds.',
                  },
                ].map((req) => (
                  <TableRow key={req.id}>
                    <TableCell className="font-mono text-xs text-slate-400">{req.num}</TableCell>
                    <TableCell className="font-mono text-xs font-bold text-blue-700">
                      {req.id}
                    </TableCell>
                    <TableCell className="text-xs text-slate-900 font-medium">{req.desc}</TableCell>
                    <TableCell className="text-xs text-slate-500 whitespace-nowrap">
                      {req.scope}
                    </TableCell>
                    <TableCell className="text-xs text-slate-600">{req.impl}</TableCell>
                    <TableCell className="text-right">
                      <Badge variant="success" className="text-[10px]">
                        Available
                      </Badge>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      )}

      {/* =========================================================================
          MODALS
          ========================================================================= */}

      {/* Provision / Edit User Modal */}
      <Dialog open={isUserModalOpen} onClose={() => setIsUserModalOpen(false)} maxWidth="max-w-xl">
        <DialogHeader>
          <DialogTitle>{editingUser ? 'Edit User Attributes' : 'Provision User (SS-23, SS-36)'}</DialogTitle>
          <DialogDescription>
            Configure 3-attribute tuple: Role Type (Functional Screens) + Scope (Data Boundary) +
            Level (Signing Authority)
          </DialogDescription>
        </DialogHeader>
        <div className="space-y-4 py-4 text-xs">
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-1">
              <Label>Full Name</Label>
              <Input value={userName} onChange={(e) => setUserName(e.target.value)} placeholder="Full Name" />
            </div>
            <div className="space-y-1">
              <Label>Corporate Email *</Label>
              <Input
                type="email"
                value={userEmail}
                onChange={(e) => setUserEmail(e.target.value)}
                placeholder="email@avoerp.com"
                disabled={!!editingUser}
                className={userEmail && !isValidEmail(userEmail) ? 'border-red-500' : ''}
              />
              {!editingUser && userEmail && getEmailError(userEmail) && (
                <p className="text-[10px] text-red-600 font-medium">{getEmailError(userEmail)}</p>
              )}
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-1">
              <Label>Job Title / Designation</Label>
              <Input
                value={userDesignation}
                onChange={(e) => setUserDesignation(e.target.value)}
                placeholder="e.g. Senior Solar Engineer"
              />
            </div>
            <div className="space-y-1">
              <Label>Department</Label>
              <Input
                value={userDepartment}
                onChange={(e) => setUserDepartment(e.target.value)}
                placeholder="Department"
              />
            </div>
          </div>

          {/* The 3-Attribute Decoupled Selectors */}
          <div className="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
            <div className="text-xs font-bold text-slate-800 flex items-center">
              <ShieldCheck className="w-3.5 h-3.5 mr-1.5 text-blue-600" />
              Decoupled Tuple Configuration
            </div>

            <div className="grid grid-cols-3 gap-3">
              <div className="space-y-1">
                <Label className="text-[11px]">1. Role Type (Function)</Label>
                <select
                  value={userRoleType}
                  onChange={(e) => setUserRoleType(e.target.value as any)}
                  className="w-full h-8 px-2 bg-white border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500"
                >
                  {rolesData?.role_types.map((rt) => (
                    <option key={rt.key} value={rt.key}>
                      {rt.key}
                    </option>
                  ))}
                </select>
              </div>

              <div className="space-y-1">
                <Label className="text-[11px]">2. Scope (Data Filter)</Label>
                <select
                  value={userScope}
                  onChange={(e) => setUserScope(e.target.value as any)}
                  className="w-full h-8 px-2 bg-white border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500"
                >
                  <option value="own">own (Self only)</option>
                  <option value="region">region (Region Hub)</option>
                  <option value="branch">branch (Branch Hub)</option>
                  <option value="global">global (Company-wide)</option>
                </select>
              </div>

              <div className="space-y-1">
                <Label className="text-[11px]">3. Level (Authority)</Label>
                <select
                  value={userLevel}
                  onChange={(e) => setUserLevel(e.target.value as any)}
                  className="w-full h-8 px-2 bg-white border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500 font-bold"
                >
                  <option value="L1">L1 (Standard)</option>
                  <option value="L2">L2 (Override / Exceptions)</option>
                  <option value="L3">L3 (CFO / Owner Sign-off)</option>
                </select>
              </div>
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-1">
              <Label>Assigned Region</Label>
              <Input
                value={userRegion}
                onChange={(e) => setUserRegion(e.target.value)}
                placeholder="North, South, HQ..."
              />
            </div>
            <div className="space-y-1">
              <Label>Reporting Manager (RA-03)</Label>
              <select
                value={userReportingId || ''}
                onChange={(e) =>
                  setUserReportingId(e.target.value ? Number(e.target.value) : undefined)
                }
                className="w-full h-9 px-2 bg-white border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500"
              >
                <option value="">-- No Direct Manager --</option>
                {usersList
                  ?.filter((u) => u.id !== editingUser?.id)
                  .map((mgr) => (
                    <option key={mgr.id} value={mgr.id}>
                      {mgr.name} ({mgr.designation})
                    </option>
                  ))}
              </select>
            </div>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" onClick={() => setIsUserModalOpen(false)}>
            Cancel
          </Button>
          <Button
            onClick={handleSaveUser}
            isLoading={userSaveMutation.isPending}
            disabled={userSaveMutation.isPending || (!editingUser && !isValidEmail(userEmail)) || !userName.trim()}
          >
            {editingUser ? 'Save Tuple Changes' : 'Provision User (60s)'}
          </Button>
        </DialogFooter>
      </Dialog>

      {/* Submit RFP Quote Modal (RA-04, RA-05) */}
      <Dialog open={isRfpModalOpen} onClose={() => setIsRfpModalOpen(false)}>
        <DialogHeader>
          <DialogTitle>Submit Commercial RFP Quote</DialogTitle>
          <DialogDescription>
            Tender quotation with background silent link deduplication and Chinese-Wall isolation
          </DialogDescription>
        </DialogHeader>
        <div className="space-y-4 py-4 text-xs">
          <div className="space-y-1">
            <Label>RFP Reference Number</Label>
            <Input
              value={rfpNumber}
              onChange={(e) => setRfpNumber(e.target.value)}
              placeholder="e.g. 2026-NTPC-09"
            />
            <p className="text-[10px] text-slate-400">
              Entering the same RFP number as another salesperson will trigger silent linking in the
              Owner dashboard without compromising commercial secrecy.
            </p>
          </div>

          <div className="space-y-1">
            <Label>Tender Title</Label>
            <Input value={rfpTitle} onChange={(e) => setRfpTitle(e.target.value)} />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-1">
              <Label>End Client (PSU / Corp)</Label>
              <Input value={endClient} onChange={(e) => setEndClient(e.target.value)} />
            </div>
            <div className="space-y-1">
              <Label>System Integrator Name</Label>
              <Input
                value={integratorName}
                onChange={(e) => setIntegratorName(e.target.value)}
                placeholder="e.g. Integrator Alpha"
              />
            </div>
          </div>

          <div className="space-y-1">
            <Label>Total Quoted Amount ($)</Label>
            <Input
              type="number"
              value={quoteAmount}
              onChange={(e) => setQuoteAmount(Number(e.target.value))}
            />
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" onClick={() => setIsRfpModalOpen(false)}>
            Cancel
          </Button>
          <Button
            onClick={() =>
              rfpSubmitMutation.mutate({
                rfp_number: rfpNumber,
                rfp_title: rfpTitle,
                end_client: endClient,
                integrator_name: integratorName,
                quote_amount: Number(quoteAmount),
              })
            }
            isLoading={rfpSubmitMutation.isPending}
          >
            Submit Quote
          </Button>
        </DialogFooter>
      </Dialog>

      {/* Log Scrap Discharge Modal (RA-07) */}
      <Dialog open={isScrapModalOpen} onClose={() => setIsScrapModalOpen(false)}>
        <DialogHeader>
          <DialogTitle>Physical Scrap Discharge Entry</DialogTitle>
          <DialogDescription>
            Warehouse blind scrap entry gate: Records physical discharge without exposing running totals
          </DialogDescription>
        </DialogHeader>
        <div className="space-y-4 py-4 text-xs">
          <div className="space-y-1">
            <Label>Material Classification</Label>
            <select
              value={scrapMaterial}
              onChange={(e) => setScrapMaterial(e.target.value)}
              className="w-full h-9 px-2 bg-white border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500"
            >
              <option value="Copper Wire Coils">Copper Wire Coils</option>
              <option value="Tested Battery Cells (Lithium)">Tested Battery Cells (Lithium)</option>
              <option value="Salvageable Inverter PCBs">Salvageable Inverter PCBs</option>
              <option value="Transformer Core Scrap">Transformer Core Scrap</option>
              <option value="Aluminium Heat Sinks">Aluminium Heat Sinks</option>
            </select>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-1">
              <Label>Weight (Kilograms)</Label>
              <Input
                type="number"
                step="0.1"
                value={scrapWeight}
                onChange={(e) => setScrapWeight(Number(e.target.value))}
              />
            </div>
            <div className="space-y-1">
              <Label>Serial / Batch Number</Label>
              <Input
                value={scrapSerial}
                onChange={(e) => setScrapSerial(e.target.value)}
                placeholder="Optional serial tag"
              />
            </div>
          </div>

          <div className="space-y-1">
            <Label>Defect / Rejection Reason</Label>
            <Input
              value={scrapDefect}
              onChange={(e) => setScrapDefect(e.target.value)}
              placeholder="e.g. Coil insulation burnout, thermal degradation"
            />
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" onClick={() => setIsScrapModalOpen(false)}>
            Cancel
          </Button>
          <Button
            onClick={() =>
              scrapSubmitMutation.mutate({
                material_type: scrapMaterial,
                weight_kg: Number(scrapWeight),
                serial_number: scrapSerial,
                defect_reason: scrapDefect,
              })
            }
            isLoading={scrapSubmitMutation.isPending}
          >
            Record Physical Discharge
          </Button>
        </DialogFooter>
      </Dialog>
    </div>
  )
}

