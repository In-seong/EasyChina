import SwiftUI
import WebKit

struct BridgeMessage {
    let action: String
    let data: [String: Any]?
}

struct WebView: UIViewRepresentable {
    let url: URL
    let onBridgeMessage: (BridgeMessage) -> Void

    func makeCoordinator() -> Coordinator {
        Coordinator(onBridgeMessage: onBridgeMessage)
    }

    func makeUIView(context: Context) -> WKWebView {
        let config = WKWebViewConfiguration()
        let userContentController = WKUserContentController()
        userContentController.add(context.coordinator, name: "nativeBridge")
        config.userContentController = userContentController

        // 인라인 미디어 재생 허용
        config.allowsInlineMediaPlayback = true
        config.mediaTypesRequiringUserActionForPlayback = []

        let webView = WKWebView(frame: .zero, configuration: config)
        webView.navigationDelegate = context.coordinator
        webView.scrollView.bounces = false
        webView.scrollView.contentInsetAdjustmentBehavior = .never

        // Safe area 설정
        if #available(iOS 16.4, *) {
            webView.isInspectable = true
        }

        webView.load(URLRequest(url: url))
        return webView
    }

    func updateUIView(_ uiView: WKWebView, context: Context) {}

    class Coordinator: NSObject, WKScriptMessageHandler, WKNavigationDelegate {
        let onBridgeMessage: (BridgeMessage) -> Void

        init(onBridgeMessage: @escaping (BridgeMessage) -> Void) {
            self.onBridgeMessage = onBridgeMessage
        }

        func userContentController(
            _ userContentController: WKUserContentController,
            didReceive message: WKScriptMessage
        ) {
            guard let jsonString = message.body as? String,
                  let jsonData = jsonString.data(using: .utf8),
                  let json = try? JSONSerialization.jsonObject(with: jsonData) as? [String: Any],
                  let action = json["action"] as? String else {
                return
            }

            let data = json["data"] as? [String: Any]
            let bridgeMessage = BridgeMessage(action: action, data: data)
            DispatchQueue.main.async {
                self.onBridgeMessage(bridgeMessage)
            }
        }

        func webView(_ webView: WKWebView, decidePolicyFor navigationAction: WKNavigationAction) async -> WKNavigationActionPolicy {
            // 외부 링크는 Safari에서 열기
            if let url = navigationAction.request.url,
               url.host != AppConfig.userWebURL.host,
               UIApplication.shared.canOpenURL(url) {
                await UIApplication.shared.open(url)
                return .cancel
            }
            return .allow
        }
    }
}
