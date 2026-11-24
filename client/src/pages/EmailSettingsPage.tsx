import { useState, useMemo } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import {
  Mail,
  Plus,
  Search,
  Server,
  Send,
  Edit2,
  Trash2,
  CheckCircle2,
  AlertCircle,
  ShieldCheck,
  Radio,
} from 'lucide-react'
import { emailApi } from '@/api/services'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Dialog, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { isValidEmail, getEmailError } from '@/lib/validation'
import type { EmailSetting } from '@/types'

export function EmailSettingsPage() {
  const queryClient = useQueryClient()
  const [search, setSearch] = useState('')

  // Modal states
  const [isFormOpen, setIsFormOpen] = useState(false)
  const [isTestOpen, setIsTestOpen] = useState(false)
  const [isDeleteOpen, setIsDeleteOpen] = useState(false)

  const [editingItem, setEditingItem] = useState<EmailSetting | null>(null)
  const [testingItem, setTestingItem] = useState<EmailSetting | null>(null)
  const [deletingId, setDeletingId] = useState<number | null>(null)

  const [feedback, setFeedback] = useState<{ type: 'success' | 'error'; message: string } | null>(null)
  const [testResult, setTestResult] = useState<{ success: boolean; message: string; diagnostics?: any } | null>(null)
  const [testRecipient, setTestRecipient] = useState('')

  // Form states
  const [title, setTitle] = useState('')
  const [smtpHost, setSmtpHost] = useState('')
  const [smtpPort, setSmtpPort] = useState(587)
  const [smtpUsername, setSmtpUsername] = useState('')
  const [smtpPassword, setSmtpPassword] = useState('')
  const [smtpEncryption, setSmtpEncryption] = useState<'none' | 'ssl' | 'tls'>('tls')
  const [fromName, setFromName] = useState('AVO ERP System')
  const [fromEmail, setFromEmail] = useState('noreply@avoerp.com')
  const [replyTo, setReplyTo] = useState('')
  const [isActive, setIsActive] = useState(true)

  // Query settings
  const { data: settings = [], isLoading } = useQuery({
    queryKey: ['email-settings'],
    queryFn: emailApi.getSettings,
  })

  // Create mutation
  const createMutation = useMutation({
    mutationFn: (data: Partial<EmailSetting>) => emailApi.createSetting(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['email-settings'] })
      setIsFormOpen(false)
      setFeedback({ type: 'success', message: 'SMTP credentials added successfully.' })
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.response?.data?.message || err.message || 'Failed to save configuration.' })
    },
  })

  // Update mutation
  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<EmailSetting> }) => emailApi.updateSetting(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['email-settings'] })
      setIsFormOpen(false)
      setFeedback({ type: 'success', message: 'SMTP credentials updated successfully.' })
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.response?.data?.message || err.message || 'Failed to update configuration.' })
    },
  })

  // Delete mutation
  const deleteMutation = useMutation({
    mutationFn: (id: number) => emailApi.deleteSetting(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['email-settings'] })
      setIsDeleteOpen(false)
      setDeletingId(null)
      setFeedback({ type: 'success', message: 'SMTP configuration deleted.' })
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.response?.data?.message || err.message || 'Failed to delete configuration.' })
    },
  })

  // Test connection mutation
  const testMutation = useMutation({
    mutationFn: (data: any) => emailApi.testConnection(data),
    onSuccess: (res: any) => {
      setTestResult({
        success: true,
        message: res.message || 'SMTP Connection Successful!',
        diagnostics: res.data?.diagnostics,
      })
    },
    onError: (err: any) => {
      setTestResult({
        success: false,
        message: err.response?.data?.message || err.message || 'Connection test failed',
        diagnostics: err.response?.data?.data,
      })
    },
  })

  // Open Create
  const handleOpenCreate = () => {
    setEditingItem(null)
    setTitle('')
    setSmtpHost('smtp.gmail.com')
    setSmtpPort(587)
    setSmtpUsername('')
    setSmtpPassword('')
    setSmtpEncryption('tls')
    setFromName('AVO ERP System')
    setFromEmail('noreply@avoerp.com')
    setReplyTo('')
    setIsActive(true)
    setIsFormOpen(true)
  }

  // Open Edit
  const handleOpenEdit = (setting: EmailSetting) => {
    setEditingItem(setting)
    setTitle(setting.title)
    setSmtpHost(setting.smtp_host)
    setSmtpPort(setting.smtp_port)
    setSmtpUsername(setting.smtp_username)
    setSmtpPassword('') // leave blank unless modifying
    setSmtpEncryption(setting.smtp_encryption)
    setFromName(setting.from_name)
    setFromEmail(setting.from_email)
    setReplyTo(setting.reply_to || '')
    setIsActive(Boolean(setting.is_active))
    setIsFormOpen(true)
  }

  // Open Test
  const handleOpenTest = (setting: EmailSetting) => {
    setTestingItem(setting)
    setTestRecipient(setting.from_email || '')
    setTestResult(null)
    setIsTestOpen(true)
  }

  const handleRunTest = (e: React.FormEvent) => {
    e.preventDefault()
    if (!testingItem) return
    if (!isValidEmail(testRecipient)) {
      setTestResult({
        success: false,
        message: 'Please enter a valid recipient email address for diagnostic test.',
      })
      return
    }
    testMutation.mutate({
      setting_id: testingItem.id,
      host: testingItem.smtp_host,
      port: testingItem.smtp_port,
      encryption: testingItem.smtp_encryption,
      test_recipient: testRecipient,
    })
  }

  const handleFormSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    if (!isValidEmail(fromEmail)) {
      setFeedback({ type: 'error', message: 'Please provide a valid sender (From) email address.' })
      return
    }
    if (replyTo && !isValidEmail(replyTo)) {
      setFeedback({ type: 'error', message: 'Reply-To address is invalid. Please enter a valid email or leave blank.' })
      return
    }
    const payload: Partial<EmailSetting> = {
      title,
      smtp_host: smtpHost,
      smtp_port: Number(smtpPort),
      smtp_username: smtpUsername,
      smtp_encryption: smtpEncryption,
      from_name: fromName,
      from_email: fromEmail,
      reply_to: replyTo || undefined,
      is_active: isActive ? 1 : 0,
    }
    if (smtpPassword) {
      payload.smtp_password = smtpPassword
    }

    if (editingItem) {
      updateMutation.mutate({ id: editingItem.id, data: payload })
    } else {
      createMutation.mutate(payload)
    }
  }

  const filteredSettings = useMemo(() => {
    return settings.filter((s) => {
      const q = search.toLowerCase()
      return (
        s.title.toLowerCase().includes(q) ||
        s.smtp_host.toLowerCase().includes(q) ||
        s.from_email.toLowerCase().includes(q) ||
        s.smtp_username.toLowerCase().includes(q)
      )
    })
  }, [settings, search])

  return (
    <div className="space-y-4">
      {/* Page Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3.5 px-4 rounded-lg border border-zinc-200 shadow-2xs">
        <div>
          <div className="flex items-center gap-2">
            <h1 className="text-sm font-semibold tracking-tight text-zinc-900">Email Settings</h1>
            <span className="text-[10px] font-medium bg-zinc-100 text-zinc-700 px-1.5 py-0.5 rounded border border-zinc-200">
              SMTP Credentials
            </span>
          </div>
          <p className="text-[11px] text-zinc-500 mt-0.5">
            Configure outbound mail servers, authentication, and verify SMTP socket connections.
          </p>
        </div>
        <Button onClick={handleOpenCreate} className="h-7.5 px-3 text-xs bg-zinc-900 hover:bg-black text-white shrink-0">
          <Plus className="w-3.5 h-3.5 mr-1.5" />
          Add Configuration
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
            <p className="text-[10px] font-medium text-zinc-500 uppercase tracking-wider">Total Mailers</p>
            <h3 className="text-base font-bold text-zinc-900 leading-tight mt-0.5">{settings.length}</h3>
          </div>
          <div className="w-7 h-7 rounded-md bg-zinc-100 flex items-center justify-center text-zinc-700">
            <Mail className="w-3.5 h-3.5" />
          </div>
        </div>
        <div className="bg-white p-2.5 px-3 rounded-lg border border-zinc-200 flex items-center justify-between shadow-2xs">
          <div>
            <p className="text-[10px] font-medium text-zinc-500 uppercase tracking-wider">Active Outbound</p>
            <h3 className="text-base font-bold text-zinc-900 leading-tight mt-0.5">
              {settings.filter((s) => s.is_active).length}
            </h3>
          </div>
          <div className="w-7 h-7 rounded-md bg-zinc-100 flex items-center justify-center text-zinc-700">
            <Radio className="w-3.5 h-3.5" />
          </div>
        </div>
        <div className="bg-white p-2.5 px-3 rounded-lg border border-zinc-200 flex items-center justify-between shadow-2xs">
          <div>
            <p className="text-[10px] font-medium text-zinc-500 uppercase tracking-wider">TLS / SSL Secured</p>
            <h3 className="text-base font-bold text-zinc-900 leading-tight mt-0.5">
              {settings.filter((s) => s.smtp_encryption !== 'none').length}
            </h3>
          </div>
          <div className="w-7 h-7 rounded-md bg-zinc-100 flex items-center justify-center text-zinc-700">
            <ShieldCheck className="w-3.5 h-3.5" />
          </div>
        </div>
      </div>

      {/* Search Bar */}
      <Card className="border-zinc-200 shadow-2xs">
        <CardContent className="p-3">
          <div className="relative">
            <Search className="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-zinc-400" />
            <Input
              type="text"
              placeholder="Search by title, host, username, from address..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="h-8 pl-8 text-xs bg-zinc-50/50 border-zinc-200 focus:bg-white"
            />
          </div>
        </CardContent>
      </Card>

      {/* Settings Table */}
      <Card className="border-zinc-200 shadow-2xs overflow-hidden">
        <div className="overflow-x-auto">
          <Table className="min-w-[750px]">
            <TableHeader className="bg-zinc-50/80 border-b border-zinc-200">
              <TableRow>
                <TableHead className="font-semibold text-zinc-600 text-[11px] w-10 text-center">#</TableHead>
                <TableHead className="font-semibold text-zinc-600 text-[11px]">Configuration</TableHead>
                <TableHead className="font-semibold text-zinc-600 text-[11px]">Host & Port</TableHead>
                <TableHead className="font-semibold text-zinc-600 text-[11px]">Username</TableHead>
                <TableHead className="font-semibold text-zinc-600 text-[11px]">Sender Identity</TableHead>
                <TableHead className="font-semibold text-zinc-600 text-[11px]">Encryption</TableHead>
                <TableHead className="font-semibold text-zinc-600 text-[11px] text-center">Status</TableHead>
                <TableHead className="text-right font-semibold text-zinc-600 text-[11px]">Action</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {isLoading ? (
                <TableRow>
                  <TableCell colSpan={8} className="text-center py-8 text-xs text-zinc-500">
                    Loading SMTP settings...
                  </TableCell>
                </TableRow>
              ) : filteredSettings.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={8} className="text-center py-8 text-xs text-zinc-500">
                    No SMTP configurations found. Click "Add Configuration" to store email credentials.
                  </TableCell>
                </TableRow>
              ) : (
                filteredSettings.map((item, idx) => (
                  <TableRow key={item.id} className="hover:bg-zinc-50/60 border-b border-zinc-100 transition-colors">
                    <TableCell className="text-center font-mono text-[11px] text-zinc-400 whitespace-nowrap">
                      {idx + 1}
                    </TableCell>
                    <TableCell className="whitespace-nowrap">
                      <div className="font-medium text-zinc-900 text-xs">{item.title}</div>
                      <div className="text-[10px] text-zinc-400">ID: #{item.id}</div>
                    </TableCell>
                    <TableCell className="font-mono text-[11px] text-zinc-700 whitespace-nowrap">
                      {item.smtp_host}:{item.smtp_port}
                    </TableCell>
                    <TableCell className="text-xs text-zinc-700 whitespace-nowrap font-mono text-[11px]">
                      {item.smtp_username}
                    </TableCell>
                    <TableCell className="whitespace-nowrap">
                      <div className="text-xs text-zinc-800">{item.from_name}</div>
                      <div className="text-[10px] text-zinc-500 font-mono">{item.from_email}</div>
                    </TableCell>
                    <TableCell className="whitespace-nowrap">
                      <span className="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono uppercase bg-zinc-100 text-zinc-700 border border-zinc-200">
                        {item.smtp_encryption}
                      </span>
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
                          onClick={() => handleOpenTest(item)}
                          className="h-6.5 px-2 text-[11px] text-zinc-700 hover:text-black hover:bg-zinc-100"
                          title="Test Connection"
                        >
                          <Send className="w-3 h-3 mr-1 text-zinc-500" />
                          Test
                        </Button>
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => handleOpenEdit(item)}
                          className="h-6.5 w-6.5 p-0 text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100"
                          title="Edit Settings"
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
                          title="Delete Settings"
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

      {/* Add / Edit Dialog */}
      <Dialog open={isFormOpen} onOpenChange={setIsFormOpen} maxWidth="max-w-lg">
        <DialogHeader>
          <DialogTitle className="text-sm font-semibold text-zinc-900">
            {editingItem ? 'Edit SMTP Configuration' : 'Add SMTP Configuration'}
          </DialogTitle>
          <DialogDescription className="text-xs text-zinc-500">
            Set up outbound SMTP mail transport credentials and sender identity.
          </DialogDescription>
        </DialogHeader>

        <form onSubmit={handleFormSubmit} className="space-y-3.5 mt-4">
            <div>
              <Label className="text-[11px] font-medium text-zinc-700">Configuration Title *</Label>
              <Input
                required
                placeholder="e.g. Primary Gmail SMTP, SendGrid Cluster"
                value={title}
                onChange={(e) => setTitle(e.target.value)}
                className="h-8 text-xs mt-1 bg-zinc-50/50"
              />
            </div>

            <div className="grid grid-cols-3 gap-2">
              <div className="col-span-2">
                <Label className="text-[11px] font-medium text-zinc-700">SMTP Host *</Label>
                <Input
                  required
                  placeholder="smtp.example.com"
                  value={smtpHost}
                  onChange={(e) => setSmtpHost(e.target.value)}
                  className="h-8 text-xs mt-1 font-mono text-[11px] bg-zinc-50/50"
                />
              </div>
              <div>
                <Label className="text-[11px] font-medium text-zinc-700">Port *</Label>
                <Input
                  required
                  type="number"
                  placeholder="587"
                  value={smtpPort}
                  onChange={(e) => setSmtpPort(Number(e.target.value))}
                  className="h-8 text-xs mt-1 font-mono text-[11px] bg-zinc-50/50"
                />
              </div>
            </div>

            <div className="grid grid-cols-2 gap-2">
              <div>
                <Label className="text-[11px] font-medium text-zinc-700">Username *</Label>
                <Input
                  required
                  placeholder="username@domain.com"
                  value={smtpUsername}
                  onChange={(e) => setSmtpUsername(e.target.value)}
                  className="h-8 text-xs mt-1 font-mono text-[11px] bg-zinc-50/50"
                />
              </div>
              <div>
                <Label className="text-[11px] font-medium text-zinc-700">
                  Password {editingItem ? '(Blank to keep current)' : '*'}
                </Label>
                <Input
                  type="password"
                  required={!editingItem}
                  placeholder="••••••••••••"
                  value={smtpPassword}
                  onChange={(e) => setSmtpPassword(e.target.value)}
                  className="h-8 text-xs mt-1 font-mono text-[11px] bg-zinc-50/50"
                />
              </div>
            </div>

            <div className="grid grid-cols-2 gap-2">
              <div>
                <Label className="text-[11px] font-medium text-zinc-700">Encryption *</Label>
                <select
                  value={smtpEncryption}
                  onChange={(e) => setSmtpEncryption(e.target.value as any)}
                  className="w-full h-8 px-2 text-xs mt-1 bg-zinc-50/50 border border-zinc-200 rounded-md text-zinc-800"
                >
                  <option value="tls">TLS (STARTTLS - Port 587)</option>
                  <option value="ssl">SSL (SMTPS - Port 465)</option>
                  <option value="none">None (Plaintext - Port 25)</option>
                </select>
              </div>
              <div>
                <Label className="text-[11px] font-medium text-zinc-700">Status</Label>
                <div className="flex items-center gap-2 mt-2">
                  <input
                    type="checkbox"
                    id="isActiveToggle"
                    checked={isActive}
                    onChange={(e) => setIsActive(e.target.checked)}
                    className="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500"
                  />
                  <label htmlFor="isActiveToggle" className="text-xs text-zinc-700 select-none">
                    Active Outbound Mailer
                  </label>
                </div>
              </div>
            </div>

            <div className="grid grid-cols-2 gap-2 pt-2 border-t border-zinc-100">
              <div>
                <Label className="text-[11px] font-medium text-zinc-700">From Name *</Label>
                <Input
                  required
                  placeholder="AVO ERP System"
                  value={fromName}
                  onChange={(e) => setFromName(e.target.value)}
                  className="h-8 text-xs mt-1 bg-zinc-50/50"
                />
              </div>
              <div>
                <Label className="text-[11px] font-medium text-zinc-700">From Email Address *</Label>
                <Input
                  required
                  type="email"
                  placeholder="noreply@avoerp.com"
                  value={fromEmail}
                  onChange={(e) => setFromEmail(e.target.value)}
                  className={`h-8 text-xs mt-1 font-mono text-[11px] bg-zinc-50/50 ${
                    fromEmail && getEmailError(fromEmail) ? 'border-red-500 focus:ring-red-400' : ''
                  }`}
                />
                {fromEmail && getEmailError(fromEmail) && (
                  <p className="text-[10px] text-red-600 mt-1 font-medium">{getEmailError(fromEmail)}</p>
                )}
              </div>
            </div>

            <div>
              <Label className="text-[11px] font-medium text-zinc-700">Reply-To Address (Optional)</Label>
              <Input
                type="email"
                placeholder="support@avoerp.com"
                value={replyTo}
                onChange={(e) => setReplyTo(e.target.value)}
                className={`h-8 text-xs mt-1 font-mono text-[11px] bg-zinc-50/50 ${
                  replyTo && getEmailError(replyTo, false) ? 'border-red-500 focus:ring-red-400' : ''
                }`}
              />
              {replyTo && getEmailError(replyTo, false) && (
                <p className="text-[10px] text-red-600 mt-1 font-medium">{getEmailError(replyTo, false)}</p>
              )}
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
                disabled={
                  createMutation.isPending ||
                  updateMutation.isPending ||
                  !isValidEmail(fromEmail) ||
                  (!!replyTo && !isValidEmail(replyTo))
                }
                className="h-7.5 text-xs bg-zinc-900 hover:bg-black text-white"
              >
                {createMutation.isPending || updateMutation.isPending ? 'Saving...' : editingItem ? 'Save Changes' : 'Create Configuration'}
              </Button>
            </DialogFooter>
          </form>
      </Dialog>

      {/* Test Connection Dialog */}
      <Dialog open={isTestOpen} onOpenChange={setIsTestOpen} maxWidth="max-w-md">
        <DialogHeader>
          <DialogTitle className="text-sm font-semibold text-zinc-900">Test SMTP Connection</DialogTitle>
          <DialogDescription className="text-xs text-zinc-500">
            Verify connectivity to {testingItem?.smtp_host}:{testingItem?.smtp_port}.
          </DialogDescription>
        </DialogHeader>

        <form onSubmit={handleRunTest} className="space-y-3 mt-4">
            <div>
              <Label className="text-[11px] font-medium text-zinc-700">Recipient Email (for diagnostic test)</Label>
              <Input
                type="email"
                required
                placeholder="admin@avoerp.com"
                value={testRecipient}
                onChange={(e) => setTestRecipient(e.target.value)}
                className={`h-8 text-xs mt-1 font-mono text-[11px] bg-zinc-50/50 ${
                  testRecipient && getEmailError(testRecipient) ? 'border-red-500 focus:ring-red-400' : ''
                }`}
              />
              {testRecipient && getEmailError(testRecipient) && (
                <p className="text-[10px] text-red-600 mt-1 font-medium">{getEmailError(testRecipient)}</p>
              )}
            </div>

            {testResult && (
              <div
                className={`p-3 rounded-md text-xs border ${
                  testResult.success
                    ? 'bg-zinc-50 border-zinc-300 text-zinc-800'
                    : 'bg-red-50 border-red-200 text-red-700'
                }`}
              >
                <div className="flex items-center gap-1.5 font-medium">
                  {testResult.success ? (
                    <CheckCircle2 className="w-3.5 h-3.5 text-zinc-800" />
                  ) : (
                    <AlertCircle className="w-3.5 h-3.5 text-red-600" />
                  )}
                  <span>{testResult.message}</span>
                </div>
                {testResult.diagnostics && (
                  <pre className="mt-2 text-[10px] bg-white p-2 rounded border border-zinc-200 overflow-x-auto text-zinc-600">
                    {JSON.stringify(testResult.diagnostics, null, 2)}
                  </pre>
                )}
              </div>
            )}

            <DialogFooter className="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-end gap-2">
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={() => setIsTestOpen(false)}
                className="h-7.5 text-xs border-zinc-200 text-zinc-700"
              >
                Close
              </Button>
              <Button
                type="submit"
                size="sm"
                disabled={testMutation.isPending || !isValidEmail(testRecipient)}
                className="h-7.5 text-xs bg-zinc-900 hover:bg-black text-white"
              >
                {testMutation.isPending ? 'Testing Socket...' : 'Send Test Ping'}
              </Button>
            </DialogFooter>
          </form>
      </Dialog>

      {/* Delete Confirmation Dialog */}
      <Dialog open={isDeleteOpen} onOpenChange={setIsDeleteOpen} maxWidth="max-w-sm">
        <DialogHeader>
          <DialogTitle className="text-sm font-semibold text-zinc-900">Delete Configuration</DialogTitle>
          <DialogDescription className="text-xs text-zinc-500">
            Are you sure you want to delete this SMTP setting? This action cannot be undone.
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
