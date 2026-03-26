/**
 * AMap 앱 또는 웹으로 길찾기
 * @param dstLat 목적지 위도
 * @param dstLng 목적지 경도
 * @param dstName 목적지 중국어 이름
 * @param srcLat 출발지 위도 (optional)
 * @param srcLng 출발지 경도 (optional)
 * @param srcName 출발지 중국어 이름 (optional)
 */
export function startAMapNavigation(
  dstLat: number, dstLng: number, dstName: string,
  srcLat?: number, srcLng?: number, srcName?: string
) {
  const dst = encodeURIComponent(dstName)
  const isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent)
  const isAndroid = /Android/i.test(navigator.userAgent)

  // 웹 URL (항상 fallback)
  let webUrl = `https://uri.amap.com/navigation?to=${dstLng},${dstLat},${dst}&mode=car&src=EasyChina`
  if (srcLat && srcLng && srcName) {
    webUrl += `&from=${srcLng},${srcLat},${encodeURIComponent(srcName)}`
  }

  if (isIOS || isAndroid) {
    // 앱 URL Scheme
    const appUrl = isIOS
      ? `iosamap://path?sourceApplication=EasyChina&dlat=${dstLat}&dlon=${dstLng}&dname=${dst}&dev=0&t=0`
      : `amapuri://route/plan/?dlat=${dstLat}&dlon=${dstLng}&dname=${dst}&dev=0&t=0`

    // iframe으로 앱 열기 시도 (더 안정적)
    const iframe = document.createElement('iframe')
    iframe.style.display = 'none'
    iframe.src = appUrl
    document.body.appendChild(iframe)

    // 1.5초 후 앱이 안 열렸으면 웹으로
    setTimeout(() => {
      document.body.removeChild(iframe)
      window.open(webUrl, '_blank')
    }, 1500)
  } else {
    window.open(webUrl, '_blank')
  }
}
