import { useState, useMemo } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import {
  FileText,
  Plus,
  Search,
  Eye,
  Edit2,
  Trash2,
  CheckCircle2,
  AlertCircle,
  Tag,
  Layers,
} from 'lucide-react'
import { emailApi } from '@/api/services'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Dialog, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import type { EmailTemplate } from '@/types'

const CATEGORIES = ['commercial', 'operational', 'finance', 'identity', 'general']

export function EmailTemplatesPage() {
  const queryClient = useQueryClient()
  const [search, setSearch] = useState('')
  const [selectedCategory, setSelectedCategory] = useState<string>('ALL')

  // Modals
  const [isFormOpen, setIsFormOpen] = useState(false)
  const [isPreviewOpen, setIsPreviewOpen] = useState(false)
  const [isDeleteOpen, setIsDeleteOpen] = useState(false)

  const [editingItem, setEditingItem] = useState<EmailTemplate | null>(null)
  const [previewItem, setPreviewItem] = useState<EmailTemplate | null>(null)
  const [deletingId, setDeletingId] = useState<number | null>(null)
  const [feedback, setFeedback] = useState<{ type: 'success' | 'error'; message: string } | null>(null)

  // Form State
  const [templateKey, setTemplateKey] = useState('')
  const [templateName, setTemplateName] = useState('')
  const [category, setCategory] = useState('commercial')
  const [subject, setSubject] = useState('')
  const [bodyHtml, setBodyHtml] = useState('')
  const [bodyText, setBodyText] = useState('')
  const [availableVariables, setAvailableVariables] = useState('')
  const [isActive, setIsActive] = useState(true)

  // Query templates
  const { data: templates = [], isLoading } = useQuery({
    queryKey: ['email-templates'],
    queryFn: () => emailApi.getTemplates(),
  })

  // Create mutation
  const createMutation = useMutation({
    mutationFn: (data: Partial<EmailTemplate>) => emailApi.createTemplate(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['email-templates'] })
      setIsFormOpen(false)
      setFeedback({ type: 'success', message: 'Email template created successfully.' })
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.response?.data?.message || err.message || 'Failed to create template.' })
    },
  })

  // Update mutation
  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<EmailTemplate> }) => emailApi.updateTemplate(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['email-templates'] })
      setIsFormOpen(false)
      setFeedback({ type: 'success', message: 'Email template updated successfully.' })
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.response?.data?.message || err.message || 'Failed to update template.' })
    },
  })

  // Delete mutation
  const deleteMutation = useMutation({
    mutationFn: (id: number) => emailApi.deleteTemplate(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['email-templates'] })
      setIsDeleteOpen(false)
      setDeletingId(null)
      setFeedback({ type: 'success', message: 'Email template deleted.' })
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.response?.data?.message || err.message || 'Failed to delete template.' })
    },
  })

  const handleOpenCreate = () => {
    setEditingItem(null)
    setTemplateKey('')
    setTemplateName('')
    setCategory('commercial')
    setSubject('')
    setBodyHtml('<p>Dear {{recipient_name}},</p>\n<p>Your notification content goes here.</p>\n<p>Regards,<br>AVO Team</p>')
    setBodyText('Dear {{recipient_name}},\n\nYour notification content goes here.\n\nRegards,\nAVO Team')
    setAvailableVariables('{{recipient_name}}, {{company_name}}')
    setIsActive(true)
    setIsFormOpen(true)
  }

  const handleOpenEdit = (tpl: EmailTemplate) => {
    setEditingItem(tpl)
    setTemplateKey(tpl.template_key)
    setTemplateName(tpl.template_name)
    setCategory(tpl.category)
    setSubject(tpl.subject)
    setBodyHtml(tpl.body_html)
    setBodyText(tpl.body_text || '')
    setAvailableVariables(tpl.available_variables || '')
    setIsActive(Boolean(tpl.is_active))
    setIsFormOpen(true)
  }

  const handleOpenPreview = (tpl: EmailTemplate) => {
    setPreviewItem(tpl)
    setIsPreviewOpen(true)
  }

  const handleFormSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    const payload: Partial<EmailTemplate> = {
      template_key: templateKey.trim().toUpperCase(),
      template_name: templateName.trim(),
      category,
      subject,
      body_html: bodyHtml,
      body_text: bodyText || undefined,
      available_variables: availableVariables || undefined,
      is_active: isActive ? 1 : 0,
    }

    if (editingItem) {
      updateMutation.mutate({ id: editingItem.id, data: payload })
    } else {
      createMutation.mutate(payload)
    }
  }

  const filteredTemplates = useMemo(() => {
    return templates.filter((t) => {
      const q = search.toLowerCase()
      const matchesSearch =
        t.template_name.toLowerCase().includes(q) ||
        t.template_key.toLowerCase().includes(q) ||
        t.subject.toLowerCase().includes(q)
      const matchesCategory = selectedCategory === 'ALL' || t.category === selectedCategory
      return matchesSearch && matchesCategory
    })
  }, [templates, search, selectedCategory])

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3.5 px-4 rounded-lg border border-zinc-200 shadow-2xs">
        <div>
          <div className="flex items-center gap-2">
            <h1 className="text-sm font-semibold tracking-tight text-zinc-900">Email Templates</h1>
            <span className="text-[10px] font-medium bg-zinc-100 text-zinc-700 px-1.5 py-0.5 rounded border border-zinc-200">
              Notification Engine
            </span>
          </div>
          <p className="text-[11px] text-zinc-500 mt-0.5">
            Configure transaction, quote dispatch, and milestone alert email templates with dynamic tokens.
          </p>
        </div>
        <Button onClick={handleOpenCreate} className="h-7.5 px-3 text-xs bg-zinc-900 hover:bg-black text-white shrink-0">
          <Plus className="w-3.5 h-3.5 mr-1.5" />
          Create Template
        </Button>
      </div>

      {feedback && (
        <div
          className={`flex items-center justify-between p-2.5 px-3 rounded-lg border text-xs ${
            feedback.type === 'success'
              ? 'bg-zinc-50 border-zinc-300 text-zinc-800'
              : 'bg-red-50 border-red-200 text-red-700'
          }`}
        >
          <div className="flex items-center gap-2">
            {feedback.type === 'success' ? (
              <CheckCircle2 className="w-4 h-4 text-zinc-800" />
            ) : (
              <AlertCircle className="w-4 h-4 text-red-600" />
            )}
            <span>{feedback.message}</span>
          </div>
          <button onClick={() => setFeedback(null)} className="text-zinc-400 hover:text-zinc-600 font-bold ml-2">
            ×
          </button>
        </div>
      )}

      {/* Summary Stats */}
      <div className="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
        <div className="bg-white p-2.5 px-3 rounded-lg border border-zinc-200 flex items-center justify-between shadow-2xs">
          <div>
            <p className="text-[10px] font-medium text-zinc-500 uppercase tracking-wider">Total Templates</p>
            <h3 className="text-base font-bold text-zinc-900 leading-tight mt-0.5">{templates.length}</h3>
          </div>
          <div className="w-7 h-7 rounded-md bg-zinc-100 flex items-center justify-center text-zinc-700">
            <FileText className="w-3.5 h-3.5" />
          </div>
        </div>
        <div className="bg-white p-2.5 px-3 rounded-lg border border-zinc-200 flex items-center justify-between shadow-2xs">
          <div>
            <p className="text-[10px] font-medium text-zinc-500 uppercase tracking-wider">Active Templates</p>
            <h3 className="text-base font-bold text-zinc-900 leading-tight mt-0.5">
              {templates.filter((t) => t.is_active).length}
            </h3>
          </div>
          <div className="w-7 h-7 rounded-md bg-zinc-100 flex items-center justify-center text-zinc-700">
            <CheckCircle2 className="w-3.5 h-3.5" />
          </div>
        </div>
        <div className="bg-white p-2.5 px-3 rounded-lg border border-zinc-200 flex items-center justify-between shadow-2xs">
          <div>
            <p className="text-[10px] font-medium text-zinc-500 uppercase tracking-wider">Categories</p>
            <h3 className="text-base font-bold text-zinc-900 leading-tight mt-0.5">
              {new Set(templates.map((t) => t.category)).size}
            </h3>
          </div>
          <div className="w-7 h-7 rounded-md bg-zinc-100 flex items-center justify-center text-zinc-700">
            <Layers className="w-3.5 h-3.5" />
          </div>
        </div>
      </div>

      {/* Search and Category Filter */}
      <Card className="border-zinc-200 shadow-2xs">
        <CardContent className="p-3 flex flex-col sm:flex-row gap-2.5 items-stretch sm:items-center">
          <div className="relative flex-1">
            <Search className="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-zinc-400" />
            <Input
              type="text"
              placeholder="Search by template name, key, subject line..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="h-8 pl-8 text-xs bg-zinc-50/50 border-zinc-200 focus:bg-white"
            />
          </div>
          <select
            value={selectedCategory}
            onChange={(e) => setSelectedCategory(e.target.value)}
            className="h-8 px-2.5 text-xs bg-zinc-50/50 border border-zinc-200 rounded-md text-zinc-700 focus:bg-white"
          >
            <option value="ALL">All Categories</option>
            {CATEGORIES.map((c) => (
              <option key={c} value={c}>
                {c.toUpperCase()}
              </option>
            ))}
          </select>
        </CardContent>
      </Card>

      {/* Templates Table */}
      <Card className="border-zinc-200 shadow-2xs overflow-hidden">
        <div className="overflow-x-auto">
          <Table className="min-w-[700px]">
            <TableHeader className="bg-zinc-50/80 border-b border-zinc-200">
              <TableRow>
                <TableHead className="font-semibold text-zinc-600 text-[11px] w-10 text-center">#</TableHead>
                <TableHead className="font-semibold text-zinc-600 text-[11px]">Template Key</TableHead>
                <TableHead className="font-semibold text-zinc-600 text-[11px]">Template Name</TableHead>
                <TableHead className="font-semibold text-zinc-600 text-[11px]">Category</TableHead>
                <TableHead className="font-semibold text-zinc-600 text-[11px]">Subject Line</TableHead>
                <TableHead className="font-semibold text-zinc-600 text-[11px]">Available Variables</TableHead>
                <TableHead className="font-semibold text-zinc-600 text-[11px] text-center">Status</TableHead>
                <TableHead className="text-right font-semibold text-zinc-600 text-[11px]">Action</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {isLoading ? (
                <TableRow>
                  <TableCell colSpan={8} className="text-center py-8 text-xs text-zinc-500">
                    Loading email templates...
                  </TableCell>
                </TableRow>
              ) : filteredTemplates.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={8} className="text-center py-8 text-xs text-zinc-500">
                    No templates found matching your criteria.
                  </TableCell>
                </TableRow>
              ) : (
                filteredTemplates.map((item, idx) => (
                  <TableRow key={item.id} className="hover:bg-zinc-50/60 border-b border-zinc-100 transition-colors">
                    <TableCell className="text-center font-mono text-[11px] text-zinc-400 whitespace-nowrap">
                      {idx + 1}
                    </TableCell>
                    <TableCell className="whitespace-nowrap font-mono text-[11px] font-medium text-zinc-900">
                      {item.template_key}
                    </TableCell>
                    <TableCell className="whitespace-nowrap text-xs text-zinc-800 font-medium">
                      {item.template_name}
                    </TableCell>
                    <TableCell className="whitespace-nowrap">
                      <span className="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono uppercase bg-zinc-100 text-zinc-700 border border-zinc-200">
                        {item.category}
                      </span>
                    </TableCell>
                    <TableCell className="text-xs text-zinc-600 max-w-xs truncate whitespace-nowrap">
                      {item.subject}
                    </TableCell>
                    <TableCell className="text-[11px] text-zinc-500 font-mono whitespace-nowrap max-w-xs truncate">
                      {item.available_variables || '—'}
                    </TableCell>
                    <TableCell className="text-center whitespace-nowrap">
                      {item.is_active ? (
                        <span className="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-zinc-900 text-white">
                          Active
                        </span>
                      ) : (
                        <span className="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-zinc-100 text-zinc-500">
                          Inactive
                        </span>
                      )}
                    </TableCell>
                    <TableCell className="text-right whitespace-nowrap">
                      <div className="flex items-center justify-end gap-1">
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => handleOpenPreview(item)}
                          className="h-6.5 px-2 text-[11px] text-zinc-700 hover:text-black hover:bg-zinc-100"
                          title="Preview Template"
                        >
                          <Eye className="w-3 h-3 mr-1 text-zinc-500" />
                          Preview
                        </Button>
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => handleOpenEdit(item)}
                          className="h-6.5 w-6.5 p-0 text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100"
                          title="Edit Template"
                        >
                          <Edit2 className="w-3 h-3" />
                        </Button>
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => {
                            setDeletingId(item.id)
                            setIsDeleteOpen(true)
                          }}
                          className="h-6.5 w-6.5 p-0 text-zinc-400 hover:text-red-600 hover:bg-red-50"
                          title="Delete Template"
                        >
                          <Trash2 className="w-3 h-3" />
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </div>
      </Card>

      {/* Create / Edit Dialog */}
      <Dialog open={isFormOpen} onOpenChange={setIsFormOpen} maxWidth="max-w-xl">
        <DialogHeader>
          <DialogTitle className="text-sm font-semibold text-zinc-900">
            {editingItem ? 'Edit Email Template' : 'Create Email Template'}
          </DialogTitle>
          <DialogDescription className="text-xs text-zinc-500">
            Configure template content with dynamic tokens like {'{{quote_number}}'}, {'{{customer_name}}'}.
          </DialogDescription>
        </DialogHeader>

        <form onSubmit={handleFormSubmit} className="space-y-3 mt-4">
            <div className="grid grid-cols-2 gap-2">
              <div>
                <Label className="text-[11px] font-medium text-zinc-700">Template Key *</Label>
                <Input
                  required
                  placeholder="e.g. SALES_QUOTE_SUBMITTED"
                  value={templateKey}
                  onChange={(e) => setTemplateKey(e.target.value.toUpperCase())}
                  className="h-8 text-xs mt-1 font-mono text-[11px] bg-zinc-50/50"
                />
              </div>
              <div>
                <Label className="text-[11px] font-medium text-zinc-700">Category *</Label>
                <select
                  value={category}
                  onChange={(e) => setCategory(e.target.value)}
                  className="w-full h-8 px-2 text-xs mt-1 bg-zinc-50/50 border border-zinc-200 rounded-md text-zinc-800"
                >
                  {CATEGORIES.map((cat) => (
                    <option key={cat} value={cat}>
                      {cat.toUpperCase()}
                    </option>
                  ))}
                </select>
              </div>
            </div>

            <div>
              <Label className="text-[11px] font-medium text-zinc-700">Template Name *</Label>
              <Input
                required
                placeholder="e.g. Quotation Submission Notification"
                value={templateName}
                onChange={(e) => setTemplateName(e.target.value)}
                className="h-8 text-xs mt-1 bg-zinc-50/50"
              />
            </div>

            <div>
              <Label className="text-[11px] font-medium text-zinc-700">Subject Line *</Label>
              <Input
                required
                placeholder="e.g. New Quotation #{{quote_number}} for {{customer_name}}"
                value={subject}
                onChange={(e) => setSubject(e.target.value)}
                className="h-8 text-xs mt-1 bg-zinc-50/50"
              />
            </div>

            <div>
              <Label className="text-[11px] font-medium text-zinc-700">HTML Body *</Label>
              <textarea
                required
                rows={6}
                value={bodyHtml}
                onChange={(e) => setBodyHtml(e.target.value)}
                className="w-full p-2.5 text-xs font-mono mt-1 bg-zinc-50/50 border border-zinc-200 rounded-md text-zinc-800 focus:bg-white focus:outline-hidden"
                placeholder="<p>HTML markup with tokens...</p>"
              />
            </div>

            <div className="grid grid-cols-2 gap-2">
              <div>
                <Label className="text-[11px] font-medium text-zinc-700">Available Variables (comma-separated)</Label>
                <Input
                  placeholder="{{quote_number}}, {{customer_name}}"
                  value={availableVariables}
                  onChange={(e) => setAvailableVariables(e.target.value)}
                  className="h-8 text-xs mt-1 font-mono text-[11px] bg-zinc-50/50"
                />
              </div>
              <div>
                <Label className="text-[11px] font-medium text-zinc-700">Status</Label>
                <div className="flex items-center gap-2 mt-2">
                  <input
                    type="checkbox"
                    id="isActiveTemplate"
                    checked={isActive}
                    onChange={(e) => setIsActive(e.target.checked)}
                    className="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500"
                  />
                  <label htmlFor="isActiveTemplate" className="text-xs text-zinc-700 select-none">
                    Active for dispatch
                  </label>
                </div>
              </div>
            </div>

            <DialogFooter className="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-end gap-2">
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={() => setIsFormOpen(false)}
                className="h-7.5 text-xs border-zinc-200 text-zinc-700"
              >
                Cancel
              </Button>
              <Button
                type="submit"
                size="sm"
                disabled={createMutation.isPending || updateMutation.isPending}
                className="h-7.5 text-xs bg-zinc-900 hover:bg-black text-white"
              >
                {createMutation.isPending || updateMutation.isPending ? 'Saving...' : editingItem ? 'Save Changes' : 'Create Template'}
              </Button>
            </DialogFooter>
          </form>
      </Dialog>

      {/* Preview Dialog */}
      <Dialog open={isPreviewOpen} onOpenChange={setIsPreviewOpen} maxWidth="max-w-lg">
        <DialogHeader>
          <DialogTitle className="text-sm font-semibold text-zinc-900">
            Template Preview: {previewItem?.template_name}
          </DialogTitle>
          <DialogDescription className="text-xs font-mono text-zinc-500">
            Key: {previewItem?.template_key}
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-3 mt-4">
          <div className="bg-zinc-50 p-2.5 rounded border border-zinc-200 text-xs">
            <span className="font-semibold text-zinc-700">Subject: </span>
            <span className="text-zinc-900">{previewItem?.subject}</span>
          </div>

          <div className="border border-zinc-200 rounded-md p-3.5 bg-white min-h-40 max-h-72 overflow-y-auto">
            <div
              className="prose prose-xs max-w-none text-zinc-800"
              dangerouslySetInnerHTML={{ __html: previewItem?.body_html || '' }}
            />
          </div>

          {previewItem?.available_variables && (
            <div className="text-[11px] text-zinc-500">
              <span className="font-medium text-zinc-700">Tokens: </span>
              <span className="font-mono text-zinc-600">{previewItem.available_variables}</span>
            </div>
          )}
        </div>

        <DialogFooter className="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-end">
          <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={() => setIsPreviewOpen(false)}
            className="h-7.5 text-xs border-zinc-200 text-zinc-700"
          >
            Close Preview
          </Button>
        </DialogFooter>
      </Dialog>

      {/* Delete Confirmation Dialog */}
      <Dialog open={isDeleteOpen} onOpenChange={setIsDeleteOpen} maxWidth="max-w-sm">
        <DialogHeader>
          <DialogTitle className="text-sm font-semibold text-zinc-900">Delete Template</DialogTitle>
          <DialogDescription className="text-xs text-zinc-500">
            Are you sure you want to delete this template? Any automated process relying on this key will fallback to default text.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter className="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-end gap-2">
          <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={() => setIsDeleteOpen(false)}
            className="h-7.5 text-xs border-zinc-200 text-zinc-700"
          >
            Cancel
          </Button>
          <Button
            type="button"
            size="sm"
            disabled={deleteMutation.isPending}
            onClick={() => deletingId && deleteMutation.mutate(deletingId)}
            className="h-7.5 text-xs bg-red-600 hover:bg-red-700 text-white"
          >
            {deleteMutation.isPending ? 'Deleting...' : 'Confirm Delete'}
          </Button>
        </DialogFooter>
      </Dialog>
    </div>
  )
}
