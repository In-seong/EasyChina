const isIOS = () => /iPhone|iPad|iPod/i.test(navigator.userAgent)
const isAndroid = () => /Android/i.test(navigator.userAgent)
const isMobile = () => isIOS() || isAndroid()
const isInApp = () => !!(window.webkit?.messageHandlers?.iOSBridge || (window as any).AndroidBridge)

/**
 * 외부 URL 열기 (웹뷰 호환)
 * 앱 내부면 JS Bridge로 네이티브에서 열기
 * 브라우저면 일반 방식
 */
function openExternalUrl(url: string) {
  if (window.webkit?.messageHandlers?.iOSBridge) {
    // iOS: Bridge로 네이티브에서 Safari/앱 열기 (딕셔너리로 전달)
    window.webkit.messageHandlers.iOSBridge.postMessage({ action: 'openExternalUrl', url })
  } else if ((window as any).AndroidBridge) {
    // Android: Bridge (JSON 문자열로 전달)
    (window as any).AndroidBridge.postMessage(
      JSON.stringify({ action: 'openExternalUrl', data: { url } })
    )
  } else {
    // 브라우저: 새 탭
    window.open(url, '_blank')
  }
}

/**
 * 앱 URL Scheme 열기 (iosamap://, diditaxi:// 등)
 */
function openAppScheme(appUrl: string, webFallbackUrl: string) {
  if (isInApp()) {
    // 웹뷰: Bridge로 앱 열기 요청
    openExternalUrl(appUrl)
    // 앱이 없으면 fallback (2초 후)
    setTimeout(() => openExternalUrl(webFallbackUrl), 2000)
  } else if (isMobile()) {
    // 모바일 브라우저
    window.location.href = appUrl
    setTimeout(() => { window.open(webFallbackUrl, '_blank') }, 1500)
  } else {
    // PC
    window.open(webFallbackUrl, '_blank')
  }
}

/**
 * AMap 길찾기
 */
export function startAMapNavigation(
  dstLat: number, dstLng: number, dstName: string,
  srcLat?: number, srcLng?: number, srcName?: string
) {
  const dst = encodeURIComponent(dstName)

  let webUrl = `https://uri.amap.com/navigation?to=${dstLng},${dstLat},${dst}&mode=car&src=EasyChina`
  if (srcLat && srcLng && srcName) {
    webUrl += `&from=${srcLng},${srcLat},${encodeURIComponent(srcName)}`
  }

  // 출발지가 있으면 웹 URL (앱은 출발지 무시)
  if (srcLat && srcLng) {
    openExternalUrl(webUrl)
    return
  }

  // 출발지 없음 → 앱 시도
  const appUrl = isIOS()
    ? `iosamap://path?sourceApplication=EasyChina&dlat=${dstLat}&dlon=${dstLng}&dname=${dst}&dev=0&t=0`
    : `amapuri://route/plan/?dlat=${dstLat}&dlon=${dstLng}&dname=${dst}&dev=0&t=0`

  openAppScheme(appUrl, webUrl)
}

/**
 * DiDi 택시 호출
 * DiDi는 목적지 파라미터를 공식 지원하지 않음
 * → 목적지를 클립보드에 복사 후 앱 열기
 */
export async function callDidiTaxi(
  dstLat: number, dstLng: number, dstName: string,
  srcLat?: number, srcLng?: number, srcName?: string
): Promise<void> {
  // 목적지 중국어를 클립보드에 복사
  try {
    await navigator.clipboard.writeText(dstName)
  } catch {
    // clipboard API 실패 시 무시
  }

  const didiAppUrl = isIOS() ? 'diditaxi://' : 'didipublic://'
  const didiWebUrl = 'https://www.didiglobal.com/download'

  openAppScheme(didiAppUrl, didiWebUrl)
}

export type NavOption = 'amap' | 'didi' | 'web'

export function getNavOptions(): { key: NavOption; label: string; icon: string; desc: string }[] {
  return [
    { key: 'amap', label: '고덕지도', icon: '🗺', desc: '직접 네비게이션' },
    { key: 'didi', label: 'DiDi 택시', icon: '🚕', desc: '택시 호출' },
    { key: 'web', label: '웹 길찾기', icon: '🌐', desc: '브라우저에서 열기' },
  ]
}
