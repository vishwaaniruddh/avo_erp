import { Link, useLocation, useNavigate } from 'react-router-dom'
import { useState, useEffect } from 'react'
import {
  Users,
  Shield,
  KeyRound,
  ShieldCheck,
  LogOut,
  Mail,
  Server,
  FileText,
  Database,
  Building,
  MapPin,
  Globe,
  Compass,
  Truck,
  Briefcase,
  TrendingUp,
  ChevronDown,
  ChevronRight,
  Calculator,
  Layers,
  Award,
  FileCheck2,
  Crown,
  LayoutDashboard,
  Headphones,
  X,
} from 'lucide-react'
import { useAuthStore } from '@/store/authStore'

interface SidebarProps {
  isOpen?: boolean
  onClose?: () => void
}

export function Sidebar({ isOpen, onClose }: SidebarProps) {
  const location = useLocation()
  const navigate = useNavigate()
  const { user, logout, canAccessSuperadmin, canAccessSales } = useAuthStore()

  const hasSuperadmin = canAccessSuperadmin()
  const hasSales = canAccessSales()

  // Detection of active sections
  const isDashboardPath = location.pathname === '/dashboard'
  const isUsersPath = ['/users', '/roles', '/permissions'].some((p) => location.pathname.startsWith(p))
  const isEmailPath = location.pathname.startsWith('/email')
  const isMastersPath = location.pathname.startsWith('/masters')
  const isSalesPath = location.pathname.startsWith('/sales')

  // Smart collapsible states (only open active section by default to prevent vertical scrolling overflow)
  const [isUsersOpen, setIsUsersOpen] = useState(isUsersPath || (hasSuperadmin && !isEmailPath && !isMastersPath && !isSalesPath))
  const [isEmailOpen, setIsEmailOpen] = useState(isEmailPath)
  const [isMastersOpen, setIsMastersOpen] = useState(isMastersPath)
  const [isSalesOpen, setIsSalesOpen] = useState(isSalesPath)

  useEffect(() => {
    if (isUsersPath) setIsUsersOpen(true)
    if (isEmailPath) setIsEmailOpen(true)
    if (isMastersPath) setIsMastersOpen(true)
    if (isSalesPath) setIsSalesOpen(true)
  }, [isUsersPath, isEmailPath, isMastersPath, isSalesPath])

  // Submenu definitions
  const userSubItems = [
    { name: 'Users Directory', path: '/users', icon: Users },
    { name: 'Roles & Archetypes', path: '/roles', icon: Shield },
    { name: 'Permission Matrix', path: '/permissions', icon: KeyRound },
  ]

  const emailSubItems = [
    { name: 'Email Settings', path: '/email/settings', icon: Server },
    { name: 'Email Template', path: '/email/templates', icon: FileText },
  ]

  const mastersSubItems = [
    { name: 'City', path: '/masters/cities', icon: Building },
    { name: 'State', path: '/masters/states', icon: MapPin },
    { name: 'Country', path: '/masters/countries', icon: Globe },
    { name: 'Zone', path: '/masters/zones', icon: Compass },
    { name: 'Courier', path: '/masters/couriers', icon: Truck },
  ]

  const salesSubItems = [
    { name: 'Leads & Field Visits', path: '/sales/leads', icon: Users },
    { name: 'Quotation & Floor MSP', path: '/sales/quotes', icon: Calculator },
    { name: 'SI Multi-Bid Hit Rate', path: '/sales/clustering', icon: Layers },
    { name: 'Tender Register & BG', path: '/sales/tenders', icon: Award },
    { name: 'Order Milestones & Audit', path: '/sales/milestones', icon: FileCheck2 },
  ]

  return (
    <aside
      className={`
        fixed md:static inset-y-0 left-0 z-50 md:z-30
        w-64 md:w-60 bg-zinc-950 text-zinc-300 flex flex-col shrink-0 h-screen
        border-r border-zinc-800/80 select-none transition-transform duration-200 ease-in-out shadow-2xl md:shadow-none
        ${isOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'}
      `}
    >
      {/* Brand Header */}
      <div className="h-14 px-4 border-b border-zinc-800/80 flex items-center justify-between shrink-0 bg-zinc-950">
        <div className="flex items-center space-x-2.5 truncate">
          <div className="w-7 h-7 rounded-lg bg-zinc-900 flex items-center justify-center text-white shrink-0 border border-zinc-800 shadow-xs">
            <ShieldCheck className="w-4 h-4 text-zinc-100" />
          </div>
          <div className="truncate">
            <div className="flex items-center gap-1.5">
              <h1 className="font-bold text-xs tracking-tight text-white leading-none">
                AVO ERP
              </h1>
              <span className="text-[9px] px-1.5 py-0.5 rounded font-mono font-semibold bg-zinc-800 text-zinc-300 border border-zinc-700/60 leading-none">
                v2.0
              </span>
            </div>
            <div className="flex items-center gap-1.5 mt-1">
              <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
              <span className="text-[10px] text-zinc-400 block leading-none font-medium truncate">
                Enterprise Core
              </span>
            </div>
          </div>
        </div>

        {/* Mobile Close Button */}
        <button
          type="button"
          onClick={onClose}
          className="p-1.5 rounded-md text-zinc-400 hover:text-white hover:bg-zinc-850 md:hidden cursor-pointer"
          title="Close Navigation Menu"
          aria-label="Close menu"
        >
          <X className="w-4 h-4" />
        </button>
      </div>

      {/* Navigation Menu with Sleek Custom Scrollbar */}
      <nav className="flex-1 px-2.5 py-3 space-y-3.5 overflow-y-auto custom-scrollbar">
        {/* WORKSPACE & OVERVIEW (Available to all authorized roles) */}
        <div className="space-y-1">
          <div className="px-2 py-0.5 text-[9px] font-bold text-zinc-500 uppercase tracking-widest flex items-center justify-between">
            <span>Workspace</span>
          </div>
          <Link
            to="/dashboard"
            onClick={onClose}
            className={`flex items-center px-2.5 py-1.5 rounded-md text-xs transition-all ${
              isDashboardPath
                ? 'bg-zinc-900 text-white font-medium border border-zinc-800 shadow-2xs'
                : 'text-zinc-400 hover:bg-zinc-900/60 hover:text-zinc-200'
            }`}
          >
            {user?.role_type === 'Service Desk' ? (
              <Headphones className={`w-3.5 h-3.5 mr-2 shrink-0 ${isDashboardPath ? 'text-white' : 'text-zinc-400'}`} />
            ) : (
              <LayoutDashboard className={`w-3.5 h-3.5 mr-2 shrink-0 ${isDashboardPath ? 'text-white' : 'text-zinc-400'}`} />
            )}
            <span className="truncate">
              {user?.role_type === 'Service Desk' ? 'Helpdesk & Service Desk' : 'Enterprise Overview'}
            </span>
          </Link>
        </div>

        {/* SUPERADMIN SECTION (Strictly Admin / Executive / L3) */}
        {hasSuperadmin && (
        <div className="space-y-1 pt-2 border-t border-zinc-900">
          <div className="px-2 py-0.5 text-[9px] font-bold text-zinc-500 uppercase tracking-widest flex items-center justify-between">
            <span className="flex items-center gap-1">
              <Crown className="w-3 h-3 text-zinc-400" />
              Superadmin
            </span>
          </div>

          <div className="space-y-1 mt-1">
            {/* - Users */}
            <div>
              <button
                type="button"
                onClick={() => setIsUsersOpen(!isUsersOpen)}
                className={`w-full flex items-center justify-between px-2.5 py-1.5 rounded-md text-xs transition-all cursor-pointer ${
                  isUsersPath
                    ? 'bg-zinc-900 text-white font-medium border border-zinc-800 shadow-2xs'
                    : 'text-zinc-400 hover:bg-zinc-900/60 hover:text-zinc-200'
                }`}
              >
                <div className="flex items-center truncate">
                  <Users
                    className={`w-3.5 h-3.5 mr-2 shrink-0 ${isUsersPath ? 'text-white' : 'text-zinc-400'}`}
                  />
                  <span className="truncate">Users & Access</span>
                </div>
                {isUsersOpen ? (
                  <ChevronDown className="w-3.5 h-3.5 text-zinc-400 shrink-0 ml-1" />
                ) : (
                  <ChevronRight className="w-3.5 h-3.5 text-zinc-500 shrink-0 ml-1" />
                )}
              </button>

              {isUsersOpen && (
                <div className="space-y-0.5 ml-3 pl-2.5 border-l border-zinc-800/80 pt-1 mt-0.5">
                  {userSubItems.map((item) => {
                    const Icon = item.icon
                    const isActive = location.pathname === item.path
                    return (
                      <Link
                        key={item.path}
                        to={item.path}
                        onClick={onClose}
                        className={`flex items-center px-2 py-1.5 rounded-md text-[11px] transition-all ${
                          isActive
                            ? 'bg-zinc-800 text-white font-medium border border-zinc-700/60 shadow-2xs'
                            : 'text-zinc-400 hover:bg-zinc-900/60 hover:text-zinc-200'
                        }`}
                      >
                        <Icon className={`w-3 h-3 mr-2 shrink-0 ${isActive ? 'text-white' : 'text-zinc-500'}`} />
                        <span className="truncate">{item.name}</span>
                      </Link>
                    )
                  })}
                </div>
              )}
            </div>

            {/* - Email */}
            <div>
              <button
                type="button"
                onClick={() => setIsEmailOpen(!isEmailOpen)}
                className={`w-full flex items-center justify-between px-2.5 py-1.5 rounded-md text-xs transition-all cursor-pointer ${
                  isEmailPath
                    ? 'bg-zinc-900 text-white font-medium border border-zinc-800 shadow-2xs'
                    : 'text-zinc-400 hover:bg-zinc-900/60 hover:text-zinc-200'
                }`}
              >
                <div className="flex items-center truncate">
                  <Mail
                    className={`w-3.5 h-3.5 mr-2 shrink-0 ${isEmailPath ? 'text-white' : 'text-zinc-400'}`}
                  />
                  <span className="truncate">Email Credentials</span>
                </div>
                {isEmailOpen ? (
                  <ChevronDown className="w-3.5 h-3.5 text-zinc-400 shrink-0 ml-1" />
                ) : (
                  <ChevronRight className="w-3.5 h-3.5 text-zinc-500 shrink-0 ml-1" />
                )}
              </button>

              {isEmailOpen && (
                <div className="space-y-0.5 ml-3 pl-2.5 border-l border-zinc-800/80 pt-1 mt-0.5">
                  {emailSubItems.map((item) => {
                    const Icon = item.icon
                    const isActive = location.pathname === item.path
                    return (
                      <Link
                        key={item.path}
                        to={item.path}
                        onClick={onClose}
                        className={`flex items-center px-2 py-1.5 rounded-md text-[11px] transition-all ${
                          isActive
                            ? 'bg-zinc-800 text-white font-medium border border-zinc-700/60 shadow-2xs'
                            : 'text-zinc-400 hover:bg-zinc-900/60 hover:text-zinc-200'
                        }`}
                      >
                        <Icon className={`w-3 h-3 mr-2 shrink-0 ${isActive ? 'text-white' : 'text-zinc-500'}`} />
                        <span className="truncate">{item.name}</span>
                      </Link>
                    )
                  })}
                </div>
              )}
            </div>

            {/* - Masters */}
            <div>
              <button
                type="button"
                onClick={() => setIsMastersOpen(!isMastersOpen)}
                className={`w-full flex items-center justify-between px-2.5 py-1.5 rounded-md text-xs transition-all cursor-pointer ${
                  isMastersPath
                    ? 'bg-zinc-900 text-white font-medium border border-zinc-800 shadow-2xs'
                    : 'text-zinc-400 hover:bg-zinc-900/60 hover:text-zinc-200'
                }`}
              >
                <div className="flex items-center truncate">
                  <Database
                    className={`w-3.5 h-3.5 mr-2 shrink-0 ${isMastersPath ? 'text-white' : 'text-zinc-400'}`}
                  />
                  <span className="truncate">System Masters</span>
                </div>
                {isMastersOpen ? (
                  <ChevronDown className="w-3.5 h-3.5 text-zinc-400 shrink-0 ml-1" />
                ) : (
                  <ChevronRight className="w-3.5 h-3.5 text-zinc-500 shrink-0 ml-1" />
                )}
              </button>

              {isMastersOpen && (
                <div className="space-y-0.5 ml-3 pl-2.5 border-l border-zinc-800/80 pt-1 mt-0.5">
                  {mastersSubItems.map((item) => {
                    const Icon = item.icon
                    const isActive =
                      location.pathname === item.path ||
                      (location.pathname === '/masters' && item.path === '/masters/cities')
                    return (
                      <Link
                        key={item.path}
                        to={item.path}
                        onClick={onClose}
                        className={`flex items-center px-2 py-1.5 rounded-md text-[11px] transition-all ${
                          isActive
                            ? 'bg-zinc-800 text-white font-medium border border-zinc-700/60 shadow-2xs'
                            : 'text-zinc-400 hover:bg-zinc-900/60 hover:text-zinc-200'
                        }`}
                      >
                        <Icon className={`w-3 h-3 mr-2 shrink-0 ${isActive ? 'text-white' : 'text-zinc-500'}`} />
                        <span className="truncate">{item.name}</span>
                      </Link>
                    )
                  })}
                </div>
              )}
            </div>
          </div>
        </div>
        )}

        {/* COMMERCIAL & BIDDING SECTION (Sales, Executive, Admin, Finance, Project) */}
        {hasSales && (
          <div className="space-y-1 pt-2 border-t border-zinc-900">
            <div className="px-2 py-0.5 text-[9px] font-bold text-zinc-500 uppercase tracking-widest flex items-center justify-between">
              <span className="flex items-center gap-1">
                <TrendingUp className="w-3 h-3 text-zinc-400" />
                Commercial & Bidding
              </span>
            </div>

          <div className="space-y-1 mt-1">
            <button
              type="button"
              onClick={() => {
                if (!isSalesPath) {
                  navigate('/sales/leads')
                } else {
                  setIsSalesOpen(!isSalesOpen)
                }
              }}
              className={`w-full flex items-center justify-between px-2.5 py-1.5 rounded-md text-xs transition-all cursor-pointer ${
                isSalesPath
                  ? 'bg-zinc-900 text-white font-medium border border-zinc-800 shadow-2xs'
                  : 'text-zinc-400 hover:bg-zinc-900/60 hover:text-zinc-200'
              }`}
            >
              <div className="flex items-center truncate">
                <Briefcase
                  className={`w-3.5 h-3.5 mr-2 shrink-0 ${isSalesPath ? 'text-white' : 'text-zinc-400'}`}
                />
                <span className="truncate">Sales & Tender Platform</span>
              </div>
              {isSalesOpen ? (
                <ChevronDown className="w-3.5 h-3.5 text-zinc-400 shrink-0 ml-1" />
              ) : (
                <ChevronRight className="w-3.5 h-3.5 text-zinc-500 shrink-0 ml-1" />
              )}
            </button>

            {isSalesOpen && (
              <div className="space-y-0.5 ml-3 pl-2.5 border-l border-zinc-800/80 pt-1 mt-0.5">
                {salesSubItems.map((item) => {
                  const Icon = item.icon
                  const isActive =
                    location.pathname === item.path ||
                    (location.pathname === '/sales' && item.path === '/sales/leads')

                  return (
                    <Link
                      key={item.path}
                      to={item.path}
                      onClick={onClose}
                      className={`flex items-center px-2 py-1.5 rounded-md text-[11px] transition-all ${
                        isActive
                          ? 'bg-zinc-800 text-white font-medium border border-zinc-700/60 shadow-2xs'
                          : 'text-zinc-400 hover:bg-zinc-900/60 hover:text-zinc-200'
                      }`}
                    >
                      <Icon className={`w-3 h-3 mr-2 shrink-0 ${isActive ? 'text-white' : 'text-zinc-500'}`} />
                      <span className="truncate">{item.name}</span>
                    </Link>
                  )
                })}
              </div>
            )}
          </div>
        </div>
        )}
      </nav>

      {/* User Profile Footer */}
      <div className="p-3 border-t border-zinc-850 bg-zinc-950 shrink-0">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-2.5 truncate">
            <div className="w-7 h-7 rounded-full bg-zinc-850 border border-zinc-750 flex items-center justify-center font-bold text-[10px] text-zinc-200 shrink-0 shadow-xs">
              {user?.name ? user.name.substring(0, 2).toUpperCase() : 'AV'}
            </div>
            <div className="truncate">
              <p className="text-[11px] font-semibold text-zinc-200 truncate leading-tight">
                {user?.name || 'Alexander Vance'}
              </p>
              <p className="text-[9px] text-zinc-500 truncate leading-tight mt-0.5">
                {user?.designation || user?.role_type || 'Superadmin'}
              </p>
            </div>
          </div>
          <button
            onClick={logout}
            title="Sign Out of AVO ERP"
            className="p-1.5 rounded-md text-zinc-400 hover:text-white hover:bg-zinc-900 transition-colors cursor-pointer shrink-0"
          >
            <LogOut className="w-3.5 h-3.5" />
          </button>
        </div>
      </div>
    </aside>
  )
}
