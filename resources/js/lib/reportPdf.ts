import { downloadBlob, fetchAdminPdf } from '@/lib/api'

export function reportHtmlPath(reportId: number | string): string {
  return `/admin/reports/${reportId}/html`
}

export function reportPdfPath(reportId: number | string): string {
  return `/admin/reports/${reportId}/pdf`
}

export function reportPdfFilename(reportId: number | string, reportNo?: string): string {
  return `${reportNo || `report-${reportId}`}.pdf`
}

export async function downloadReportPdf(
  reportId: number | string,
  reportNo?: string,
): Promise<void> {
  const blob = await fetchAdminPdf(reportPdfPath(reportId))
  downloadBlob(blob, reportPdfFilename(reportId, reportNo))
}
