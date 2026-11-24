import { useState, useMemo } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import {
  Users,
  UserPlus,
  Search,
  Shield,
  Layers,
  MapPin,
  CheckCircle2,
  AlertCircle,
  Edit2,
  Trash2,
  UserCheck,
} from 'lucide-react'
import { identityApi } from '@/api/services'
import { useAuthStore } from '@/store/authStore'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Dialog, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { isValidEmail, getEmailError } from '@/lib/validation'
import type { RoleType, ScopeType, LevelType, User } from '@/types'

const ROLE_TYPES: RoleType[] = [
  'Executive',
  'Finance',
  'Sales',
  'Service Field',
  'Service Desk',
  'Repair',
  'Operations/Warehouse',
  'Project',
  'Admin',
  'Customer',
]

const SCOPES: { value: ScopeType; label: string; desc: string }[] = [
  { value: 'own', label: 'Own Data Only', desc: 'Restricted strictly to personal accounts and leads' },
  { value: 'branch', label: 'Branch / Hub', desc: 'Accessible across local branch facility' },
  { value: 'region', label: 'Territory / Region', desc: 'Multi-branch zone oversight (e.g. Western Region)' },
  { value: 'global', label: 'Global Enterprise', desc: 'Unrestricted enterprise-wide data access' },
]

const LEVELS: { value: LevelType; label: string; desc: string }[] = [
  { value: 'L1', label: 'L1 - Execution / Staff', desc: 'Operational day-to-day data entry and processing' },
  { value: 'L2', label: 'L2 - Manager / Lead', desc: 'Operational approvals, team oversight, dispatch' },
  { value: 'L3', label: 'L3 - Director / Owner', desc: 'Authoritative sign-off, high-value overrides' },
]

export function UsersPage() {
  const queryClient = useQueryClient()
  const { user: currentUser } = useAuthStore()

  // State
  const [search, setSearch] = useState('')
  const [selectedRoleType, setSelectedRoleType] = useState<string>('ALL')
  const [selectedScope, setSelectedScope] = useState<string>('ALL')
  const [selectedLevel, setSelectedLevel] = useState<string>('ALL')

  const [isModalOpen, setIsModalOpen] = useState(false)
  const [editingUser, setEditingUser] = useState<User | null>(null)
  const [feedback, setFeedback] = useState<{ type: 'success' | 'error'; message: string } | null>(null)

  // Form State
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [designation, setDesignation] = useState('')
  const [roleType, setRoleType] = useState<RoleType>('Sales')
  const [scope, setScope] = useState<ScopeType>('own')
  const [level, setLevel] = useState<LevelType>('L1')
  const [department, setDepartment] = useState('Commercial Sales')
  const [region, setRegion] = useState('North')
  const [branch, setBranch] = useState('Delhi Central Hub')
  const [reportingManagerId, setReportingManagerId] = useState<number | undefined>(undefined)

  // Queries
  const { data: users = [], isLoading } = useQuery({
    queryKey: ['identity-users'],
    queryFn: identityApi.getUsers,
  })

  // Mutation
  const saveMutation = useMutation({
    mutationFn: (payload: any) =>
      editingUser
        ? identityApi.updateUser(editingUser.id, payload)
        : identityApi.createUser(payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['identity-users'] })
      setIsModalOpen(false)
      setEditingUser(null)
      setFeedback({
        type: 'success',
        message: editingUser
          ? 'User details updated successfully.'
          : 'New user created successfully in database.',
      })
      setTimeout(() => setFeedback(null), 3000)
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.message || 'Operation failed' })
    },
  })

  // Delete State & Mutation
  const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false)
  const [userToDelete, setUserToDelete] = useState<User | null>(null)

  const deleteMutation = useMutation({
    mutationFn: (id: number) => identityApi.deleteUser(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['identity-users'] })
      setIsDeleteModalOpen(false)
      setUserToDelete(null)
      setFeedback({ type: 'success', message: 'User deleted successfully.' })
      setTimeout(() => setFeedback(null), 3000)
    },
    onError: (err: any) => {
      setFeedback({
        type: 'error',
        message: err?.response?.data?.message || err.message || 'Failed to delete user',
      })
    },
  })

  // Filtered Users
  const filteredUsers = useMemo(() => {
    return users.filter((u: User) => {
      const matchSearch =
        !search ||
        u.name.toLowerCase().includes(search.toLowerCase()) ||
        u.email.toLowerCase().includes(search.toLowerCase()) ||
        (u.designation && u.designation.toLowerCase().includes(search.toLowerCase())) ||
        (u.department && u.department.toLowerCase().includes(search.toLowerCase()))

      const matchRole = selectedRoleType === 'ALL' || u.role_type === selectedRoleType
      const matchScope = selectedScope === 'ALL' || u.scope === selectedScope
      const matchLevel = selectedLevel === 'ALL' || u.level === selectedLevel

      return matchSearch && matchRole && matchScope && matchLevel
    })
  }, [users, search, selectedRoleType, selectedScope, selectedLevel])

  // Stats calculation
  const stats = useMemo(() => {
    const total = users.length
    const globalCount = users.filter((u: User) => u.scope === 'global').length
    const l3Count = users.filter((u: User) => u.level === 'L3').length
    const l2Count = users.filter((u: User) => u.level === 'L2').length
    return { total, globalCount, l3Count, l2Count }
  }, [users])

  const openCreateModal = () => {
    setEditingUser(null)
    setName('')
    setEmail('')
    setPassword('')
    setDesignation('Sales Executive')
    setRoleType('Sales')
    setScope('own')
    setLevel('L1')
    setDepartment('Commercial Sales')
    setRegion('North')
    setBranch('Delhi Central Hub')
    setReportingManagerId(users.length > 0 ? users[0].id : undefined)
    setIsModalOpen(true)
  }

  const openEditModal = (target: User) => {
    setEditingUser(target)
    setName(target.name)
    setEmail(target.email)
    setPassword('')
    setDesignation(target.designation || '')
    setRoleType(target.role_type)
    setScope(target.scope)
    setLevel(target.level)
    setDepartment(target.department || 'Operations')
    setRegion(target.region || 'North')
    setBranch(target.branch || 'HQ Hub')
    setReportingManagerId(target.reporting_manager_id || undefined)
    setIsModalOpen(true)
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    if (!editingUser && !isValidEmail(email)) {
      setFeedback({ type: 'error', message: 'Please enter a valid email address.' })
      return
    }
    const payload: any = {
      name,
      email,
      role_type: roleType,
      scope,
      level,
      designation,
      department,
      region,
      branch,
      reporting_manager_id: reportingManagerId ? Number(reportingManagerId) : null,
    }

    if (!editingUser) {
      payload.password = password || 'admin123'
    } else if (password) {
      payload.password = password
    }

    saveMutation.mutate(payload)
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

      {/* Compact Page Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3.5 px-4 rounded-lg border border-zinc-200 shadow-2xs">
        <div>
          <h1 className="text-sm font-semibold tracking-tight text-zinc-900">User Management</h1>
          <p className="text-[11px] text-zinc-500 mt-0.5">
            Enterprise user directory and role assignment management.
          </p>
        </div>
        <Button onClick={openCreateModal} className="h-7.5 px-3 text-xs bg-zinc-900 hover:bg-black text-white shrink-0">
          <UserPlus className="w-3.5 h-3.5 mr-1.5" />
          Provision User
        </Button>
      </div>

      {/* Minimal Stats Row */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
        <div className="bg-white p-2.5 px-3 rounded-lg border border-zinc-200 flex items-center justify-between shadow-2xs">
          <div>
            <p className="text-[10px] font-medium text-zinc-500 uppercase tracking-wider">Total Users</p>
            <h3 className="text-base font-bold text-zinc-900 leading-tight mt-0.5">{stats.total}</h3>
          </div>
          <div className="w-7 h-7 rounded-md bg-zinc-100 flex items-center justify-center text-zinc-700">
            <Users className="w-3.5 h-3.5" />
          </div>
        </div>

        <div className="bg-white p-2.5 px-3 rounded-lg border border-zinc-200 flex items-center justify-between shadow-2xs">
          <div>
            <p className="text-[10px] font-medium text-zinc-500 uppercase tracking-wider">Global Scope</p>
            <h3 className="text-base font-bold text-zinc-900 leading-tight mt-0.5">{stats.globalCount}</h3>
          </div>
          <div className="w-7 h-7 rounded-md bg-zinc-100 flex items-center justify-center text-zinc-700">
            <Layers className="w-3.5 h-3.5" />
          </div>
        </div>

        <div className="bg-white p-2.5 px-3 rounded-lg border border-zinc-200 flex items-center justify-between shadow-2xs">
          <div>
            <p className="text-[10px] font-medium text-zinc-500 uppercase tracking-wider">L3 Directors</p>
            <h3 className="text-base font-bold text-zinc-900 leading-tight mt-0.5">{stats.l3Count}</h3>
          </div>
          <div className="w-7 h-7 rounded-md bg-zinc-100 flex items-center justify-center text-zinc-700">
            <Shield className="w-3.5 h-3.5" />
          </div>
        </div>

        <div className="bg-white p-2.5 px-3 rounded-lg border border-zinc-200 flex items-center justify-between shadow-2xs">
          <div>
            <p className="text-[10px] font-medium text-zinc-500 uppercase tracking-wider">L2 Managers</p>
            <h3 className="text-base font-bold text-zinc-900 leading-tight mt-0.5">{stats.l2Count}</h3>
          </div>
          <div className="w-7 h-7 rounded-md bg-zinc-100 flex items-center justify-center text-zinc-700">
            <UserCheck className="w-3.5 h-3.5" />
          </div>
        </div>
      </div>

      {/* Minimal Filter and Search Bar */}
      <Card className="border-zinc-200 shadow-2xs">
        <CardContent className="p-2.5 px-3">
          <div className="flex flex-col md:flex-row gap-2">
            {/* Search Input */}
            <div className="relative flex-1">
              <Search className="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-zinc-400" />
              <Input
                placeholder="Search by name, email, department..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="h-8 pl-8 text-xs bg-zinc-50/50 border-zinc-200 focus:bg-white"
              />
            </div>

            {/* Role Filter */}
            <select
              value={selectedRoleType}
              onChange={(e) => setSelectedRoleType(e.target.value)}
              className="h-8 px-2.5 text-xs bg-zinc-50/50 border border-zinc-200 rounded-md text-zinc-700 focus:bg-white focus:outline-hidden focus:border-zinc-400"
            >
              <option value="ALL">All Roles ({ROLE_TYPES.length})</option>
              {ROLE_TYPES.map((rt) => (
                <option key={rt} value={rt}>
                  {rt}
                </option>
              ))}
            </select>

            {/* Scope Filter */}
            <select
              value={selectedScope}
              onChange={(e) => setSelectedScope(e.target.value)}
              className="h-8 px-2.5 text-xs bg-zinc-50/50 border border-zinc-200 rounded-md text-zinc-700 focus:bg-white focus:outline-hidden focus:border-zinc-400"
            >
              <option value="ALL">All Scopes</option>
              <option value="own">Own Data Only</option>
              <option value="branch">Branch / Hub</option>
              <option value="region">Territory / Region</option>
              <option value="global">Global Enterprise</option>
            </select>

            {/* Level Filter */}
            <select
              value={selectedLevel}
              onChange={(e) => setSelectedLevel(e.target.value)}
              className="h-8 px-2.5 text-xs bg-zinc-50/50 border border-zinc-200 rounded-md text-zinc-700 focus:bg-white focus:outline-hidden focus:border-zinc-400"
            >
              <option value="ALL">All Levels</option>
              <option value="L1">L1 - Staff</option>
              <option value="L2">L2 - Manager</option>
              <option value="L3">L3 - Director</option>
            </select>

            {(search || selectedRoleType !== 'ALL' || selectedScope !== 'ALL' || selectedLevel !== 'ALL') && (
              <Button
                variant="outline"
                size="sm"
                onClick={() => {
                  setSearch('')
                  setSelectedRoleType('ALL')
                  setSelectedScope('ALL')
                  setSelectedLevel('ALL')
                }}
                className="h-8 text-xs text-zinc-600 px-2"
              >
                Reset
              </Button>
            )}
          </div>
        </CardContent>
      </Card>

      {/* Users Table */}
      <Card className="border-zinc-200 shadow-2xs overflow-hidden">
        <div className="overflow-x-auto">
          <Table className="min-w-[850px]">
            <TableHeader className="bg-zinc-50/80 border-b border-zinc-200">
              <TableRow>
                <TableHead className="font-semibold text-zinc-600 text-[11px] w-10 text-center">#</TableHead>
                <TableHead className="font-semibold text-zinc-600 text-[11px]">User</TableHead>
                <TableHead className="font-semibold text-zinc-600 text-[11px]">Role Type</TableHead>
                <TableHead className="font-semibold text-zinc-600 text-[11px]">Scope</TableHead>
                <TableHead className="font-semibold text-zinc-600 text-[11px] text-center">Level</TableHead>
                <TableHead className="font-semibold text-zinc-600 text-[11px]">Department & Hub</TableHead>
                <TableHead className="font-semibold text-zinc-600 text-[11px]">Manager</TableHead>
                <TableHead className="text-right font-semibold text-zinc-600 text-[11px]">Action</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {isLoading ? (
                <TableRow>
                  <TableCell colSpan={8} className="text-center py-8 text-zinc-400 text-xs">
                    Loading users...
                  </TableCell>
                </TableRow>
              ) : filteredUsers.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={8} className="text-center py-8 text-zinc-400 text-xs">
                    No users found matching your search filter.
                  </TableCell>
                </TableRow>
              ) : (
                filteredUsers.map((u: User, idx: number) => (
                  <TableRow key={u.id} className="hover:bg-zinc-50/60 transition-colors">
                    {/* # Serial Number */}
                    <TableCell className="text-center font-mono text-[11px] text-zinc-400 w-10">
                      {idx + 1}
                    </TableCell>

                    {/* User & Email */}
                    <TableCell>
                      <div className="flex items-center space-x-2.5">
                        <div className="w-6 h-6 rounded-full bg-zinc-900 text-white font-medium text-[10px] flex items-center justify-center shrink-0">
                          {u.name
                            .split(' ')
                            .map((n) => n[0])
                            .join('')
                            .substring(0, 2)
                            .toUpperCase()}
                        </div>
                        <div className="truncate">
                          <div className="font-medium text-zinc-900 text-xs flex items-center gap-1">
                            {u.name}
                            {currentUser?.id === u.id && (
                              <span className="text-[9px] font-semibold uppercase px-1 rounded bg-zinc-100 text-zinc-700 border border-zinc-200">
                                You
                              </span>
                            )}
                          </div>
                          <div className="text-[11px] text-zinc-400 truncate">{u.email}</div>
                        </div>
                      </div>
                    </TableCell>

                    {/* Role Type */}
                    <TableCell>
                      <span className="text-[10px] px-1.5 py-0.5 rounded font-medium bg-zinc-100 text-zinc-800 border border-zinc-200">
                        {u.role_type}
                      </span>
                    </TableCell>

                    {/* Scope */}
                    <TableCell>
                      <span className="text-[10px] px-1.5 py-0.5 rounded font-mono uppercase bg-zinc-50 text-zinc-600 border border-zinc-200">
                        {u.scope}
                      </span>
                    </TableCell>

                    {/* Level in Separate Column */}
                    <TableCell className="text-center">
                      <span className="text-[10px] px-1.5 py-0.5 rounded font-bold font-mono bg-zinc-900 text-white">
                        {u.level}
                      </span>
                    </TableCell>

                    {/* Department & Location */}
                    <TableCell>
                      <div className="text-xs">
                        <div className="font-normal text-zinc-800">{u.designation || u.department}</div>
                        <div className="text-[10px] text-zinc-400 flex items-center gap-0.5 mt-0.5">
                          <MapPin className="w-2.5 h-2.5 text-zinc-400" />
                          {u.branch || 'Delhi Central Hub'}
                        </div>
                      </div>
                    </TableCell>

                    {/* Reporting Manager */}
                    <TableCell>
                      <div className="text-xs">
                        {u.reporting_manager_name ? (
                          <span className="text-zinc-700 font-normal">
                            {u.reporting_manager_name}
                          </span>
                        ) : (
                          <span className="text-zinc-400 text-[11px] italic">Executive Board</span>
                        )}
                      </div>
                    </TableCell>

                    {/* Actions */}
                    <TableCell className="text-right">
                      <div className="flex items-center justify-end space-x-1.5">
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => openEditModal(u)}
                          className="h-6.5 px-2 text-[11px] text-zinc-700 hover:text-zinc-950"
                        >
                          <Edit2 className="w-3 h-3 mr-1 text-zinc-400" />
                          Edit
                        </Button>

                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => {
                            setUserToDelete(u)
                            setIsDeleteModalOpen(true)
                          }}
                          disabled={currentUser?.id === u.id || u.id === 1}
                          title={
                            currentUser?.id === u.id
                              ? 'Cannot delete your own active account'
                              : u.id === 1
                              ? 'Primary administrator cannot be deleted'
                              : 'Delete user'
                          }
                          className="h-6.5 px-2 text-[11px] text-zinc-500 hover:text-rose-600 hover:border-rose-200 disabled:opacity-40 disabled:hover:text-zinc-500 cursor-pointer"
                        >
                          <Trash2 className="w-3 h-3 mr-1 text-zinc-400" />
                          Delete
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </div>
      </Card>

      {/* User Provisioning / Edit Modal Dialog */}
      <Dialog open={isModalOpen} onClose={() => setIsModalOpen(false)} maxWidth="max-w-2xl">
        <form onSubmit={handleSubmit} className="space-y-4">
          <DialogHeader>
            <DialogTitle className="text-base font-semibold text-zinc-900">
              {editingUser ? `Edit User: ${editingUser.name}` : 'Provision New User'}
            </DialogTitle>
            <DialogDescription className="text-xs text-zinc-500">
              Enter user coordinates and assign the 3D decoupled role tuple (Role Type + Scope + Level).
            </DialogDescription>
          </DialogHeader>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[65vh] overflow-y-auto px-1 py-1">
            {/* Full Name */}
            <div className="space-y-1">
              <Label htmlFor="u_name" className="text-xs font-medium text-zinc-700">Full Name *</Label>
              <Input
                id="u_name"
                required
                value={name}
                onChange={(e) => setName(e.target.value)}
                placeholder="e.g. Elena Rostova"
                className="h-8 text-xs bg-zinc-50/50 border-zinc-200"
              />
            </div>

            {/* Email */}
            <div className="space-y-1">
              <Label htmlFor="u_email" className="text-xs font-medium text-zinc-700">Email Address *</Label>
              <Input
                id="u_email"
                type="email"
                required
                disabled={!!editingUser}
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="e.g. elena@avoerp.com"
                className={`h-8 text-xs bg-zinc-50/50 border-zinc-200 ${
                  email && !isValidEmail(email) ? 'border-red-500 focus:ring-red-400' : ''
                }`}
              />
              {!editingUser && email && getEmailError(email) && (
                <p className="text-[10px] text-red-600 font-medium">{getEmailError(email)}</p>
              )}
            </div>

            {/* Password */}
            <div className="space-y-1">
              <Label htmlFor="u_pass" className="text-xs font-medium text-zinc-700">
                {editingUser ? 'Password (Blank = Keep Current)' : 'Initial Password *'}
              </Label>
              <Input
                id="u_pass"
                type="password"
                required={!editingUser}
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder={editingUser ? '••••••••' : 'Enter password'}
                className="h-8 text-xs bg-zinc-50/50 border-zinc-200"
              />
            </div>

            {/* Designation */}
            <div className="space-y-1">
              <Label htmlFor="u_desig" className="text-xs font-medium text-zinc-700">Designation / Title</Label>
              <Input
                id="u_desig"
                value={designation}
                onChange={(e) => setDesignation(e.target.value)}
                placeholder="e.g. Senior Bidding Engineer"
                className="h-8 text-xs bg-zinc-50/50 border-zinc-200"
              />
            </div>

            {/* 3D Tuple Section */}
            <div className="col-span-1 md:col-span-2 pt-2 border-t border-zinc-200">
              <h4 className="text-[11px] font-semibold uppercase tracking-wider text-zinc-900 flex items-center gap-1">
                <Shield className="w-3 h-3" />
                3D Decoupled Tuple (SS6 Access Model)
              </h4>
            </div>

            {/* Role Type */}
            <div className="space-y-1">
              <Label htmlFor="u_role_type" className="text-xs font-medium text-zinc-700">Role Type</Label>
              <select
                id="u_role_type"
                value={roleType}
                onChange={(e) => setRoleType(e.target.value as RoleType)}
                className="w-full h-8 px-2.5 text-xs bg-white border border-zinc-200 rounded-md text-zinc-800 focus:outline-hidden focus:border-zinc-400"
              >
                {ROLE_TYPES.map((rt) => (
                  <option key={rt} value={rt}>
                    {rt}
                  </option>
                ))}
              </select>
            </div>

            {/* Data Scope */}
            <div className="space-y-1">
              <Label htmlFor="u_scope" className="text-xs font-medium text-zinc-700">Data Scope</Label>
              <select
                id="u_scope"
                value={scope}
                onChange={(e) => setScope(e.target.value as ScopeType)}
                className="w-full h-8 px-2.5 text-xs bg-white border border-zinc-200 rounded-md text-zinc-800 focus:outline-hidden focus:border-zinc-400"
              >
                {SCOPES.map((sc) => (
                  <option key={sc.value} value={sc.value}>
                    {sc.label}
                  </option>
                ))}
              </select>
            </div>

            {/* Seniority Level */}
            <div className="space-y-1">
              <Label htmlFor="u_level" className="text-xs font-medium text-zinc-700">Seniority Level</Label>
              <select
                id="u_level"
                value={level}
                onChange={(e) => setLevel(e.target.value as LevelType)}
                className="w-full h-8 px-2.5 text-xs bg-white border border-zinc-200 rounded-md text-zinc-800 focus:outline-hidden focus:border-zinc-400"
              >
                {LEVELS.map((lv) => (
                  <option key={lv.value} value={lv.value}>
                    {lv.label}
                  </option>
                ))}
              </select>
            </div>

            {/* Reporting Manager */}
            <div className="space-y-1">
              <Label htmlFor="u_manager" className="text-xs font-medium text-zinc-700">Reporting Manager</Label>
              <select
                id="u_manager"
                value={reportingManagerId || ''}
                onChange={(e) => setReportingManagerId(e.target.value ? Number(e.target.value) : undefined)}
                className="w-full h-8 px-2.5 text-xs bg-white border border-zinc-200 rounded-md text-zinc-800 focus:outline-hidden focus:border-zinc-400"
              >
                <option value="">None (Reports to Board)</option>
                {users
                  .filter((u: User) => !editingUser || u.id !== editingUser.id)
                  .map((mgr: User) => (
                    <option key={mgr.id} value={mgr.id}>
                      {mgr.name} ({mgr.level})
                    </option>
                  ))}
              </select>
            </div>

            {/* Department */}
            <div className="space-y-1">
              <Label htmlFor="u_dept" className="text-xs font-medium text-zinc-700">Department</Label>
              <Input
                id="u_dept"
                value={department}
                onChange={(e) => setDepartment(e.target.value)}
                placeholder="e.g. Commercial Sales"
                className="h-8 text-xs bg-zinc-50/50 border-zinc-200"
              />
            </div>

            {/* Branch Facility */}
            <div className="space-y-1">
              <Label htmlFor="u_branch" className="text-xs font-medium text-zinc-700">Branch / Hub</Label>
              <Input
                id="u_branch"
                value={branch}
                onChange={(e) => setBranch(e.target.value)}
                placeholder="e.g. Delhi Central Hub"
                className="h-8 text-xs bg-zinc-50/50 border-zinc-200"
              />
            </div>
          </div>

          <DialogFooter className="pt-2.5 border-t border-zinc-200">
            <Button
              type="button"
              variant="outline"
              size="sm"
              onClick={() => setIsModalOpen(false)}
            >
              Cancel
            </Button>
            <Button
              type="submit"
              size="sm"
              disabled={saveMutation.isPending || (!editingUser && !isValidEmail(email)) || !name.trim()}
              className="bg-zinc-900 hover:bg-black text-white"
            >
              {saveMutation.isPending ? 'Saving...' : editingUser ? 'Update User' : 'Create User'}
            </Button>
          </DialogFooter>
        </form>
      </Dialog>

      {/* Delete Confirmation Modal */}
      {userToDelete && (
        <Dialog open={isDeleteModalOpen} onClose={() => setIsDeleteModalOpen(false)} maxWidth="max-w-md">
          <div className="space-y-3.5">
            <DialogHeader>
              <div className="flex items-center space-x-2 text-rose-600">
                <div className="w-7 h-7 rounded-full bg-rose-50 border border-rose-200 flex items-center justify-center shrink-0">
                  <Trash2 className="w-4 h-4 text-rose-600" />
                </div>
                <DialogTitle className="text-sm font-semibold text-zinc-900">
                  Delete User Account
                </DialogTitle>
              </div>
              <DialogDescription className="text-xs text-zinc-500 mt-1">
                Are you sure you want to permanently delete this user? This will revoke all their system access and remove their account from the database.
              </DialogDescription>
            </DialogHeader>

            <div className="p-3 rounded-md bg-zinc-50 border border-zinc-200 text-xs space-y-1.5 font-sans">
              <div className="flex justify-between">
                <span className="text-zinc-500">Name:</span>
                <span className="font-semibold text-zinc-900">{userToDelete.name}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-zinc-500">Email:</span>
                <span className="font-mono text-zinc-800">{userToDelete.email}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-zinc-500">Access Tuple:</span>
                <span className="font-medium text-zinc-800">
                  {userToDelete.role_type} &bull; {userToDelete.scope} &bull; {userToDelete.level}
                </span>
              </div>
              {userToDelete.department && (
                <div className="flex justify-between">
                  <span className="text-zinc-500">Department:</span>
                  <span className="text-zinc-800">{userToDelete.department}</span>
                </div>
              )}
            </div>

            <DialogFooter className="flex justify-end gap-2 pt-2 border-t border-zinc-100">
              <Button
                variant="outline"
                size="sm"
                onClick={() => {
                  setIsDeleteModalOpen(false)
                  setUserToDelete(null)
                }}
                className="h-8 text-xs text-zinc-700"
              >
                Cancel
              </Button>
              <Button
                size="sm"
                onClick={() => deleteMutation.mutate(userToDelete.id)}
                disabled={deleteMutation.isPending}
                className="h-8 text-xs bg-rose-600 hover:bg-rose-700 text-white"
              >
                {deleteMutation.isPending ? 'Deleting...' : 'Confirm Deletion'}
              </Button>
            </DialogFooter>
          </div>
        </Dialog>
      )}
    </div>
  )
}
