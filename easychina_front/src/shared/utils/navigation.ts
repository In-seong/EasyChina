const isIOS = () => /iPhone|iPad|iPod/i.test(navigator.userAgent)
const isAndroid = () => /Android/i.test(navigator.userAgent)
const isMobile = () => isIOS() || isAndroid()

/**
 * 숨겨진 iframe으로 앱 열기 시도
 */
function tryOpenApp(url: string, fallbackUrl: string, timeout = 1500) {
  if (isMobile()) {
    const iframe = document.createElement('iframe')
    iframe.style.display = 'none'
    iframe.src = url
    document.body.appendChild(iframe)

    setTimeout(() => {
      document.body.removeChild(iframe)
      window.open(fallbackUrl, '_blank')
    }, timeout)
  } else {
    window.open(fallbackUrl, '_blank')
  }
}

/**
 * AMap 앱 또는 웹으로 길찾기
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

  // DiDi 앱 URL Scheme
  let didiAppUrl: string
  if (isIOS()) {
    // iOS DiDi URL Scheme
    didiAppUrl = `diditaxi://passenger?action=create_order&dlat=${dstLat}&dlng=${dstLng}&dname=${dst}`
    if (srcLat && srcLng) {
      didiAppUrl += `&flat=${srcLat}&flng=${srcLng}&fname=${encodeURIComponent(srcName || '')}`
    }
  } else {
    // Android DiDi URL Scheme
    didiAppUrl = `didipublic://passenger?action=create_order&dlat=${dstLat}&dlng=${dstLng}&dname=${dst}`
    if (srcLat && srcLng) {
      didiAppUrl += `&flat=${srcLat}&flng=${srcLng}&fname=${encodeURIComponent(srcName || '')}`
    }
  }

  // DiDi 웹 fallback (미니프로그램 or 다운로드 페이지)
  const didiWebUrl = `https://common.diditaxi.com.cn/general/default/redirect?dlat=${dstLat}&dlng=${dstLng}&dname=${dst}`

  tryOpenApp(didiAppUrl, didiWebUrl)
}

/**
 * 길찾기 옵션 선택 (AMap / DiDi / 웹)
 */
export type NavOption = 'amap' | 'didi' | 'web'

export function getNavOptions(): { key: NavOption; label: string; icon: string; desc: string }[] {
  return [
    { key: 'amap', label: '고덕지도', icon: '🗺', desc: '직접 네비게이션' },
    { key: 'didi', label: 'DiDi 택시', icon: '🚕', desc: '택시 호출' },
    { key: 'web', label: '웹 길찾기', icon: '🌐', desc: '브라우저에서 열기' },
  ]
}
