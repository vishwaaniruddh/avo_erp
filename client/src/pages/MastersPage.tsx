import { useState, useMemo } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import {
  MapPin,
  Globe,
  Compass,
  Truck,
  Building,
  Plus,
  Search,
  Edit2,
  Trash2,
  CheckCircle2,
  AlertCircle,
  ExternalLink,
} from 'lucide-react'
import { mastersApi } from '@/api/services'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Dialog, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { sanitizePhoneInput, isValidPhone, getPhoneError } from '@/lib/validation'
import type { CityMaster, CountryMaster, CourierMaster, StateMaster, ZoneMaster } from '@/types'

type MasterTab = 'cities' | 'states' | 'countries' | 'zones' | 'couriers'

const TABS: { id: MasterTab; label: string; icon: any; desc: string }[] = [
  { id: 'cities', label: 'City', icon: Building, desc: 'City directories, linked states & postal PIN codes' },
  { id: 'states', label: 'State', icon: MapPin, desc: 'States and territories linked to national jurisdictions' },
  { id: 'countries', label: 'Country', icon: Globe, desc: 'Global country registry, ISO codes & international dial codes' },
  { id: 'zones', label: 'Zone', icon: Compass, desc: 'Operational delivery & commercial coverage zones' },
  { id: 'couriers', label: 'Courier', icon: Truck, desc: 'Logistics partners & live AWB consignment tracking patterns' },
]

export function MastersPage() {
  const { entity } = useParams<{ entity?: string }>()
  const navigate = useNavigate()
  const queryClient = useQueryClient()

  // Active Tab
  const activeTab: MasterTab =
    entity && ['cities', 'states', 'countries', 'zones', 'couriers'].includes(entity)
      ? (entity as MasterTab)
      : 'cities'

  const handleSelectTab = (tab: MasterTab) => {
    navigate(`/masters/${tab}`)
  }

  const [search, setSearch] = useState('')
  const [feedback, setFeedback] = useState<{ type: 'success' | 'error'; message: string } | null>(null)

  // Modals
  const [isFormOpen, setIsFormOpen] = useState(false)
  const [isDeleteOpen, setIsDeleteOpen] = useState(false)
  const [deletingId, setDeletingId] = useState<number | null>(null)
  const [editingItem, setEditingItem] = useState<any | null>(null)

  // Form Fields
  const [countryName, setCountryName] = useState('')
  const [countryCode, setCountryCode] = useState('')
  const [countryPhoneCode, setCountryPhoneCode] = useState('')
  const [countryCurrency, setCountryCurrency] = useState('')

  const [stateName, setStateName] = useState('')
  const [stateCode, setStateCode] = useState('')
  const [stateCountryId, setStateCountryId] = useState<number>(1)

  const [cityName, setCityName] = useState('')
  const [cityPincode, setCityPincode] = useState('')
  const [cityStateId, setCityStateId] = useState<number>(1)

  const [zoneCode, setZoneCode] = useState('')
  const [zoneName, setZoneName] = useState('')
  const [zoneDesc, setZoneDesc] = useState('')

  const [courierName, setCourierName] = useState('')
  const [courierTracking, setCourierTracking] = useState('')
  const [courierContact, setCourierContact] = useState('')

  const [isActive, setIsActive] = useState(true)

  // Queries
  const { data: countries = [], isLoading: loadingCountries } = useQuery({
    queryKey: ['masters-countries'],
    queryFn: () => mastersApi.getCountries(),
  })

  const { data: states = [], isLoading: loadingStates } = useQuery({
    queryKey: ['masters-states'],
    queryFn: () => mastersApi.getStates(),
  })

  const { data: cities = [], isLoading: loadingCities } = useQuery({
    queryKey: ['masters-cities'],
    queryFn: () => mastersApi.getCities(),
  })

  const { data: zones = [], isLoading: loadingZones } = useQuery({
    queryKey: ['masters-zones'],
    queryFn: () => mastersApi.getZones(),
  })

  const { data: couriers = [], isLoading: loadingCouriers } = useQuery({
    queryKey: ['masters-couriers'],
    queryFn: () => mastersApi.getCouriers(),
  })

  // Mutations
  const createMutation = useMutation({
    mutationFn: async (data: any) => {
      switch (activeTab) {
        case 'countries':
          return mastersApi.createCountry(data)
        case 'states':
          return mastersApi.createState(data)
        case 'cities':
          return mastersApi.createCity(data)
        case 'zones':
          return mastersApi.createZone(data)
        case 'couriers':
          return mastersApi.createCourier(data)
      }
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [`masters-${activeTab}`] })
      setIsFormOpen(false)
      setFeedback({ type: 'success', message: `${activeTab.slice(0, -1)} created successfully.` })
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.response?.data?.message || err.message || 'Operation failed.' })
    },
  })

  const updateMutation = useMutation({
    mutationFn: async ({ id, data }: { id: number; data: any }) => {
      switch (activeTab) {
        case 'countries':
          return mastersApi.updateCountry(id, data)
        case 'states':
          return mastersApi.updateState(id, data)
        case 'cities':
          return mastersApi.updateCity(id, data)
        case 'zones':
          return mastersApi.updateZone(id, data)
        case 'couriers':
          return mastersApi.updateCourier(id, data)
      }
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [`masters-${activeTab}`] })
      setIsFormOpen(false)
      setFeedback({ type: 'success', message: `${activeTab.slice(0, -1)} updated successfully.` })
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.response?.data?.message || err.message || 'Operation failed.' })
    },
  })

  const deleteMutation = useMutation({
    mutationFn: async (id: number) => {
      switch (activeTab) {
        case 'countries':
          return mastersApi.deleteCountry(id)
        case 'states':
          return mastersApi.deleteState(id)
        case 'cities':
          return mastersApi.deleteCity(id)
        case 'zones':
          return mastersApi.deleteZone(id)
        case 'couriers':
          return mastersApi.deleteCourier(id)
      }
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [`masters-${activeTab}`] })
      setIsDeleteOpen(false)
      setDeletingId(null)
      setFeedback({ type: 'success', message: 'Item deleted successfully.' })
    },
    onError: (err: any) => {
      setFeedback({ type: 'error', message: err.response?.data?.message || err.message || 'Deletion failed.' })
    },
  })

  // Open Form
  const handleOpenCreate = () => {
    setEditingItem(null)
    setIsActive(true)

    if (activeTab === 'countries') {
      setCountryName('')
      setCountryCode('')
      setCountryPhoneCode('+91')
      setCountryCurrency('INR')
    } else if (activeTab === 'states') {
      setStateName('')
      setStateCode('')
      setStateCountryId(countries[0]?.id || 1)
    } else if (activeTab === 'cities') {
      setCityName('')
      setCityPincode('')
      setCityStateId(states[0]?.id || 1)
    } else if (activeTab === 'zones') {
      setZoneCode('')
      setZoneName('')
      setZoneDesc('')
    } else if (activeTab === 'couriers') {
      setCourierName('')
      setCourierTracking('https://example.com/track?awb={tracking_number}')
      setCourierContact('')
    }
    setIsFormOpen(true)
  }

  const handleOpenEdit = (item: any) => {
    setEditingItem(item)
    setIsActive(Boolean(item.is_active))

    if (activeTab === 'countries') {
      setCountryName(item.name)
      setCountryCode(item.code)
      setCountryPhoneCode(item.phone_code || '')
      setCountryCurrency(item.currency || '')
    } else if (activeTab === 'states') {
      setStateName(item.name)
      setStateCode(item.code || '')
      setStateCountryId(item.country_id)
    } else if (activeTab === 'cities') {
      setCityName(item.name)
      setCityPincode(item.pincode || '')
      setCityStateId(item.state_id)
    } else if (activeTab === 'zones') {
      setZoneCode(item.code)
      setZoneName(item.name)
      setZoneDesc(item.description || '')
    } else if (activeTab === 'couriers') {
      setCourierName(item.name)
      setCourierTracking(item.tracking_url_template || '')
      setCourierContact(item.contact_number || '')
    }
    setIsFormOpen(true)
  }

  const handleSubmitForm = (e: React.FormEvent) => {
    e.preventDefault()
    let payload: any = { is_active: isActive ? 1 : 0 }

    if (activeTab === 'countries') {
      payload = {
        ...payload,
        name: countryName.trim(),
        code: countryCode.trim().toUpperCase(),
        phone_code: countryPhoneCode.trim() || null,
        currency: countryCurrency.trim().toUpperCase() || null,
      }
    } else if (activeTab === 'states') {
      payload = {
        ...payload,
        name: stateName.trim(),
        code: stateCode.trim().toUpperCase() || null,
        country_id: Number(stateCountryId),
      }
    } else if (activeTab === 'cities') {
      payload = {
        ...payload,
        name: cityName.trim(),
        pincode: cityPincode.trim() || null,
        state_id: Number(cityStateId),
      }
    } else if (activeTab === 'zones') {
      payload = {
        ...payload,
        code: zoneCode.trim().toUpperCase(),
        name: zoneName.trim(),
        description: zoneDesc.trim() || null,
      }
    } else if (activeTab === 'couriers') {
      if (courierContact.trim() && !isValidPhone(courierContact)) {
        setFeedback({ type: 'error', message: 'Please enter a valid 10-15 digit phone number for courier helpline.' })
        return
      }
      payload = {
        ...payload,
        name: courierName.trim(),
        tracking_url_template: courierTracking.trim() || null,
        contact_number: courierContact.trim() || null,
      }
    }

    if (editingItem) {
      updateMutation.mutate({ id: editingItem.id, data: payload })
    } else {
      createMutation.mutate(payload)
    }
  }

  // Filtered master items
  const currentItems = useMemo(() => {
    const q = search.toLowerCase()
    switch (activeTab) {
      case 'cities':
        return cities.filter(
          (c) =>
            c.name.toLowerCase().includes(q) ||
            (c.state_name && c.state_name.toLowerCase().includes(q)) ||
            (c.pincode && c.pincode.includes(q))
        )
      case 'states':
        return states.filter(
          (s) =>
            s.name.toLowerCase().includes(q) ||
            (s.code && s.code.toLowerCase().includes(q)) ||
            (s.country_name && s.country_name.toLowerCase().includes(q))
        )
      case 'countries':
        return countries.filter(
          (c) =>
            c.name.toLowerCase().includes(q) ||
            c.code.toLowerCase().includes(q) ||
            (c.currency && c.currency.toLowerCase().includes(q))
        )
      case 'zones':
        return zones.filter(
          (z) =>
            z.name.toLowerCase().includes(q) ||
            z.code.toLowerCase().includes(q) ||
            (z.description && z.description.toLowerCase().includes(q))
        )
      case 'couriers':
        return couriers.filter(
          (cr) =>
            cr.name.toLowerCase().includes(q) ||
            (cr.contact_number && cr.contact_number.includes(q))
        )
    }
  }, [activeTab, search, cities, states, countries, zones, couriers])

  const isLoading =
    activeTab === 'cities'
      ? loadingCities
      : activeTab === 'states'
      ? loadingStates
      : activeTab === 'countries'
      ? loadingCountries
      : activeTab === 'zones'
      ? loadingZones
      : loadingCouriers

  const activeTabMeta = TABS.find((t) => t.id === activeTab)!

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3.5 px-4 rounded-lg border border-zinc-200 shadow-2xs">
        <div>
          <div className="flex items-center gap-2">
            <h1 className="text-sm font-semibold tracking-tight text-zinc-900">Masters Directory</h1>
            <span className="text-[10px] font-medium bg-zinc-100 text-zinc-700 px-1.5 py-0.5 rounded border border-zinc-200">
              Superadmin
            </span>
          </div>
          <p className="text-[11px] text-zinc-500 mt-0.5">{activeTabMeta.desc}</p>
        </div>
        <Button onClick={handleOpenCreate} className="h-7.5 px-3 text-xs bg-zinc-900 hover:bg-black text-white shrink-0">
          <Plus className="w-3.5 h-3.5 mr-1.5" />
          Add {activeTabMeta.label}
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

      {/* Tabs */}
      <div className="flex items-center gap-1.5 border-b border-zinc-200 pb-2 overflow-x-auto">
        {TABS.map((tab) => {
          const Icon = tab.icon
          const isActiveTab = activeTab === tab.id
          let count = 0
          if (tab.id === 'cities') count = cities.length
          if (tab.id === 'states') count = states.length
          if (tab.id === 'countries') count = countries.length
          if (tab.id === 'zones') count = zones.length
          if (tab.id === 'couriers') count = couriers.length

          return (
            <button
              key={tab.id}
              onClick={() => handleSelectTab(tab.id)}
              className={`flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-medium transition-colors whitespace-nowrap ${
                isActiveTab
                  ? 'bg-zinc-900 text-white'
                  : 'bg-white hover:bg-zinc-100 text-zinc-600 border border-zinc-200'
              }`}
            >
              <Icon className="w-3.5 h-3.5" />
              <span>{tab.label}</span>
              <span
                className={`text-[10px] px-1.5 py-0.2 rounded-full font-mono ${
                  isActiveTab ? 'bg-zinc-700 text-zinc-200' : 'bg-zinc-100 text-zinc-600'
                }`}
              >
                {count}
              </span>
            </button>
          )
        })}
      </div>

      {/* Search Input */}
      <Card className="border-zinc-200 shadow-2xs">
        <CardContent className="p-3">
          <div className="relative">
            <Search className="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-zinc-400" />
            <Input
              type="text"
              placeholder={`Search ${activeTabMeta.label.toLowerCase()} records by name or code...`}
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="h-8 pl-8 text-xs bg-zinc-50/50 border-zinc-200 focus:bg-white"
            />
          </div>
        </CardContent>
      </Card>

      {/* Dynamic Master Table */}
      <Card className="border-zinc-200 shadow-2xs overflow-hidden">
        <div className="overflow-x-auto">
          <Table className="min-w-[700px]">
            <TableHeader className="bg-zinc-50/80 border-b border-zinc-200">
              <TableRow>
                <TableHead className="font-semibold text-zinc-600 text-[11px] w-10 text-center">#</TableHead>

                {activeTab === 'cities' && (
                  <>
                    <TableHead className="font-semibold text-zinc-600 text-[11px]">City Name</TableHead>
                    <TableHead className="font-semibold text-zinc-600 text-[11px]">State</TableHead>
                    <TableHead className="font-semibold text-zinc-600 text-[11px]">Country</TableHead>
                    <TableHead className="font-semibold text-zinc-600 text-[11px]">PIN Code</TableHead>
                  </>
                )}

                {activeTab === 'states' && (
                  <>
                    <TableHead className="font-semibold text-zinc-600 text-[11px]">State Name</TableHead>
                    <TableHead className="font-semibold text-zinc-600 text-[11px]">State Code</TableHead>
                    <TableHead className="font-semibold text-zinc-600 text-[11px]">Country</TableHead>
                  </>
                )}

                {activeTab === 'countries' && (
                  <>
                    <TableHead className="font-semibold text-zinc-600 text-[11px]">Country Name</TableHead>
                    <TableHead className="font-semibold text-zinc-600 text-[11px]">ISO Code</TableHead>
                    <TableHead className="font-semibold text-zinc-600 text-[11px]">Phone Dial Code</TableHead>
                    <TableHead className="font-semibold text-zinc-600 text-[11px]">Currency</TableHead>
                  </>
                )}

                {activeTab === 'zones' && (
                  <>
                    <TableHead className="font-semibold text-zinc-600 text-[11px]">Zone Code</TableHead>
                    <TableHead className="font-semibold text-zinc-600 text-[11px]">Zone Name</TableHead>
                    <TableHead className="font-semibold text-zinc-600 text-[11px]">Description</TableHead>
                  </>
                )}

                {activeTab === 'couriers' && (
                  <>
                    <TableHead className="font-semibold text-zinc-600 text-[11px]">Courier Partner</TableHead>
                    <TableHead className="font-semibold text-zinc-600 text-[11px]">Helpline Contact</TableHead>
                    <TableHead className="font-semibold text-zinc-600 text-[11px]">Tracking URL Template</TableHead>
                  </>
                )}

                <TableHead className="font-semibold text-zinc-600 text-[11px] text-center">Status</TableHead>
                <TableHead className="text-right font-semibold text-zinc-600 text-[11px]">Action</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {isLoading ? (
                <TableRow>
                  <TableCell colSpan={8} className="text-center py-8 text-xs text-zinc-500">
                    Loading {activeTabMeta.label.toLowerCase()} records...
                  </TableCell>
                </TableRow>
              ) : currentItems.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={8} className="text-center py-8 text-xs text-zinc-500">
                    No {activeTabMeta.label.toLowerCase()} records found. Click "+ Add {activeTabMeta.label}" to register new data.
                  </TableCell>
                </TableRow>
              ) : (
                currentItems.map((item: any, idx: number) => (
                  <TableRow key={item.id} className="hover:bg-zinc-50/60 border-b border-zinc-100 transition-colors">
                    <TableCell className="text-center font-mono text-[11px] text-zinc-400 whitespace-nowrap">
                      {idx + 1}
                    </TableCell>

                    {/* Cities */}
                    {activeTab === 'cities' && (
                      <>
                        <TableCell className="whitespace-nowrap font-medium text-xs text-zinc-900">
                          {item.name}
                        </TableCell>
                        <TableCell className="whitespace-nowrap text-xs text-zinc-700">
                          {item.state_name || '—'}
                        </TableCell>
                        <TableCell className="whitespace-nowrap text-xs text-zinc-500">
                          {item.country_name || 'India'}
                        </TableCell>
                        <TableCell className="whitespace-nowrap font-mono text-[11px] text-zinc-600">
                          {item.pincode || '—'}
                        </TableCell>
                      </>
                    )}

                    {/* States */}
                    {activeTab === 'states' && (
                      <>
                        <TableCell className="whitespace-nowrap font-medium text-xs text-zinc-900">
                          {item.name}
                        </TableCell>
                        <TableCell className="whitespace-nowrap font-mono text-[11px] text-zinc-700">
                          {item.code || '—'}
                        </TableCell>
                        <TableCell className="whitespace-nowrap text-xs text-zinc-600">
                          {item.country_name || 'India'}
                        </TableCell>
                      </>
                    )}

                    {/* Countries */}
                    {activeTab === 'countries' && (
                      <>
                        <TableCell className="whitespace-nowrap font-medium text-xs text-zinc-900">
                          {item.name}
                        </TableCell>
                        <TableCell className="whitespace-nowrap font-mono text-[11px] text-zinc-700">
                          {item.code}
                        </TableCell>
                        <TableCell className="whitespace-nowrap font-mono text-[11px] text-zinc-600">
                          {item.phone_code || '—'}
                        </TableCell>
                        <TableCell className="whitespace-nowrap font-mono text-[11px] text-zinc-600">
                          {item.currency || '—'}
                        </TableCell>
                      </>
                    )}

                    {/* Zones */}
                    {activeTab === 'zones' && (
                      <>
                        <TableCell className="whitespace-nowrap font-mono text-[11px] font-semibold text-zinc-900">
                          {item.code}
                        </TableCell>
                        <TableCell className="whitespace-nowrap text-xs text-zinc-800">
                          {item.name}
                        </TableCell>
                        <TableCell className="whitespace-nowrap text-xs text-zinc-500 max-w-sm truncate">
                          {item.description || '—'}
                        </TableCell>
                      </>
                    )}

                    {/* Couriers */}
                    {activeTab === 'couriers' && (
                      <>
                        <TableCell className="whitespace-nowrap font-medium text-xs text-zinc-900">
                          {item.name}
                        </TableCell>
                        <TableCell className="whitespace-nowrap font-mono text-[11px] text-zinc-700">
                          {item.contact_number || '—'}
                        </TableCell>
                        <TableCell className="whitespace-nowrap font-mono text-[11px] text-zinc-500 max-w-xs truncate">
                          {item.tracking_url_template || '—'}
                        </TableCell>
                      </>
                    )}

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
                          onClick={() => handleOpenEdit(item)}
                          className="h-6.5 w-6.5 p-0 text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100"
                          title="Edit"
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
                          title="Delete"
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
      <Dialog open={isFormOpen} onOpenChange={setIsFormOpen} maxWidth="max-w-md">
        <DialogHeader>
          <DialogTitle className="text-sm font-semibold text-zinc-900">
            {editingItem ? `Edit ${activeTabMeta.label}` : `Add New ${activeTabMeta.label}`}
          </DialogTitle>
          <DialogDescription className="text-xs text-zinc-500">
            Provide the configuration parameters for this {activeTabMeta.label.toLowerCase()} record.
          </DialogDescription>
        </DialogHeader>

        <form onSubmit={handleSubmitForm} className="space-y-3 mt-4">
            {/* Country Fields */}
            {activeTab === 'countries' && (
              <>
                <div>
                  <Label className="text-[11px] font-medium text-zinc-700">Country Name *</Label>
                  <Input
                    required
                    placeholder="e.g. India, United States"
                    value={countryName}
                    onChange={(e) => setCountryName(e.target.value)}
                    className="h-8 text-xs mt-1 bg-zinc-50/50"
                  />
                </div>
                <div className="grid grid-cols-3 gap-2">
                  <div>
                    <Label className="text-[11px] font-medium text-zinc-700">ISO Code *</Label>
                    <Input
                      required
                      placeholder="IN"
                      value={countryCode}
                      onChange={(e) => setCountryCode(e.target.value)}
                      className="h-8 text-xs mt-1 font-mono uppercase bg-zinc-50/50"
                    />
                  </div>
                  <div>
                    <Label className="text-[11px] font-medium text-zinc-700">Dial Code</Label>
                    <Input
                      placeholder="+91"
                      value={countryPhoneCode}
                      onChange={(e) => setCountryPhoneCode(e.target.value)}
                      className="h-8 text-xs mt-1 font-mono bg-zinc-50/50"
                    />
                  </div>
                  <div>
                    <Label className="text-[11px] font-medium text-zinc-700">Currency</Label>
                    <Input
                      placeholder="INR"
                      value={countryCurrency}
                      onChange={(e) => setCountryCurrency(e.target.value)}
                      className="h-8 text-xs mt-1 font-mono uppercase bg-zinc-50/50"
                    />
                  </div>
                </div>
              </>
            )}

            {/* State Fields */}
            {activeTab === 'states' && (
              <>
                <div>
                  <Label className="text-[11px] font-medium text-zinc-700">State Name *</Label>
                  <Input
                    required
                    placeholder="e.g. Maharashtra, Karnataka"
                    value={stateName}
                    onChange={(e) => setStateName(e.target.value)}
                    className="h-8 text-xs mt-1 bg-zinc-50/50"
                  />
                </div>
                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <Label className="text-[11px] font-medium text-zinc-700">State Code</Label>
                    <Input
                      placeholder="e.g. MH, KA"
                      value={stateCode}
                      onChange={(e) => setStateCode(e.target.value)}
                      className="h-8 text-xs mt-1 font-mono uppercase bg-zinc-50/50"
                    />
                  </div>
                  <div>
                    <Label className="text-[11px] font-medium text-zinc-700">Country *</Label>
                    <select
                      value={stateCountryId}
                      onChange={(e) => setStateCountryId(Number(e.target.value))}
                      className="w-full h-8 px-2 text-xs mt-1 bg-zinc-50/50 border border-zinc-200 rounded-md text-zinc-800"
                    >
                      {countries.map((c) => (
                        <option key={c.id} value={c.id}>
                          {c.name} ({c.code})
                        </option>
                      ))}
                    </select>
                  </div>
                </div>
              </>
            )}

            {/* City Fields */}
            {activeTab === 'cities' && (
              <>
                <div>
                  <Label className="text-[11px] font-medium text-zinc-700">City Name *</Label>
                  <Input
                    required
                    placeholder="e.g. Mumbai, Bengaluru"
                    value={cityName}
                    onChange={(e) => setCityName(e.target.value)}
                    className="h-8 text-xs mt-1 bg-zinc-50/50"
                  />
                </div>
                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <Label className="text-[11px] font-medium text-zinc-700">State *</Label>
                    <select
                      value={cityStateId}
                      onChange={(e) => setCityStateId(Number(e.target.value))}
                      className="w-full h-8 px-2 text-xs mt-1 bg-zinc-50/50 border border-zinc-200 rounded-md text-zinc-800"
                    >
                      {states.map((s) => (
                        <option key={s.id} value={s.id}>
                          {s.name}
                        </option>
                      ))}
                    </select>
                  </div>
                  <div>
                    <Label className="text-[11px] font-medium text-zinc-700">PIN Code</Label>
                    <Input
                      placeholder="e.g. 400001"
                      value={cityPincode}
                      onChange={(e) => setCityPincode(e.target.value)}
                      className="h-8 text-xs mt-1 font-mono bg-zinc-50/50"
                    />
                  </div>
                </div>
              </>
            )}

            {/* Zone Fields */}
            {activeTab === 'zones' && (
              <>
                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <Label className="text-[11px] font-medium text-zinc-700">Zone Code *</Label>
                    <Input
                      required
                      placeholder="e.g. NORTH"
                      value={zoneCode}
                      onChange={(e) => setZoneCode(e.target.value)}
                      className="h-8 text-xs mt-1 font-mono uppercase bg-zinc-50/50"
                    />
                  </div>
                  <div>
                    <Label className="text-[11px] font-medium text-zinc-700">Zone Name *</Label>
                    <Input
                      required
                      placeholder="e.g. Northern Region"
                      value={zoneName}
                      onChange={(e) => setZoneName(e.target.value)}
                      className="h-8 text-xs mt-1 bg-zinc-50/50"
                    />
                  </div>
                </div>
                <div>
                  <Label className="text-[11px] font-medium text-zinc-700">Description</Label>
                  <Input
                    placeholder="Coverage details, primary hubs"
                    value={zoneDesc}
                    onChange={(e) => setZoneDesc(e.target.value)}
                    className="h-8 text-xs mt-1 bg-zinc-50/50"
                  />
                </div>
              </>
            )}

            {/* Courier Fields */}
            {activeTab === 'couriers' && (
              <>
                <div>
                  <Label className="text-[11px] font-medium text-zinc-700">Courier Partner Name *</Label>
                  <Input
                    required
                    placeholder="e.g. Blue Dart Express"
                    value={courierName}
                    onChange={(e) => setCourierName(e.target.value)}
                    className="h-8 text-xs mt-1 bg-zinc-50/50"
                  />
                </div>
                <div>
                  <Label className="text-[11px] font-medium text-zinc-700">Helpline / Contact Number</Label>
                  <Input
                    placeholder="e.g. 1860 233 1234 or +91 98200 12345"
                    value={courierContact}
                    maxLength={16}
                    onChange={(e) => setCourierContact(sanitizePhoneInput(e.target.value))}
                    className={`h-8 text-xs mt-1 font-mono bg-zinc-50/50 ${
                      courierContact && getPhoneError(courierContact, false) ? 'border-red-500 focus:ring-red-400' : ''
                    }`}
                  />
                  {courierContact && getPhoneError(courierContact, false) && (
                    <p className="text-[10px] text-red-600 mt-1 font-medium">{getPhoneError(courierContact, false)}</p>
                  )}
                </div>
                <div>
                  <Label className="text-[11px] font-medium text-zinc-700">
                    Tracking URL Template (use {'{tracking_number}'})
                  </Label>
                  <Input
                    placeholder="https://track.courier.com/?awb={tracking_number}"
                    value={courierTracking}
                    onChange={(e) => setCourierTracking(e.target.value)}
                    className="h-8 text-xs mt-1 font-mono text-[11px] bg-zinc-50/50"
                  />
                </div>
              </>
            )}

            {/* Active Toggle */}
            <div className="flex items-center gap-2 pt-2 border-t border-zinc-100">
              <input
                type="checkbox"
                id="isActiveMasterToggle"
                checked={isActive}
                onChange={(e) => setIsActive(e.target.checked)}
                className="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500"
              />
              <label htmlFor="isActiveMasterToggle" className="text-xs text-zinc-700 select-none">
                Active in operations
              </label>
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
                  (activeTab === 'couriers' && !!courierContact.trim() && !isValidPhone(courierContact))
                }
                className="h-7.5 text-xs bg-zinc-900 hover:bg-black text-white"
              >
                {createMutation.isPending || updateMutation.isPending
                  ? 'Saving...'
                  : editingItem
                  ? 'Save Changes'
                  : `Create ${activeTabMeta.label}`}
              </Button>
            </DialogFooter>
          </form>
      </Dialog>

      {/* Delete Confirmation */}
      <Dialog open={isDeleteOpen} onOpenChange={setIsDeleteOpen} maxWidth="max-w-sm">
        <DialogHeader>
          <DialogTitle className="text-sm font-semibold text-zinc-900">
            Delete {activeTabMeta.label}
          </DialogTitle>
          <DialogDescription className="text-xs text-zinc-500">
            Are you sure you want to delete this record? This action cannot be undone.
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
