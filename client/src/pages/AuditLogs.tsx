import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { History, Search, User, Shield, Terminal, Clock, Eye } from 'lucide-react'
import { auditLogApi } from '@/api/services'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { PaginationControls } from '@/components/ui/pagination'
import { Dialog, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { formatDate } from '@/lib/utils'
import type { AuditLog, PaginatedResult } from '@/types'

const ACTION_COLORS: Record<string, 'default' | 'success' | 'warning' | 'destructive' | 'info'> = {
  LOGIN: 'info',
  CREATE: 'success',
  CREATE_ORDER: 'success',
  APPROVE_ORDER: 'default',
  REJECT_ORDER: 'destructive',
  STOCK_ADJUSTMENT: 'warning',
  GENERATE_INVOICE: 'default',
  PAY_INVOICE: 'success',
}

export function AuditLogs() {
  const [page, setPage] = useState(1)
  const [limit, setLimit] = useState(15)
  const [entityType, setEntityType] = useState('')
  const [search, setSearch] = useState('')

  const [selectedLog, setSelectedLog] = useState<AuditLog | null>(null)

  const { data, isLoading } = useQuery({
    queryKey: ['audit-logs', page, limit, entityType, search],
    queryFn: () => auditLogApi.getAll({ page, limit, entity_type: entityType, search }) as Promise<PaginatedResult<AuditLog>>,
  })

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight text-slate-900">Immutable Audit Trail</h1>
          <p className="text-sm text-slate-500">
            Every business mutation is automatically recorded by the PHP backend with user identity and state diffs
          </p>
        </div>
      </div>

      {/* Filter Toolbar */}
      <Card>
        <CardContent className="p-4 flex flex-col sm:flex-row gap-4 items-center justify-between">
          <div className="relative w-full sm:w-80">
            <Search className="w-4 h-4 absolute left-3 top-3 text-slate-400" />
            <Input
              type="text"
              placeholder="Search by User, Action, or Entity ID..."
              className="pl-9"
              value={search}
              onChange={(e) => {
                setSearch(e.target.value)
                setPage(1)
              }}
            />
          </div>

          <div className="flex items-center space-x-2">
            <span className="text-xs text-slate-500 font-medium">Entity Type:</span>
            <select
              value={entityType}
              onChange={(e) => {
                setEntityType(e.target.value)
                setPage(1)
              }}
              className="text-xs border border-slate-300 rounded px-2.5 py-1.5 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium"
            >
              <option value="">All Entities</option>
              <option value="Order">Order</option>
              <option value="Product">Product</option>
              <option value="Invoice">Invoice</option>
              <option value="Customer">Customer</option>
              <option value="User">User</option>
            </select>
          </div>
        </CardContent>
      </Card>

      {/* Audit Logs Table */}
      <Card>
        <CardContent className="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Timestamp</TableHead>
                <TableHead>Actor / Role</TableHead>
                <TableHead>Action</TableHead>
                <TableHead>Target Entity</TableHead>
                <TableHead>IP Address</TableHead>
                <TableHead className="text-right">State Diff</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {isLoading ? (
                <TableRow>
                  <TableCell colSpan={6} className="h-32 text-center text-slate-500">
                    Loading audit trail from database...
                  </TableCell>
                </TableRow>
              ) : !data || data.data.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={6} className="h-32 text-center text-slate-500">
                    No audit records recorded yet.
                  </TableCell>
                </TableRow>
              ) : (
                data.data.map((log) => (
                  <TableRow key={log.id}>
                    <TableCell className="text-xs text-slate-500 font-medium">
                      {formatDate(log.created_at)}
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center space-x-1.5">
                        <span className="font-semibold text-slate-900 text-xs">{log.user_name}</span>
                        <Badge variant="outline" className="text-[10px] px-1 py-0 uppercase">
                          {log.user_role}
                        </Badge>
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge variant={ACTION_COLORS[log.action] || 'secondary'} className="text-[11px]">
                        {log.action}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <span className="font-mono text-xs bg-slate-100 px-2 py-0.5 rounded text-slate-800">
                        {log.entity_type} #{log.entity_id}
                      </span>
                    </TableCell>
                    <TableCell className="font-mono text-xs text-slate-400">{log.ip_address}</TableCell>
                    <TableCell className="text-right">
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => setSelectedLog(log)}
                      >
                        <Eye className="w-3.5 h-3.5 mr-1" />
                        Inspect Diff
                      </Button>
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>

          {data && (
            <PaginationControls
              currentPage={data.pagination.page}
              totalPages={data.pagination.total_pages}
              totalItems={data.pagination.total}
              limit={data.pagination.limit}
              onPageChange={setPage}
              onLimitChange={(l) => {
                setLimit(l)
                setPage(1)
              }}
            />
          )}
        </CardContent>
      </Card>

      {/* Inspect Audit Log Diff Modal */}
      <Dialog open={!!selectedLog} onClose={() => setSelectedLog(null)} maxWidth="max-w-2xl">
        <DialogHeader>
          <div className="flex items-center space-x-2">
            <DialogTitle>Audit Record #{selectedLog?.id}</DialogTitle>
            {selectedLog && (
              <Badge variant={ACTION_COLORS[selectedLog.action] || 'secondary'}>
                {selectedLog.action}
              </Badge>
            )}
          </div>
          <DialogDescription>
            Performed by <span className="font-semibold text-slate-900">{selectedLog?.user_name}</span> ({selectedLog?.user_role}) on {selectedLog && formatDate(selectedLog.created_at)}
          </DialogDescription>
        </DialogHeader>

        {selectedLog && (
          <div className="py-4 space-y-4 text-xs">
            <div className="grid grid-cols-2 gap-4 p-3 bg-slate-50 rounded-lg">
              <div>
                <span className="text-slate-400 font-semibold uppercase">Target Entity:</span>
                <p className="font-bold text-slate-900 text-sm mt-0.5">
                  {selectedLog.entity_type} #{selectedLog.entity_id}
                </p>
              </div>
              <div>
                <span className="text-slate-400 font-semibold uppercase">Client IP Address:</span>
                <p className="font-mono text-slate-700 text-sm mt-0.5">{selectedLog.ip_address}</p>
              </div>
            </div>

            {selectedLog.old_values && (
              <div className="space-y-1">
                <span className="font-bold text-slate-700">Previous State (Old Values):</span>
                <pre className="p-3 bg-rose-50 border border-rose-200 rounded-lg text-rose-900 overflow-x-auto text-[11px]">
                  {JSON.stringify(JSON.parse(selectedLog.old_values), null, 2)}
                </pre>
              </div>
            )}

            {selectedLog.new_values && (
              <div className="space-y-1">
                <span className="font-bold text-slate-700">Updated State (New Values):</span>
                <pre className="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-900 overflow-x-auto text-[11px]">
                  {JSON.stringify(JSON.parse(selectedLog.new_values), null, 2)}
                </pre>
              </div>
            )}
          </div>
        )}

        <DialogFooter>
          <Button variant="outline" onClick={() => setSelectedLog(null)}>
            Close
          </Button>
        </DialogFooter>
      </Dialog>
    </div>
  )
}

