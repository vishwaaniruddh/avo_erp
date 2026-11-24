import { useLocation } from 'react-router-dom'
import { useAuthStore } from '@/store/authStore'
import { LogOut, Menu } from 'lucide-react'

interface NavbarProps {
  onToggleMenu?: () => void
}

export function Navbar({ onToggleMenu }: NavbarProps) {
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
    <header className="h-12 border-b border-zinc-200 bg-white px-3 sm:px-4 flex items-center justify-between shadow-2xs select-none shrink-0 z-20">
      <div className="flex items-center space-x-2 sm:space-x-2.5 min-w-0">
        {/* Mobile Hamburger Button */}
        <button
          type="button"
          onClick={onToggleMenu}
          className="p-1.5 -ml-1 text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 rounded-md md:hidden cursor-pointer shrink-0 transition-colors"
          title="Toggle Navigation Menu"
          aria-label="Toggle navigation menu"
        >
          <Menu className="w-5 h-5" />
        </button>

        <span className="text-xs font-semibold tracking-tight text-zinc-900 truncate">
          {moduleTitle}
        </span>
      </div>

      {/* User Info & Actions */}
      <div className="flex items-center space-x-2 sm:space-x-3 shrink-0">
        {user && (
          <div className="flex items-center space-x-2">
            <div className="text-right hidden sm:block">
              <span className="text-xs font-medium text-zinc-900 leading-tight block">{user.name}</span>
              <span className="text-[10px] text-zinc-500 block leading-tight">{user.designation || user.role_type}</span>
            </div>
            <div className="w-7 h-7 sm:w-6 sm:h-6 rounded-full bg-zinc-900 text-white font-medium text-[10px] flex items-center justify-center shadow-xs">
              {user.name.substring(0, 2).toUpperCase()}
            </div>
            <button
              onClick={logout}
              title="Sign Out"
              className="p-1.5 rounded text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 transition-colors cursor-pointer"
            >
              <LogOut className="w-4 h-4 sm:w-3.5 sm:h-3.5" />
            </button>
          </div>
        )}
      </div>
    </header>
  )
}
