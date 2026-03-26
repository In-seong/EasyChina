declare global {
  interface Window {
    webkit?: {
      messageHandlers: {
        nativeBridge: {
          postMessage: (message: string) => void
        }
        iOSBridge: {
          postMessage: (message: any) => void
        }
      }
    }
    AndroidBridge?: {
      postMessage: (message: string) => void
    }
    onNativeMessage?: (type: string, data: string) => void
  }
}

interface BridgeMessage {
  action: string
  data?: Record<string, any>
}

const pendingCallbacks = new Map<string, (data: any) => void>()

export function sendToNative(message: BridgeMessage): void {
  const json = JSON.stringify(message)

  if (window.webkit?.messageHandlers?.nativeBridge) {
    window.webkit.messageHandlers.nativeBridge.postMessage(json)
  } else if (window.AndroidBridge) {
    window.AndroidBridge.postMessage(json)
  } else {
    console.log('[Bridge] Not in native app:', message)
  }
}

export function openMap(): void {
  sendToNative({ action: 'openMap' })
}

export function navigateTo(lat: number, lng: number, name: string): void {
  sendToNative({ action: 'navigateTo', data: { lat, lng, name } })
}

export function showPlaceOnMap(placeId: number, lat: number, lng: number, name: string): void {
  sendToNative({ action: 'showPlaceOnMap', data: { placeId, lat, lng, name } })
}

export function speakChinese(text: string): void {
  sendToNative({ action: 'speakChinese', data: { text } })
}

export function isInApp(): boolean {
  return !!(window.webkit?.messageHandlers?.nativeBridge || window.AndroidBridge)
}

// Native -> WebView callback handler
window.onNativeMessage = (type: string, data: string) => {
  const callback = pendingCallbacks.get(type)
  if (callback) {
    callback(JSON.parse(data))
    pendingCallbacks.delete(type)
  }
}
