import { useLocation } from 'react-router-dom'
import { useAuthStore } from '@/store/authStore'
import { LogOut } from 'lucide-react'

export function Navbar() {
  const { user, logout } = useAuthStore()
  const location = useLocation()

  const moduleTitle = location.pathname.startsWith('/sales')
    ? 'Commercial & Bidding'
    : location.pathname.startsWith('/email')
    ? 'Superadmin: Email & Credentials'
    : location.pathname.startsWith('/masters')
    ? 'Superadmin: Masters Directory'
    : 'Superadmin: Users & Access Control'

  return (
    <header className="h-12 border-b border-zinc-200 bg-white px-4 flex items-center justify-between shadow-2xs select-none shrink-0">
      <div className="flex items-center space-x-2.5">
        <span className="text-xs font-semibold tracking-tight text-zinc-900">
          {moduleTitle}
        </span>
      </div>

      {/* User Info & Actions */}
      <div className="flex items-center space-x-3">
        {user && (
          <div className="flex items-center space-x-2">
            <div className="text-right hidden sm:block">
              <span className="text-xs font-medium text-zinc-900 leading-tight block">{user.name}</span>
              <span className="text-[10px] text-zinc-500 block leading-tight">{user.designation || user.role_type}</span>
            </div>
            <div className="w-6 h-6 rounded-full bg-zinc-900 text-white font-medium text-[10px] flex items-center justify-center shadow-xs">
              {user.name.substring(0, 2).toUpperCase()}
            </div>
            <button
              onClick={logout}
              title="Sign Out"
              className="p-1.5 rounded text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 transition-colors cursor-pointer"
            >
              <LogOut className="w-3.5 h-3.5" />
            </button>
          </div>
        )}
      </div>
    </header>
  )
}
