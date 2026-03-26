const isIOS = () => /iPhone|iPad|iPod/i.test(navigator.userAgent)
const isAndroid = () => /Android/i.test(navigator.userAgent)
const isMobile = () => isIOS() || isAndroid()

/**
 * 외부 URL 열기 (웹뷰 호환)
 * window.open은 웹뷰에서 차단되므로 <a> 태그 클릭으로 처리
 */
function openUrl(url: string) {
  const a = document.createElement('a')
  a.href = url
  a.target = '_blank'
  a.rel = 'noopener noreferrer'
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
}

/**
 * 앱 URL Scheme 열기 시도 → 실패 시 웹 fallback
 */
function tryOpenApp(appUrl: string, webUrl: string, timeout = 1500) {
  if (isMobile()) {
    // 앱 열기 시도
    window.location.href = appUrl

    // 앱이 안 열리면 웹으로 (앱이 열리면 이 타이머는 실행 안됨)
    setTimeout(() => {
      openUrl(webUrl)
    }, timeout)
  } else {
    openUrl(webUrl)
  }
}

/**
 * AMap 길찾기
 * 출발지가 있으면 웹 URL로 열기 (앱 URL Scheme은 출발지 지정 불가)
 * 출발지가 없으면 앱 시도 → 웹 fallback
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

  // 출발지가 지정된 경우 → 웹 URL만 사용 (앱은 출발지 무시함)
  if (srcLat && srcLng) {
    openUrl(webUrl)
    return
  }

  // 출발지 없음 (현재 위치 → 목적지) → 앱 시도
  const appUrl = isIOS()
    ? `iosamap://path?sourceApplication=EasyChina&dlat=${dstLat}&dlon=${dstLng}&dname=${dst}&dev=0&t=0`
    : `amapuri://route/plan/?dlat=${dstLat}&dlon=${dstLng}&dname=${dst}&dev=0&t=0`

  tryOpenApp(appUrl, webUrl)
}

/**
 * DiDi(滴滴出行) 택시 호출
 */
export function callDidiTaxi(
  dstLat: number, dstLng: number, dstName: string,
  srcLat?: number, srcLng?: number, srcName?: string
) {
  const dst = encodeURIComponent(dstName)

  let didiAppUrl: string
  if (isIOS()) {
    didiAppUrl = `diditaxi://passenger?action=create_order&dlat=${dstLat}&dlng=${dstLng}&dname=${dst}`
    if (srcLat && srcLng) {
      didiAppUrl += `&flat=${srcLat}&flng=${srcLng}&fname=${encodeURIComponent(srcName || '')}`
    }
  } else {
    didiAppUrl = `didipublic://passenger?action=create_order&dlat=${dstLat}&dlng=${dstLng}&dname=${dst}`
    if (srcLat && srcLng) {
      didiAppUrl += `&flat=${srcLat}&flng=${srcLng}&fname=${encodeURIComponent(srcName || '')}`
    }
  }

  const didiWebUrl = `https://common.diditaxi.com.cn/general/default/redirect?dlat=${dstLat}&dlng=${dstLng}&dname=${dst}`

  tryOpenApp(didiAppUrl, didiWebUrl)
}

export type NavOption = 'amap' | 'didi' | 'web'

export function getNavOptions(): { key: NavOption; label: string; icon: string; desc: string }[] {
  return [
    { key: 'amap', label: '고덕지도', icon: '🗺', desc: '직접 네비게이션' },
    { key: 'didi', label: 'DiDi 택시', icon: '🚕', desc: '택시 호출' },
    { key: 'web', label: '웹 길찾기', icon: '🌐', desc: '브라우저에서 열기' },
  ]
}
