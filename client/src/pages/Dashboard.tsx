import { useQuery } from '@tanstack/react-query'
import {
  DollarSign,
  Clock,
  AlertTriangle,
  ShoppingBag,
  TrendingUp,
  ArrowUpRight,
  Package,
} from 'lucide-react'
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  Tooltip,
  ResponsiveContainer,
  PieChart,
  Pie,
  Cell,
  Legend,
} from 'recharts'
import { dashboardApi } from '@/api/services'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { formatCurrency, formatDate } from '@/lib/utils'
import { Link } from 'react-router-dom'

const STATUS_COLORS: Record<string, string> = {
  draft: '#94a3b8',
  pending_approval: '#f59e0b',
  approved: '#3b82f6',
  invoiced: '#10b981',
  rejected: '#ef4444',
}

export function Dashboard() {
  const { data, isLoading, error } = useQuery({
    queryKey: ['dashboard-metrics'],
    queryFn: dashboardApi.getMetrics,
    refetchInterval: 15000,
  })

  if (isLoading) {
    return (
      <div className="flex h-96 items-center justify-center">
        <div className="text-center">
          <div className="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
          <p className="text-sm text-slate-500">Loading Enterprise Analytics...</p>
        </div>
      </div>
    )
  }

  if (error || !data) {
    return (
      <div className="p-6 bg-rose-50 border border-rose-200 rounded-xl text-rose-800">
        Failed to fetch dashboard metrics. Please check server connectivity.
      </div>
    )
  }

  const { kpis, status_distribution, category_breakdown, low_stock_items, recent_invoices } = data

  const pieData = status_distribution.map((s) => ({
    name: s.status.replace('_', ' ').toUpperCase(),
    value: Number(s.count),
    color: STATUS_COLORS[s.status] || '#64748b',
  }))

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight text-slate-900">Executive Dashboard</h1>
          <p className="text-sm text-slate-500">Real-time overview of financial performance, stock, and workflows</p>
        </div>
        <div className="flex items-center space-x-2">
          <Link
            to="/orders/new"
            className="inline-flex items-center justify-center h-9 px-4 text-xs font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-sm transition-colors"
          >
            + Create New Sales Order
          </Link>
        </div>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between pb-2">
            <CardTitle className="text-xs font-medium uppercase text-slate-500">Total Invoiced Revenue</CardTitle>
            <div className="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <DollarSign className="w-4 h-4" />
            </div>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-slate-900">{formatCurrency(kpis.total_revenue)}</div>
            <p className="text-xs text-slate-500 mt-1 flex items-center">
              <TrendingUp className="w-3.5 h-3.5 text-emerald-500 mr-1" />
              Authoritative billed amount
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between pb-2">
            <CardTitle className="text-xs font-medium uppercase text-slate-500">Pending Approvals</CardTitle>
            <div className="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
              <Clock className="w-4 h-4" />
            </div>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-slate-900">{kpis.pending_approvals}</div>
            <p className="text-xs text-slate-500 mt-1">Orders requiring manager sign-off</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between pb-2">
            <CardTitle className="text-xs font-medium uppercase text-slate-500">Low Stock Alerts</CardTitle>
            <div className="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
              <AlertTriangle className="w-4 h-4" />
            </div>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-rose-600">{kpis.low_stock_count}</div>
            <p className="text-xs text-slate-500 mt-1">Items at or below reorder threshold</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between pb-2">
            <CardTitle className="text-xs font-medium uppercase text-slate-500">Total Sales Orders</CardTitle>
            <div className="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
              <ShoppingBag className="w-4 h-4" />
            </div>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-slate-900">{kpis.total_orders}</div>
            <p className="text-xs text-slate-500 mt-1">Recorded in ERP pipeline</p>
          </CardContent>
        </Card>
      </div>

      {/* Visual Analytics Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Category Inventory Breakdown */}
        <Card className="lg:col-span-2">
          <CardHeader>
            <CardTitle>Inventory Valuation by Category</CardTitle>
            <CardDescription>Total valuation (cost price × stock quantity) across warehouse categories</CardDescription>
          </CardHeader>
          <CardContent className="h-72">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={category_breakdown}>
                <XAxis dataKey="category" tick={{ fontSize: 11 }} />
                <YAxis tickFormatter={(val) => `$${val}`} tick={{ fontSize: 11 }} />
                <Tooltip
                  formatter={(val: any) => [formatCurrency(Number(val)), 'Inventory Value']}
                  labelStyle={{ fontWeight: 'bold' }}
                />
                <Bar dataKey="inventory_value" fill="#3b82f6" radius={[6, 6, 0, 0]} name="Value ($)" />
              </BarChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>

        {/* Order Status Distribution */}
        <Card>
          <CardHeader>
            <CardTitle>Workflow Distribution</CardTitle>
            <CardDescription>Orders grouped by current workflow state</CardDescription>
          </CardHeader>
          <CardContent className="h-72 flex flex-col items-center justify-center">
            {pieData.length > 0 ? (
              <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                  <Pie
                    data={pieData}
                    cx="50%"
                    cy="45%"
                    innerRadius={50}
                    outerRadius={80}
                    paddingAngle={4}
                    dataKey="value"
                  >
                    {pieData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.color} />
                    ))}
                  </Pie>
                  <Tooltip />
                  <Legend verticalAlign="bottom" height={36} wrapperStyle={{ fontSize: '11px' }} />
                </PieChart>
              </ResponsiveContainer>
            ) : (
              <p className="text-sm text-slate-400">No order data yet</p>
            )}
          </CardContent>
        </Card>
      </div>

      {/* Tables Row: Low Stock Alert & Recent Invoices */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Low Stock Alerts */}
        <Card>
          <CardHeader className="flex flex-row items-center justify-between">
            <div>
              <CardTitle className="text-base flex items-center text-rose-700">
                <AlertTriangle className="w-4 h-4 mr-2" />
                Critical Low Stock Notice
              </CardTitle>
              <CardDescription>Items that require replenishment immediately</CardDescription>
            </div>
            <Link to="/products" className="text-xs text-blue-600 hover:underline flex items-center">
              Manage Inventory <ArrowUpRight className="w-3.5 h-3.5 ml-0.5" />
            </Link>
          </CardHeader>
          <CardContent>
            {low_stock_items.length === 0 ? (
              <p className="text-sm text-slate-500 py-4 text-center">All inventory stock levels are healthy.</p>
            ) : (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>SKU</TableHead>
                    <TableHead>Product</TableHead>
                    <TableHead>Current</TableHead>
                    <TableHead>Reorder Level</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {low_stock_items.map((item: any) => (
                    <TableRow key={item.id}>
                      <TableCell className="font-mono text-xs">{item.sku}</TableCell>
                      <TableCell className="font-medium text-slate-900">{item.name}</TableCell>
                      <TableCell>
                        <Badge variant="destructive">
                          {item.stock_quantity} {item.unit}
                        </Badge>
                      </TableCell>
                      <TableCell className="text-slate-500">
                        {item.reorder_level} {item.unit}
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            )}
          </CardContent>
        </Card>

        {/* Recent Invoices */}
        <Card>
          <CardHeader className="flex flex-row items-center justify-between">
            <div>
              <CardTitle className="text-base flex items-center text-slate-900">
                <Package className="w-4 h-4 mr-2 text-blue-600" />
                Recent Authoritative Invoices
              </CardTitle>
              <CardDescription>Latest finalized financial invoices</CardDescription>
            </div>
            <Link to="/invoices" className="text-xs text-blue-600 hover:underline flex items-center">
              All Invoices <ArrowUpRight className="w-3.5 h-3.5 ml-0.5" />
            </Link>
          </CardHeader>
          <CardContent>
            {recent_invoices.length === 0 ? (
              <p className="text-sm text-slate-500 py-4 text-center">No invoices generated yet.</p>
            ) : (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Invoice #</TableHead>
                    <TableHead>Customer</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead>Status</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {recent_invoices.map((inv: any) => (
                    <TableRow key={inv.id}>
                      <TableCell className="font-mono text-xs font-semibold text-blue-600">
                        {inv.invoice_number}
                      </TableCell>
                      <TableCell className="text-slate-800">{inv.customer_name}</TableCell>
                      <TableCell className="font-medium">{formatCurrency(inv.total_amount)}</TableCell>
                      <TableCell>
                        <Badge variant={inv.status === 'paid' ? 'success' : 'warning'}>
                          {inv.status.toUpperCase()}
                        </Badge>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
