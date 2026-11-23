import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { ShieldCheck, Lock, Mail, AlertCircle, ArrowRight } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { authApi } from '@/api/services'
import { useAuthStore } from '@/store/authStore'

const loginSchema = z.object({
  email: z.string().email('Please enter a valid email address'),
  password: z.string().min(4, 'Password must be at least 4 characters'),
})

type LoginFormValues = z.infer<typeof loginSchema>

export function Login() {
  const navigate = useNavigate()
  const { setAuth } = useAuthStore()
  const [serverError, setServerError] = useState<string | null>(null)
  const [loading, setLoading] = useState(false)

  const {
    register,
    handleSubmit,
    setValue,
    formState: { errors },
  } = useForm<LoginFormValues>({
    resolver: zodResolver(loginSchema),
    defaultValues: {
      email: 'admin@avoerp.com',
      password: 'admin123',
    },
  })

  const onSubmit = async (values: LoginFormValues) => {
    setServerError(null)
    setLoading(true)
    try {
      const data = await authApi.login(values)
      setAuth(data.user, data.token)
      if (
        data.user.role === 'admin' ||
        data.user.role_type === 'Admin' ||
        data.user.role_type === 'Executive' ||
        data.user.level === 'L3'
      ) {
        navigate('/users')
      } else if (['Sales', 'Finance', 'Project'].includes(data.user.role_type)) {
        navigate('/sales/leads')
      } else {
        navigate('/dashboard')
      }
    } catch (err: any) {
      setServerError(err.message || 'Authentication failed. Please check credentials.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-zinc-950 flex flex-col justify-center items-center p-4 select-none">
      <div className="w-full max-w-sm">
        {/* Brand Header */}
        <div className="text-center mb-6">
          <div className="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-zinc-900 border border-zinc-800 text-white mb-2 shadow-xs">
            <ShieldCheck className="w-5 h-5 text-zinc-100" />
          </div>
          <h1 className="text-lg font-bold text-white tracking-tight">
            AVO ERP
          </h1>
          <p className="text-xs text-zinc-400 mt-0.5">User & Access Management</p>
        </div>

        <Card className="border-zinc-800 bg-white text-zinc-950 shadow-xl">
          <CardHeader className="pb-3 border-b border-zinc-100">
            <CardTitle className="text-sm font-semibold text-zinc-900">Sign In</CardTitle>
            <CardDescription className="text-xs text-zinc-500">
              Enter your enterprise credentials to access the system
            </CardDescription>
          </CardHeader>
          <CardContent className="pt-3">
            {serverError && (
              <div className="mb-3 p-2 bg-zinc-50 border border-rose-200 rounded text-xs text-rose-700 flex items-center">
                <AlertCircle className="w-3.5 h-3.5 mr-1.5 shrink-0" />
                <span>{serverError}</span>
              </div>
            )}

            <form onSubmit={handleSubmit(onSubmit)} className="space-y-3">
              <div className="space-y-1">
                <Label htmlFor="email" className="text-xs font-medium text-zinc-700">Email Address</Label>
                <div className="relative">
                  <Mail className="absolute left-2.5 top-2.5 h-3.5 w-3.5 text-zinc-400" />
                  <Input
                    id="email"
                    type="email"
                    placeholder="name@company.com"
                    className="pl-8 text-xs h-8 bg-zinc-50/50 border-zinc-200"
                    error={errors.email?.message}
                    {...register('email')}
                  />
                </div>
              </div>

              <div className="space-y-1">
                <Label htmlFor="password" className="text-xs font-medium text-zinc-700">Password</Label>
                <div className="relative">
                  <Lock className="absolute left-2.5 top-2.5 h-3.5 w-3.5 text-zinc-400" />
                  <Input
                    id="password"
                    type="password"
                    placeholder="••••••••"
                    className="pl-8 text-xs h-8 bg-zinc-50/50 border-zinc-200"
                    error={errors.password?.message}
                    {...register('password')}
                  />
                </div>
              </div>

              <Button
                type="submit"
                className="w-full h-8 text-xs bg-zinc-900 hover:bg-black text-white font-medium mt-1"
                isLoading={loading}
              >
                Sign In
                <ArrowRight className="w-3.5 h-3.5 ml-1.5" />
              </Button>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
