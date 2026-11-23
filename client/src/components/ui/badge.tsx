import * as React from 'react'
import { cn } from '@/lib/utils'

export interface BadgeProps extends React.HTMLAttributes<HTMLDivElement> {
  variant?: 'default' | 'secondary' | 'outline' | 'success' | 'warning' | 'destructive' | 'info'
}

export function Badge({ className, variant = 'default', ...props }: BadgeProps) {
  const variants = {
    default: 'bg-zinc-900 text-white border-zinc-800',
    secondary: 'bg-zinc-100 text-zinc-800 border-zinc-200',
    outline: 'border border-zinc-200 text-zinc-700 bg-white',
    success: 'bg-zinc-100 text-zinc-900 border-zinc-200',
    warning: 'bg-zinc-100 text-zinc-900 border-zinc-200',
    destructive: 'bg-zinc-950 text-white border-zinc-900',
    info: 'bg-zinc-100 text-zinc-800 border-zinc-200',
  }

  return (
    <div
      className={cn(
        'inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-medium border transition-colors',
        variants[variant],
        className
      )}
      {...props}
    />
  )
}
