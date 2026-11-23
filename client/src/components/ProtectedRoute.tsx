import type { ReactNode } from 'react'
import { Link } from 'react-router-dom'
import { ShieldAlert, ArrowLeft, Lock } from 'lucide-react'
import { useAuthStore } from '@/store/authStore'
import { Button } from '@/components/ui/button'

interface ProtectedRouteProps {
  children: ReactNode
  allow: () => boolean
  moduleName?: string
}

export function ProtectedRoute({ children, allow, moduleName = 'User Module' }: ProtectedRouteProps) {
  const { user } = useAuthStore()

  if (allow()) {
    return <>{children}</>
  }

  return (
    <div className="min-h-[70vh] flex flex-col items-center justify-center p-6 text-center">
      <div className="w-16 h-16 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center mb-4 shadow-xs">
        <ShieldAlert className="w-8 h-8" />
      </div>
      <h2 className="text-2xl font-bold text-slate-900 tracking-tight">403 - Permission Denied</h2>
      <p className="text-sm text-slate-600 max-w-md mt-2 leading-relaxed">
        Access to <strong className="text-slate-800">{moduleName}</strong> is restricted to{' '}
        <span className="font-semibold text-rose-600">System Administrators and Executive Directors</span>.
      </p>

      {user && (
        <div className="mt-4 p-3 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-600 max-w-sm">
          <div>
            Current User: <span className="font-semibold text-slate-800">{user.name}</span>
          </div>
          <div className="mt-1">
            Access Tuple:{' '}
            <span className="font-mono bg-white px-1.5 py-0.5 rounded border border-slate-300">
              {user.role_type} • {user.scope} • {user.level}
            </span>
          </div>
        </div>
      )}

      <div className="mt-6 flex items-center gap-3">
        <Link to="/">
          <Button variant="default" className="bg-slate-900 hover:bg-slate-800 text-white cursor-pointer">
            <ArrowLeft className="w-4 h-4 mr-2" />
            Return to Dashboard
          </Button>
        </Link>
      </div>
    </div>
  )
}
