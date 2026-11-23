import * as React from 'react'
import { cn } from '@/lib/utils'

export interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  error?: string
}

export const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ className, type, error, ...props }, ref) => {
    return (
      <div className="w-full">
        <input
          type={type}
          className={cn(
            'flex h-8 w-full rounded-md border border-zinc-200 bg-white px-2.5 py-1 text-xs placeholder:text-zinc-400 focus:outline-hidden focus:border-zinc-400 focus:ring-1 focus:ring-zinc-300 disabled:cursor-not-allowed disabled:opacity-50 transition-colors',
            error && 'border-rose-500 focus:border-rose-500',
            className
          )}
          ref={ref}
          {...props}
        />
        {error && <p className="mt-1 text-[11px] text-rose-600">{error}</p>}
      </div>
    )
  }
)
Input.displayName = 'Input'
