import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { Plus, Search, Building2, Mail, Phone, CheckCircle2, AlertCircle } from 'lucide-react'
import { customerApi } from '@/api/services'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { PaginationControls } from '@/components/ui/pagination'
import { Dialog, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { formatCurrency } from '@/lib/utils'
import { isValidPhone } from '@/lib/validation'
import type { Customer, PaginatedResult } from '@/types'

const customerSchema = z.object({
  name: z.string().min(2, 'Customer name is required'),
  company: z.string().optional(),
  email: z.string().email('Valid email is required'),
  phone: z
    .string()
    .optional()
    .refine((val) => !val || isValidPhone(val), {
      message: 'Valid 10-15 digit phone is required (e.g. +91 98200 12345)',
    }),
  tax_number: z.string().optional(),
  credit_limit: z.coerce.number().min(0, 'Credit limit must be positive'),
  address: z.string().optional(),
})

type CustomerFormValues = z.infer<typeof customerSchema>

export function Customers() {
  const queryClient = useQueryClient()
  const [page, setPage] = useState(1)
  const [limit, setLimit] = useState(10)
  const [search, setSearch] = useState('')
  const [isAddOpen, setIsAddOpen] = useState(false)
  const [feedback, setFeedback] = useState<{ type: 'success' | 'error'; message: string } | null>(null)

  const { data, isLoading } = useQuery({
    queryKey: ['customers', page, limit, search],
    queryFn: () => customerApi.getAll({ page, limit, search }) as Promise<PaginatedResult<Customer>>,
  })

  const createMutation = useMutation({
    mutationFn: customerApi.create,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['customers'] })
      setIsAddOpen(false)
      reset()
      setFeedback({ type: 'success', message: 'Customer registered successfully!' })
      setTimeout(() => setFeedback(null), 4000)
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.message || 'Failed to create customer' })
    },
  })

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<CustomerFormValues>({
    resolver: zodResolver(customerSchema) as any,
    defaultValues: {
      credit_limit: 10000,
    },
  })

  const onAddSubmit = (values: CustomerFormValues) => {
    createMutation.mutate(values)
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight text-slate-900">Customer Management</h1>
          <p className="text-sm text-slate-500">
            Account receivables, credit limit business validation, and contact records
          </p>
        </div>
        <Button onClick={() => setIsAddOpen(true)}>
          <Plus className="w-4 h-4 mr-2" />
          Add Customer
        </Button>
      </div>

      {feedback && (
        <div
          className={`p-3 rounded-lg text-xs flex items-center ${
            feedback.type === 'success'
              ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
              : 'bg-rose-50 text-rose-800 border border-rose-200'
          }`}
        >
          {feedback.type === 'success' ? (
            <CheckCircle2 className="w-4 h-4 mr-2 shrink-0" />
          ) : (
            <AlertCircle className="w-4 h-4 mr-2 shrink-0" />
          )}
          {feedback.message}
        </div>
      )}

      {/* Search Toolbar */}
      <Card>
        <CardContent className="p-4 flex items-center justify-between">
          <div className="relative w-full sm:w-80">
            <Search className="w-4 h-4 absolute left-3 top-3 text-slate-400" />
            <Input
              type="text"
              placeholder="Search by customer, company, or email..."
              className="pl-9"
              value={search}
              onChange={(e) => {
                setSearch(e.target.value)
                setPage(1)
              }}
            />
          </div>
        </CardContent>
      </Card>

      {/* Customer Table */}
      <Card>
        <CardContent className="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Customer / Company</TableHead>
                <TableHead>Contact Information</TableHead>
                <TableHead>Tax Number / EIN</TableHead>
                <TableHead>Credit Limit</TableHead>
                <TableHead>Outstanding Balance</TableHead>
                <TableHead>Credit Utilization</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {isLoading ? (
                <TableRow>
                  <TableCell colSpan={6} className="h-32 text-center text-slate-500">
                    Loading customer accounts...
                  </TableCell>
                </TableRow>
              ) : !data || data.data.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={6} className="h-32 text-center text-slate-500">
                    No customers found.
                  </TableCell>
                </TableRow>
              ) : (
                data.data.map((cust) => {
                  const utilPercent =
                    cust.credit_limit > 0
                      ? Math.min(100, Math.round((cust.outstanding_balance / cust.credit_limit) * 100))
                      : 0

                  return (
                    <TableRow key={cust.id}>
                      <TableCell>
                        <div className="font-semibold text-slate-900">{cust.name}</div>
                        {cust.company && (
                          <div className="text-xs text-slate-500 flex items-center mt-0.5">
                            <Building2 className="w-3 h-3 mr-1" />
                            {cust.company}
                          </div>
                        )}
                      </TableCell>
                      <TableCell>
                        <div className="text-xs text-slate-700 flex items-center">
                          <Mail className="w-3 h-3 mr-1 text-slate-400" />
                          {cust.email}
                        </div>
                        {cust.phone && (
                          <div className="text-xs text-slate-500 flex items-center mt-0.5">
                            <Phone className="w-3 h-3 mr-1 text-slate-400" />
                            {cust.phone}
                          </div>
                        )}
                      </TableCell>
                      <TableCell className="font-mono text-xs">{cust.tax_number || '-'}</TableCell>
                      <TableCell className="font-semibold text-slate-900">
                        {formatCurrency(cust.credit_limit)}
                      </TableCell>
                      <TableCell className="font-semibold text-slate-700">
                        {formatCurrency(cust.outstanding_balance)}
                      </TableCell>
                      <TableCell>
                        <div className="w-32">
                          <div className="flex justify-between text-xs text-slate-500 mb-1">
                            <span>{utilPercent}%</span>
                            <span>{formatCurrency(cust.credit_limit - cust.outstanding_balance)} left</span>
                          </div>
                          <div className="h-1.5 w-full bg-slate-200 rounded-full overflow-hidden">
                            <div
                              className={`h-full rounded-full transition-all ${
                                utilPercent > 80
                                  ? 'bg-rose-500'
                                  : utilPercent > 50
                                  ? 'bg-amber-500'
                                  : 'bg-emerald-500'
                              }`}
                              style={{ width: `${utilPercent}%` }}
                            />
                          </div>
                        </div>
                      </TableCell>
                    </TableRow>
                  )
                })
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

      {/* Add Customer Modal */}
      <Dialog open={isAddOpen} onClose={() => setIsAddOpen(false)} maxWidth="max-w-lg">
        <DialogHeader>
          <DialogTitle>Register New Customer</DialogTitle>
          <DialogDescription>Add a commercial client with credit limit and billing data</DialogDescription>
        </DialogHeader>
        <form onSubmit={handleSubmit(onAddSubmit)} className="space-y-4 py-4">
          <div className="space-y-1">
            <Label>Customer / Contact Name</Label>
            <Input placeholder="e.g. John Doe" error={errors.name?.message} {...register('name')} />
          </div>

          <div className="space-y-1">
            <Label>Company / Organization Name</Label>
            <Input placeholder="e.g. Acme Industries Ltd" {...register('company')} />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-1">
              <Label>Email</Label>
              <Input type="email" placeholder="client@domain.com" error={errors.email?.message} {...register('email')} />
            </div>
            <div className="space-y-1">
              <Label>Phone</Label>
              <Input placeholder="e.g. +91 98200 12345" error={errors.phone?.message} {...register('phone')} />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-1">
              <Label>Tax ID / EIN</Label>
              <Input placeholder="US-EIN-..." {...register('tax_number')} />
            </div>
            <div className="space-y-1">
              <Label>Credit Limit ($)</Label>
              <Input type="number" error={errors.credit_limit?.message} {...register('credit_limit')} />
            </div>
          </div>

          <div className="space-y-1">
            <Label>Billing Address</Label>
            <Input placeholder="Street, City, State, ZIP" {...register('address')} />
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" onClick={() => setIsAddOpen(false)}>
              Cancel
            </Button>
            <Button type="submit" isLoading={createMutation.isPending}>
              Create Customer
            </Button>
          </DialogFooter>
        </form>
      </Dialog>
    </div>
  )
}
