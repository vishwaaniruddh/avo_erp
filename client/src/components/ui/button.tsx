import * as React from 'react'
import { cn } from '@/lib/utils'

export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'default' | 'secondary' | 'outline' | 'destructive' | 'ghost' | 'success'
  size?: 'default' | 'sm' | 'lg' | 'icon'
  isLoading?: boolean
}

export const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant = 'default', size = 'default', isLoading, children, disabled, ...props }, ref) => {
    const base =
      'inline-flex items-center justify-center font-medium transition-all focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-zinc-400 disabled:pointer-events-none disabled:opacity-50 cursor-pointer rounded-md'

    const variants = {
      default: 'bg-zinc-900 text-white hover:bg-black border border-zinc-800 shadow-2xs',
      secondary: 'bg-zinc-100 text-zinc-900 hover:bg-zinc-200/80 border border-zinc-200',
      outline: 'border border-zinc-200 bg-white hover:bg-zinc-50 text-zinc-800 shadow-2xs',
      destructive: 'bg-zinc-900 text-white hover:bg-rose-900 border border-zinc-800',
      ghost: 'hover:bg-zinc-100 text-zinc-700',
      success: 'bg-zinc-900 text-white hover:bg-zinc-800 border border-zinc-800',
    }

    const sizes = {
      default: 'h-8 px-3 text-xs',
      sm: 'h-7 px-2.5 text-[11px]',
      lg: 'h-9 px-4 text-xs font-semibold',
      icon: 'h-8 w-8 p-0',
    }

    return (
      <button
        ref={ref}
        className={cn(base, variants[variant], sizes[size], className)}
        disabled={disabled || isLoading}
        {...props}
      >
        {isLoading && (
          <svg className="animate-spin -ml-0.5 mr-1.5 h-3.5 w-3.5 text-current" fill="none" viewBox="0 0 24 24">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
            <path
              className="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
          </svg>
        )}
        {children}
      </button>
    )
  }
)
Button.displayName = 'Button'
