import SwiftUI

struct ContentView: View {
    @State private var showMap = false

    var body: some View {
        ZStack {
            // WebView (메인)
            WebView(
                url: AppConfig.userWebURL,
                onBridgeMessage: handleBridgeMessage
            )
            .edgesIgnoringSafeArea(.all)

            // Native Map Overlay
            if showMap {
                AMapView(
                    onClose: { showMap = false }
                )
                .transition(.move(edge: .bottom))
            }
        }
    }

    private func handleBridgeMessage(_ message: BridgeMessage) {
        switch message.action {
        case "openMap":
            withAnimation { showMap = true }

        case "navigateTo":
            if let data = message.data,
               let lat = data["lat"] as? Double,
               let lng = data["lng"] as? Double,
               let name = data["name"] as? String {
                AMapNavigator.shared.navigate(to: lat, lng: lng, name: name)
            }

        case "showPlaceOnMap":
            if let data = message.data,
               let lat = data["lat"] as? Double,
               let lng = data["lng"] as? Double {
                withAnimation { showMap = true }
                // AMapView에 마커 표시
            }

        case "speakChinese":
            if let data = message.data,
               let text = data["text"] as? String {
                TTSManager.shared.speak(text: text, language: "zh-CN")
            }

        default:
            break
        }
    }
}
