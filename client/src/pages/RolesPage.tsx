import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import {
  ShieldCheck,
  Users,
  CheckCircle2,
  Crown,
  FileSpreadsheet,
  TrendingUp,
  Wrench,
  Headset,
  Hammer,
  Warehouse,
  SunMedium,
  Shield,
} from 'lucide-react'
import { identityApi } from '@/api/services'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import type { ArchetypeRole, User } from '@/types'

export function RolesPage() {
  const [activeTab, setActiveTab] = useState<'role_types' | 'archetypes'>('role_types')

  // Queries
  const { data: rolesData, isLoading: rolesLoading } = useQuery({
    queryKey: ['identity-roles'],
    queryFn: identityApi.getRoles,
  })

  const { data: users = [] } = useQuery({
    queryKey: ['identity-users'],
    queryFn: identityApi.getUsers,
  })

  const roleTypes = rolesData?.role_types || {}
  const archetypes: ArchetypeRole[] = rolesData?.archetypes || []

  // Count active users per role type
  const userCounts = users.reduce((acc: Record<string, number>, u: User) => {
    acc[u.role_type] = (acc[u.role_type] || 0) + 1
    return acc
  }, {})

  const getRoleIcon = (key: string) => {
    switch (key) {
      case 'Executive':
        return Crown
      case 'Finance':
        return FileSpreadsheet
      case 'Sales':
        return TrendingUp
      case 'Service Field':
        return Wrench
      case 'Service Desk':
        return Headset
      case 'Repair':
        return Hammer
      case 'Operations/Warehouse':
        return Warehouse
      case 'Project':
        return SunMedium
      case 'Admin':
        return ShieldCheck
      default:
        return Users
    }
  }

  return (
    <div className="space-y-3.5 max-w-7xl mx-auto">
      {/* Page Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3.5 px-4 rounded-lg border border-zinc-200 shadow-2xs">
        <div>
          <h1 className="text-sm font-semibold tracking-tight text-zinc-900">Roles & Archetypes</h1>
          <p className="text-[11px] text-zinc-500 mt-0.5">
            10 Functional Core Role Types & 14 Enterprise Positions.
          </p>
        </div>

        {/* Minimal Tab Toggle */}
        <div className="flex bg-zinc-100 p-0.5 rounded-md border border-zinc-200 self-start sm:self-auto">
          <button
            onClick={() => setActiveTab('role_types')}
            className={`px-3 py-1 text-xs font-medium rounded transition-all cursor-pointer ${
              activeTab === 'role_types'
                ? 'bg-white text-zinc-900 shadow-2xs font-semibold'
                : 'text-zinc-600 hover:text-zinc-900'
            }`}
          >
            10 Role Types
          </button>
          <button
            onClick={() => setActiveTab('archetypes')}
            className={`px-3 py-1 text-xs font-medium rounded transition-all cursor-pointer ${
              activeTab === 'archetypes'
                ? 'bg-white text-zinc-900 shadow-2xs font-semibold'
                : 'text-zinc-600 hover:text-zinc-900'
            }`}
          >
            14 Archetypes
          </button>
        </div>
      </div>

      {/* View 1: 10 Core Role Types Grid */}
      {activeTab === 'role_types' && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          {Object.entries(roleTypes).map(([key, role]: [string, any]) => {
            const IconComponent = getRoleIcon(key)
            const count = userCounts[key] || 0

            return (
              <Card
                key={key}
                className="border-zinc-200 shadow-2xs hover:border-zinc-400 transition-all overflow-hidden flex flex-col justify-between"
              >
                <div>
                  <div className="p-3 bg-zinc-900 text-white flex items-center justify-between border-b border-zinc-800">
                    <div className="flex items-center space-x-2">
                      <div className="p-1 rounded bg-zinc-800 text-zinc-200">
                        <IconComponent className="w-3.5 h-3.5" />
                      </div>
                      <div>
                        <h3 className="font-semibold text-xs leading-none text-white">{role.name}</h3>
                        <span className="text-[10px] text-zinc-400 font-mono mt-0.5 block">{role.key}</span>
                      </div>
                    </div>
                    <span className="text-[10px] px-1.5 py-0.5 rounded font-mono font-medium bg-zinc-800 text-zinc-300 border border-zinc-700">
                      {count} {count === 1 ? 'user' : 'users'}
                    </span>
                  </div>

                  <CardContent className="p-3 space-y-2.5">
                    <p className="text-[11px] text-zinc-600 leading-relaxed min-h-8">{role.description}</p>

                    <div>
                      <div className="text-[9px] font-semibold text-zinc-400 uppercase tracking-wider mb-1.5">
                        Allowed Screens ({role.screens.length})
                      </div>
                      <div className="flex flex-wrap gap-1">
                        {role.screens.map((screen: string) => (
                          <span
                            key={screen}
                            className="text-[10px] px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-800 font-medium border border-zinc-200"
                          >
                            {screen}
                          </span>
                        ))}
                      </div>
                    </div>
                  </CardContent>
                </div>

                <div className="p-2 px-3 bg-zinc-50 border-t border-zinc-100 flex items-center justify-between text-[10px] text-zinc-500">
                  <span>Access Control</span>
                  <span className="font-medium text-zinc-800 flex items-center">
                    Enforced <CheckCircle2 className="w-3 h-3 ml-1 text-zinc-700" />
                  </span>
                </div>
              </Card>
            )
          })}
        </div>
      )}

      {/* View 2: 14 Organizational Archetypes Table */}
      {activeTab === 'archetypes' && (
        <Card className="border-zinc-200 shadow-2xs overflow-hidden">
          <div className="overflow-x-auto">
            <Table>
              <TableHeader className="bg-zinc-50/80 border-b border-zinc-200">
                <TableRow>
                  <TableHead className="font-semibold text-zinc-600 text-[11px] w-10 text-center">#</TableHead>
                  <TableHead className="font-semibold text-zinc-600 text-[11px]">Archetype Title</TableHead>
                  <TableHead className="font-semibold text-zinc-600 text-[11px]">Role Type</TableHead>
                  <TableHead className="font-semibold text-zinc-600 text-[11px]">Scope</TableHead>
                  <TableHead className="font-semibold text-zinc-600 text-[11px] text-center">Level</TableHead>
                  <TableHead className="font-semibold text-zinc-600 text-[11px]">Department</TableHead>
                  <TableHead className="font-semibold text-zinc-600 text-[11px]">Sample User</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {archetypes.map((arch: ArchetypeRole, idx: number) => {
                  return (
                    <TableRow key={idx} className="hover:bg-zinc-50/60 transition-colors">
                      <TableCell className="text-center font-mono text-[11px] text-zinc-400 w-10">
                        {idx + 1}
                      </TableCell>
                      <TableCell className="font-medium text-zinc-900 text-xs">
                        {arch.title}
                      </TableCell>

                      <TableCell>
                        <span className="text-[10px] px-1.5 py-0.5 rounded font-medium bg-zinc-100 text-zinc-800 border border-zinc-200">
                          {arch.role_type}
                        </span>
                      </TableCell>

                      <TableCell>
                        <span className="text-[10px] px-1.5 py-0.5 rounded font-mono uppercase bg-zinc-50 text-zinc-600 border border-zinc-200">
                          {arch.scope}
                        </span>
                      </TableCell>

                      <TableCell className="text-center">
                        <span className="text-[10px] px-1.5 py-0.5 rounded font-bold font-mono bg-zinc-900 text-white">
                          {arch.level}
                        </span>
                      </TableCell>

                      <TableCell className="text-zinc-600 text-xs">
                        {arch.department}
                      </TableCell>

                      <TableCell>
                        <div className="font-normal text-zinc-800 text-xs">{arch.sample_name}</div>
                        <div className="text-zinc-400 text-[10px]">{arch.email}</div>
                      </TableCell>
                    </TableRow>
                  )
                })}
              </TableBody>
            </Table>
          </div>
        </Card>
      )}
    </div>
  )
}
