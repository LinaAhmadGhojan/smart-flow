import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  withCredentials: true,
})

// Add auth token to requests; let browser set multipart boundary for FormData
api.interceptors.request.use((config) => {
  const token = sessionStorage.getItem('authToken')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  if (typeof FormData !== 'undefined' && config.data instanceof FormData) {
    // Let the browser set multipart boundary (default JSON Content-Type breaks uploads)
    const headers = config.headers as any
    if (headers?.set) {
      headers.set('Content-Type', undefined)
    } else if (headers) {
      delete headers['Content-Type']
      delete headers['content-type']
    }
  }
  return config
})

// Handle auth errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      sessionStorage.removeItem('authToken')
      sessionStorage.removeItem('adminLoggedIn')
      window.location.href = '/admin'
    }
    return Promise.reject(error)
  }
)

export default api

export async function fetchAdminPdf(path: string): Promise<Blob> {
  const res = await api.get(path, {
    responseType: 'blob',
    headers: { Accept: 'application/pdf' },
  })
  const type = res.headers['content-type'] || ''
  if (type.includes('application/json')) {
    const text = await (res.data as Blob).text()
    const err = JSON.parse(text)
    throw new Error(err.message || 'PDF error')
  }
  return new Blob([res.data], { type: 'application/pdf' })
}

export async function fetchAdminHtml(path: string): Promise<string> {
  const res = await api.get(path, {
    responseType: 'text',
    headers: { Accept: 'text/html' },
  })
  const type = res.headers['content-type'] || ''
  if (type.includes('application/json')) {
    const err = JSON.parse(res.data)
    throw new Error(err.message || 'HTML error')
  }
  return res.data as string
}

export function downloadBlob(blob: Blob, filename: string) {
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  document.body.appendChild(a)
  a.click()
  a.remove()
  window.URL.revokeObjectURL(url)
}

export function openBlobInNewTab(blob: Blob) {
  const url = window.URL.createObjectURL(blob)
  const win = window.open(url, '_blank', 'noopener,noreferrer')
  if (!win) {
    window.URL.revokeObjectURL(url)
    throw new Error('تعذر فتح التبويب — اسمح بالنوافذ المنبثقة')
  }
  setTimeout(() => window.URL.revokeObjectURL(url), 120000)
}
