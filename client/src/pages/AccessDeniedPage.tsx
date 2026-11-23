import { Link } from 'react-router-dom'
import { ShieldAlert, ArrowLeft } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { useAuthStore } from '@/store/authStore'

interface AccessDeniedProps {
  moduleName?: string
  requiredRole?: string
}

export function AccessDeniedPage({
  moduleName = 'this restricted module',
  requiredRole = 'Authorized Personnel',
}: AccessDeniedProps) {
  const { user } = useAuthStore()

  const defaultLandingPath =
    user?.role === 'admin' || user?.role_type === 'Admin' || user?.role_type === 'Executive' || user?.level === 'L3'
      ? '/users'
      : ['Sales', 'Finance', 'Project'].includes(user?.role_type || '')
      ? '/sales/leads'
      : '/dashboard'

  return (
    <div className="min-h-[70vh] flex flex-col items-center justify-center p-6 text-center select-none">
      <div className="w-14 h-14 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 mb-3.5 shadow-xs">
        <ShieldAlert className="w-7 h-7" />
      </div>

      <span className="text-[10px] font-mono font-bold tracking-widest uppercase text-rose-700 bg-rose-100/70 px-2.5 py-0.5 rounded-full border border-rose-200 mb-2">
        HTTP 403 &bull; Access Forbidden
      </span>

      <h1 className="text-lg font-bold text-zinc-900 tracking-tight mb-1.5">
        Access Denied &bull; Insufficient Permissions
      </h1>

      <p className="text-xs text-zinc-600 max-w-md mb-3 leading-relaxed">
        Your assigned role{' '}
        <strong className="font-semibold text-zinc-900">{user?.designation || user?.role_type || 'User'}</strong> (
        <span className="font-mono text-zinc-700">
          Archetype: {user?.role_type} &bull; Level: {user?.level || 'L1'} &bull; Scope: {user?.scope || 'branch'}
        </span>
        ) is not authorized to view or perform actions in <strong>{moduleName}</strong>.
      </p>

      {requiredRole && (
        <p className="text-[11px] text-zinc-500 mb-5 bg-zinc-100 px-3 py-1.5 rounded-md border border-zinc-200 font-mono">
          Authorized Archetypes: <strong className="text-zinc-800">{requiredRole}</strong>
        </p>
      )}

      <div className="flex items-center gap-3">
        <Link to={defaultLandingPath}>
          <Button size="sm" className="h-8 text-xs bg-zinc-900 hover:bg-zinc-800 text-white font-medium">
            <ArrowLeft className="w-3.5 h-3.5 mr-1.5" />
            Go to My Authorized Workspace
          </Button>
        </Link>
      </div>
    </div>
  )
}
