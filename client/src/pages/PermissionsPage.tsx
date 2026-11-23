import { useState, useMemo } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import {
  Shield,
  Search,
  CheckCircle2,
  AlertCircle,
  Users,
  Grid3X3,
  ListFilter,
  Check,
  X,
  Sparkles,
  Crown,
  FileSpreadsheet,
  TrendingUp,
  Wrench,
  Headset,
  Hammer,
  Warehouse,
  SunMedium,
  ShieldCheck,
  RotateCcw,
} from 'lucide-react'
import { identityApi } from '@/api/services'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import type { PermissionMatrixItem, RoleType, User } from '@/types'

const ROLE_METADATA: Record<string, { desc: string; icon: any }> = {
  Executive: { desc: 'Executive leadership with strategic oversight and signing authority.', icon: Crown },
  Finance: { desc: 'Billing, credit control, invoicing, and ledger auditing.', icon: FileSpreadsheet },
  Sales: { desc: 'Commercial pipeline, customer accounts, and RFP bid tenders.', icon: TrendingUp },
  'Operations/Warehouse': { desc: 'Hub storage, inventory receipts, dispatches, and scrap entries.', icon: Warehouse },
  Admin: { desc: 'System IT administration, user provisioning, and policy management.', icon: ShieldCheck },
  'Service Field': { desc: 'On-site engineering, technical installations, and diagnostics.', icon: Wrench },
  'Service Desk': { desc: 'Helpdesk call intake, technician dispatch, and customer support.', icon: Headset },
  Repair: { desc: 'Workshop repair bench, component rework, and PCB diagnostics.', icon: Hammer },
  Project: { desc: 'Solar engineering, site commissioning, and turnkey execution.', icon: SunMedium },
  Customer: { desc: 'Client portal view for order tracking and AMC contract monitoring.', icon: Users },
}

export function PermissionsPage() {
  const queryClient = useQueryClient()
  const [viewMode, setViewMode] = useState<'profile' | 'matrix'>('profile')
  const [selectedRole, setSelectedRole] = useState<string>('Executive')
  const [roleSearch, setRoleSearch] = useState('')
  const [feedback, setFeedback] = useState<{ type: 'success' | 'error'; message: string } | null>(null)

  // Queries
  const { data: matrix = [], isLoading: matrixLoading } = useQuery({
    queryKey: ['identity-matrix'],
    queryFn: identityApi.getMatrix,
  })

  const { data: users = [] } = useQuery({
    queryKey: ['identity-users'],
    queryFn: identityApi.getUsers,
  })

  // Count active users per role
  const userCounts = useMemo(() => {
    return users.reduce((acc: Record<string, number>, u: User) => {
      acc[u.role_type] = (acc[u.role_type] || 0) + 1
      return acc
    }, {})
  }, [users])

  // Distinct roles available in database matrix
  const distinctRoles = useMemo(() => {
    const set = new Set<string>()
    matrix.forEach((item) => set.add(item.role_type))
    const list = Array.from(set)
    return list.length > 0
      ? list
      : Object.keys(ROLE_METADATA)
  }, [matrix])

  // Single cell permission toggle mutation
  const toggleMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<PermissionMatrixItem> }) =>
      identityApi.updateMatrix(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['identity-matrix'] })
      setFeedback({ type: 'success', message: 'Permission updated successfully.' })
      setTimeout(() => setFeedback(null), 2500)
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.message || 'Failed to update permission' })
    },
  })

  // Bulk role preset mutation
  const bulkMutation = useMutation({
    mutationFn: ({ role, preset }: { role: string; preset: 'all' | 'read_only' | 'standard' | 'none' }) =>
      identityApi.bulkUpdateRole(role, preset),
    onSuccess: (_, vars) => {
      queryClient.invalidateQueries({ queryKey: ['identity-matrix'] })
      setFeedback({
        type: 'success',
        message: `Applied '${vars.preset.toUpperCase()}' policy preset to role '${vars.role}'.`,
      })
      setTimeout(() => setFeedback(null), 3000)
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.message || 'Failed to apply preset' })
    },
  })

  // Modules for the selected role in Profile view (exactly 9 rows)
  const currentRoleModules = useMemo(() => {
    return matrix.filter((item) => item.role_type === selectedRole)
  }, [matrix, selectedRole])

  // Distinct module keys for Comparison Matrix view
  const distinctModules = useMemo(() => {
    const map = new Map<string, string>()
    matrix.forEach((item) => {
      if (!map.has(item.module_key)) {
        map.set(item.module_key, item.module_name)
      }
    })
    return Array.from(map.entries()).map(([key, name]) => ({ key, name }))
  }, [matrix])

  const handleToggle = (
    item: PermissionMatrixItem,
    field: 'can_view' | 'can_create' | 'can_edit' | 'can_approve' | 'can_delete'
  ) => {
    toggleMutation.mutate({
      id: item.id,
      data: {
        can_view: field === 'can_view' ? (item.can_view ? 0 : 1) : item.can_view,
        can_create: field === 'can_create' ? (item.can_create ? 0 : 1) : item.can_create,
        can_edit: field === 'can_edit' ? (item.can_edit ? 0 : 1) : item.can_edit,
        can_approve: field === 'can_approve' ? (item.can_approve ? 0 : 1) : item.can_approve,
        can_delete: field === 'can_delete' ? (item.can_delete ? 0 : 1) : item.can_delete,
      },
    })
  }

  // Filtered roles list for left pane
  const filteredRoles = useMemo(() => {
    return distinctRoles.filter((r) =>
      r.toLowerCase().includes(roleSearch.toLowerCase())
    )
  }, [distinctRoles, roleSearch])

  // Helper for Comparison Matrix summary chip
  const getMatrixCellSummary = (role: string, moduleKey: string) => {
    const item = matrix.find((m) => m.role_type === role && m.module_key === moduleKey)
    if (!item) return { label: '—', cls: 'text-zinc-300' }

    if (item.can_view && item.can_create && item.can_edit && item.can_approve && item.can_delete) {
      return { label: 'FULL', cls: 'bg-zinc-900 text-white font-bold' }
    }
    if (item.can_view && (item.can_create || item.can_edit)) {
      return { label: 'R / W', cls: 'bg-zinc-200 text-zinc-900 font-medium' }
    }
    if (item.can_view) {
      return { label: 'VIEW', cls: 'bg-zinc-100 text-zinc-700' }
    }
    return { label: 'DENIED', cls: 'text-zinc-400 bg-zinc-50 border border-zinc-200' }
  }

  return (
    <div className="space-y-3.5 max-w-7xl mx-auto">
      {/* Top Banner / Feedback Alert */}
      {feedback && (
        <div
          className={`p-2.5 rounded-md flex items-center space-x-2 text-xs font-medium border shadow-2xs transition-all ${
            feedback.type === 'success'
              ? 'bg-zinc-50 text-zinc-900 border-zinc-300'
              : 'bg-zinc-50 text-rose-800 border-rose-200'
          }`}
        >
          {feedback.type === 'success' ? (
            <CheckCircle2 className="w-3.5 h-3.5 text-zinc-900 shrink-0" />
          ) : (
            <AlertCircle className="w-3.5 h-3.5 text-rose-600 shrink-0" />
          )}
          <span>{feedback.message}</span>
        </div>
      )}

      {/* Page Header with ERP Mode Switcher */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3.5 px-4 rounded-lg border border-zinc-200 shadow-2xs">
        <div>
          <h1 className="text-sm font-semibold tracking-tight text-zinc-900">Access Control & Permissions</h1>
          <p className="text-[11px] text-zinc-500 mt-0.5">
            Industry-standard role authorization. Manage module rights per role.
          </p>
        </div>

        {/* View Switcher: Role Profile vs Comparison Matrix */}
        <div className="flex bg-zinc-100 p-0.5 rounded-md border border-zinc-200 self-start sm:self-auto">
          <button
            onClick={() => setViewMode('profile')}
            className={`px-3 py-1 text-xs font-medium rounded transition-all cursor-pointer flex items-center gap-1.5 ${
              viewMode === 'profile'
                ? 'bg-white text-zinc-900 shadow-2xs font-semibold'
                : 'text-zinc-600 hover:text-zinc-900'
            }`}
          >
            <ListFilter className="w-3 h-3" />
            Role Profile Editor
          </button>
          <button
            onClick={() => setViewMode('matrix')}
            className={`px-3 py-1 text-xs font-medium rounded transition-all cursor-pointer flex items-center gap-1.5 ${
              viewMode === 'matrix'
                ? 'bg-white text-zinc-900 shadow-2xs font-semibold'
                : 'text-zinc-600 hover:text-zinc-900'
            }`}
          >
            <Grid3X3 className="w-3 h-3" />
            Comparison Matrix
          </button>
        </div>
      </div>

      {/* ============================================================== */}
      {/* MODE 1: PROFESSIONAL ERP ROLE PROFILE EDITOR (MASTER-DETAIL)   */}
      {/* ============================================================== */}
      {viewMode === 'profile' && (
        <div className="grid grid-cols-1 md:grid-cols-12 gap-3.5">
          {/* LEFT COLUMN: Role Directory (Master List) */}
          <div className="md:col-span-4 lg:col-span-3 space-y-2">
            <Card className="border-zinc-200 shadow-2xs overflow-hidden">
              <div className="p-2.5 border-b border-zinc-100 bg-zinc-50/70">
                <div className="flex items-center justify-between">
                  <span className="text-[11px] font-semibold text-zinc-600 uppercase tracking-wider">
                    Roles Directory ({distinctRoles.length})
                  </span>
                  <span className="text-[10px] text-zinc-400">Select to Edit</span>
                </div>
              </div>

              <div className="p-1.5 space-y-0.5 max-h-[68vh] overflow-y-auto">
                {filteredRoles.map((roleKey) => {
                  const meta = ROLE_METADATA[roleKey] || { desc: '', icon: Shield }
                  const Icon = meta.icon
                  const isSelected = selectedRole === roleKey
                  const count = userCounts[roleKey] || 0

                  return (
                    <button
                      key={roleKey}
                      onClick={() => setSelectedRole(roleKey)}
                      className={`w-full text-left p-2 px-2.5 rounded-md text-xs transition-all flex items-center justify-between cursor-pointer ${
                        isSelected
                          ? 'bg-zinc-900 text-white font-medium shadow-2xs'
                          : 'text-zinc-700 hover:bg-zinc-100'
                      }`}
                    >
                      <div className="flex items-center space-x-2 truncate">
                        <Icon className={`w-3.5 h-3.5 shrink-0 ${isSelected ? 'text-white' : 'text-zinc-500'}`} />
                        <span className="truncate">{roleKey}</span>
                      </div>
                      <span
                        className={`text-[10px] px-1.5 py-0.2 rounded font-mono ${
                          isSelected
                            ? 'bg-zinc-800 text-zinc-300'
                            : 'bg-zinc-100 text-zinc-500'
                        }`}
                      >
                        {count}
                      </span>
                    </button>
                  )
                })}
              </div>
            </Card>
          </div>

          {/* RIGHT COLUMN: Permissions Table for Selected Role (Detail) */}
          <div className="md:col-span-8 lg:col-span-9 space-y-3">
            <Card className="border-zinc-200 shadow-2xs overflow-hidden">
              {/* Role Header Banner */}
              <div className="p-3 px-4 bg-zinc-900 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                  <div className="flex items-center space-x-2">
                    <span className="text-sm font-semibold tracking-tight text-white">{selectedRole}</span>
                    <span className="text-[10px] px-1.5 py-0.5 rounded bg-zinc-800 text-zinc-300 font-mono">
                      {userCounts[selectedRole] || 0} active users
                    </span>
                  </div>
                  <p className="text-[11px] text-zinc-400 mt-0.5">
                    {ROLE_METADATA[selectedRole]?.desc || 'Configure module authorization rights for this role.'}
                  </p>
                </div>

                {/* Bulk Policy Presets (Industry ERP Feature) */}
                <div className="flex items-center gap-1.5 shrink-0">
                  <span className="text-[10px] text-zinc-400 mr-1 hidden lg:inline">Presets:</span>
                  <button
                    onClick={() => bulkMutation.mutate({ role: selectedRole, preset: 'all' })}
                    disabled={bulkMutation.isPending}
                    title="Grant all permissions across all modules"
                    className="text-[11px] px-2 py-0.5 rounded bg-zinc-800 hover:bg-zinc-700 text-zinc-200 transition-colors cursor-pointer"
                  >
                    Grant All
                  </button>
                  <button
                    onClick={() => bulkMutation.mutate({ role: selectedRole, preset: 'read_only' })}
                    disabled={bulkMutation.isPending}
                    title="Allow View only for all modules"
                    className="text-[11px] px-2 py-0.5 rounded bg-zinc-800 hover:bg-zinc-700 text-zinc-200 transition-colors cursor-pointer"
                  >
                    Read Only
                  </button>
                  <button
                    onClick={() => bulkMutation.mutate({ role: selectedRole, preset: 'none' })}
                    disabled={bulkMutation.isPending}
                    title="Revoke all permissions"
                    className="text-[11px] px-2 py-0.5 rounded bg-zinc-800 hover:bg-zinc-700 text-zinc-200 transition-colors cursor-pointer"
                  >
                    Revoke All
                  </button>
                </div>
              </div>

              {/* Focused 9 Modules Table */}
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader className="bg-zinc-50/80 border-b border-zinc-200">
                    <TableRow>
                      <TableHead className="w-10 text-center font-semibold text-zinc-600 text-[11px]">
                        #
                      </TableHead>
                      <TableHead className="font-semibold text-zinc-600 text-[11px] w-[35%]">
                        Module Name
                      </TableHead>
                      <TableHead className="text-center font-semibold text-zinc-600 text-[11px]">
                        View
                      </TableHead>
                      <TableHead className="text-center font-semibold text-zinc-600 text-[11px]">
                        Create
                      </TableHead>
                      <TableHead className="text-center font-semibold text-zinc-600 text-[11px]">
                        Edit
                      </TableHead>
                      <TableHead className="text-center font-semibold text-zinc-600 text-[11px]">
                        Approve
                      </TableHead>
                      <TableHead className="text-center font-semibold text-zinc-600 text-[11px]">
                        Delete
                      </TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {matrixLoading ? (
                      <TableRow>
                        <TableCell colSpan={7} className="text-center py-8 text-zinc-400 text-xs">
                          Loading permissions...
                        </TableCell>
                      </TableRow>
                    ) : currentRoleModules.length === 0 ? (
                      <TableRow>
                        <TableCell colSpan={7} className="text-center py-8 text-zinc-400 text-xs">
                          No module permissions configured for {selectedRole}.
                        </TableCell>
                      </TableRow>
                    ) : (
                      currentRoleModules.map((item, idx) => (
                        <TableRow key={item.id} className="hover:bg-zinc-50/60 transition-colors">
                          <TableCell className="text-center font-mono text-[11px] text-zinc-400 w-10">
                            {idx + 1}
                          </TableCell>
                          <TableCell className="py-2.5">
                            <div className="font-medium text-zinc-900 text-xs">{item.module_name}</div>
                            <div className="text-[10px] text-zinc-400 font-mono">{item.module_key}</div>
                          </TableCell>

                          {/* View */}
                          <TableCell className="text-center py-2.5">
                            <button
                              onClick={() => handleToggle(item, 'can_view')}
                              className={`px-2 py-0.5 rounded text-[10px] font-medium transition-all cursor-pointer ${
                                item.can_view
                                  ? 'bg-zinc-900 text-white'
                                  : 'bg-zinc-100 text-zinc-400 hover:text-zinc-600'
                              }`}
                            >
                              {item.can_view ? 'Allowed' : 'Denied'}
                            </button>
                          </TableCell>

                          {/* Create */}
                          <TableCell className="text-center py-2.5">
                            <button
                              onClick={() => handleToggle(item, 'can_create')}
                              className={`px-2 py-0.5 rounded text-[10px] font-medium transition-all cursor-pointer ${
                                item.can_create
                                  ? 'bg-zinc-900 text-white'
                                  : 'bg-zinc-100 text-zinc-400 hover:text-zinc-600'
                              }`}
                            >
                              {item.can_create ? 'Allowed' : 'Denied'}
                            </button>
                          </TableCell>

                          {/* Edit */}
                          <TableCell className="text-center py-2.5">
                            <button
                              onClick={() => handleToggle(item, 'can_edit')}
                              className={`px-2 py-0.5 rounded text-[10px] font-medium transition-all cursor-pointer ${
                                item.can_edit
                                  ? 'bg-zinc-900 text-white'
                                  : 'bg-zinc-100 text-zinc-400 hover:text-zinc-600'
                              }`}
                            >
                              {item.can_edit ? 'Allowed' : 'Denied'}
                            </button>
                          </TableCell>

                          {/* Approve */}
                          <TableCell className="text-center py-2.5">
                            <button
                              onClick={() => handleToggle(item, 'can_approve')}
                              className={`px-2 py-0.5 rounded text-[10px] font-medium transition-all cursor-pointer ${
                                item.can_approve
                                  ? 'bg-zinc-900 text-white'
                                  : 'bg-zinc-100 text-zinc-400 hover:text-zinc-600'
                              }`}
                            >
                              {item.can_approve ? 'Allowed' : 'Denied'}
                            </button>
                          </TableCell>

                          {/* Delete */}
                          <TableCell className="text-center py-2.5">
                            <button
                              onClick={() => handleToggle(item, 'can_delete')}
                              className={`px-2 py-0.5 rounded text-[10px] font-medium transition-all cursor-pointer ${
                                item.can_delete
                                  ? 'bg-zinc-900 text-white'
                                  : 'bg-zinc-100 text-zinc-400 hover:text-zinc-600'
                              }`}
                            >
                              {item.can_delete ? 'Allowed' : 'Denied'}
                            </button>
                          </TableCell>
                        </TableRow>
                      ))
                    )}
                  </TableBody>
                </Table>
              </div>

              {/* Status Footer */}
              <div className="p-2.5 px-3 bg-zinc-50 border-t border-zinc-100 flex items-center justify-between text-[11px] text-zinc-500">
                <span>Showing 9 core enterprise modules for {selectedRole}</span>
                <span className="font-medium text-zinc-700 flex items-center gap-1">
                  <CheckCircle2 className="w-3 h-3 text-zinc-800" />
                  Auto-saved
                </span>
              </div>
            </Card>
          </div>
        </div>
      )}

      {/* ============================================================== */}
      {/* MODE 2: COMPARISON MATRIX (EXECUTIVE OVERVIEW PIVOT TABLE)     */}
      {/* ============================================================== */}
      {viewMode === 'matrix' && (
        <Card className="border-zinc-200 shadow-2xs overflow-hidden">
          <div className="p-3 border-b border-zinc-200 bg-zinc-50/70 flex items-center justify-between">
            <div>
              <h3 className="text-xs font-semibold text-zinc-900">Organization Security Comparison Matrix</h3>
              <p className="text-[10px] text-zinc-500">
                High-level audit grid comparing permissions across all 10 roles on a single screen.
              </p>
            </div>
            <div className="flex items-center gap-2 text-[10px] text-zinc-600">
              <span className="flex items-center gap-1">
                <span className="w-2 h-2 rounded-full bg-zinc-900 inline-block"></span> FULL = All 5 Rights
              </span>
              <span className="flex items-center gap-1">
                <span className="w-2 h-2 rounded-full bg-zinc-400 inline-block"></span> R/W = View + Write
              </span>
              <span className="flex items-center gap-1">
                <span className="w-2 h-2 rounded-full bg-zinc-200 inline-block"></span> VIEW = Read Only
              </span>
            </div>
          </div>

          <div className="overflow-x-auto">
            <Table>
              <TableHeader className="bg-zinc-50 border-b border-zinc-200">
                <TableRow>
                  <TableHead className="w-10 text-center font-semibold text-zinc-700 text-[11px] sticky left-0 bg-zinc-50 z-20">
                    #
                  </TableHead>
                  <TableHead className="font-semibold text-zinc-700 text-[11px] sticky left-10 bg-zinc-50 min-w-44 z-10">
                    Module
                  </TableHead>
                  {distinctRoles.map((role) => (
                    <TableHead key={role} className="text-center font-semibold text-zinc-700 text-[10px] min-w-24">
                      {role}
                    </TableHead>
                  ))}
                </TableRow>
              </TableHeader>
              <TableBody>
                {distinctModules.map((mod, idx) => (
                  <TableRow key={mod.key} className="hover:bg-zinc-50/60 transition-colors">
                    <TableCell className="text-center font-mono text-[11px] text-zinc-400 w-10 sticky left-0 bg-white border-r border-zinc-100 z-20 py-2">
                      {idx + 1}
                    </TableCell>
                    <TableCell className="font-medium text-zinc-900 text-xs sticky left-10 bg-white border-r border-zinc-100 z-10 py-2">
                      <div>{mod.name}</div>
                      <div className="text-[10px] text-zinc-400 font-mono">{mod.key}</div>
                    </TableCell>

                    {distinctRoles.map((role) => {
                      const summary = getMatrixCellSummary(role, mod.key)
                      return (
                        <TableCell key={role} className="text-center py-2">
                          <span
                            className={`inline-block px-1.5 py-0.5 rounded text-[10px] font-mono leading-none ${summary.cls}`}
                          >
                            {summary.label}
                          </span>
                        </TableCell>
                      )
                    })}
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>
        </Card>
      )}
    </div>
  )
}
