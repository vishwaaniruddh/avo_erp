import * as React from 'react'
import { X } from 'lucide-react'
import { cn } from '@/lib/utils'

interface DialogProps {
  open: boolean
  onClose?: () => void
  onOpenChange?: (open: boolean) => void
  children: React.ReactNode
  maxWidth?: string
  zIndex?: string
}

export function Dialog({ open, onClose, onOpenChange, children, maxWidth = 'max-w-2xl', zIndex = 'z-50' }: DialogProps) {
  if (!open) return null

  const handleClose = () => {
    if (onClose) onClose()
    if (onOpenChange) onOpenChange(false)
  }

  return (
    <div className={cn("fixed inset-0 flex items-center justify-center p-4", zIndex)}>
      {/* Backdrop */}
      <div
        className="fixed inset-0 bg-black/45 backdrop-blur-2xs transition-opacity"
        onClick={handleClose}
      />
      {/* Content */}
      <div
        className={cn(
          'relative w-full rounded-xl bg-white p-5 sm:p-6 shadow-2xl border border-zinc-200 transition-all z-10 max-h-[90vh] overflow-y-auto',
          maxWidth
        )}
      >
        <button
          onClick={handleClose}
          className="absolute right-4 top-4 rounded-md p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 transition-colors cursor-pointer"
        >
          <X className="w-4 h-4" />
        </button>
        {children}
      </div>
    </div>
  )
}

export function DialogHeader({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('flex flex-col space-y-1 pb-3 border-b border-zinc-100', className)} {...props} />
}

export function DialogTitle({ className, ...props }: React.HTMLAttributes<HTMLHeadingElement>) {
  return <h2 className={cn('text-base font-semibold tracking-tight text-zinc-900', className)} {...props} />
}

export function DialogDescription({ className, ...props }: React.HTMLAttributes<HTMLParagraphElement>) {
  return <p className={cn('text-xs text-zinc-500', className)} {...props} />
}

export function DialogFooter({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('flex items-center justify-end space-x-2 pt-3 border-t border-zinc-100 mt-4', className)} {...props} />
}
