import SwiftUI
import WebKit

struct WebViewContainer: UIViewRepresentable {
    let url: URL?
    @Binding var webView: WKWebView?
    @Binding var isError: Bool
    @Binding var shouldReload: Bool
    var onMessage: ((String, [String: Any]) -> Void)?
    var onLoadComplete: (() -> Void)?

    func makeCoordinator() -> WebViewCoordinator { WebViewCoordinator(self) }

    func makeUIView(context: Context) -> WKWebView {
        let config = WKWebViewConfiguration()
        config.allowsInlineMediaPlayback = true
        config.mediaTypesRequiringUserActionForPlayback = []
        config.userContentController.add(context.coordinator, name: "iOSBridge")

        let wv = WKWebView(frame: .zero, configuration: config)
        wv.navigationDelegate = context.coordinator
        wv.uiDelegate = context.coordinator
        wv.scrollView.bounces = false
        wv.scrollView.bouncesZoom = false
        wv.scrollView.minimumZoomScale = 1.0
        wv.scrollView.maximumZoomScale = 1.0
        wv.scrollView.pinchGestureRecognizer?.isEnabled = false
        wv.isOpaque = true
        wv.backgroundColor = .white

        // 오프라인 시 캐시 사용
        config.websiteDataStore = .default()

        context.coordinator.webView = wv
        DispatchQueue.main.async { self.webView = wv }

        if let myURL = url {
            var request = URLRequest(url: myURL)
            request.cachePolicy = .returnCacheDataElseLoad  // 캐시 우선, 없으면 네트워크
            if let cookies = HTTPCookieStorage.shared.cookies(for: myURL) {
                request.allHTTPHeaderFields = HTTPCookie.requestHeaderFields(with: cookies)
            }
            wv.load(request)
        }
        return wv
    }

    func updateUIView(_ uiView: WKWebView, context: Context) {
        if shouldReload {
            DispatchQueue.main.async { self.shouldReload = false }
            if let myURL = url {
                var request = URLRequest(url: myURL)
                if let cookies = HTTPCookieStorage.shared.cookies(for: myURL) {
                    request.allHTTPHeaderFields = HTTPCookie.requestHeaderFields(with: cookies)
                }
                uiView.load(request)
            }
        }
    }
}

class WebViewCoordinator: NSObject {
    var parent: WebViewContainer
    var webView: WKWebView?
    init(_ parent: WebViewContainer) { self.parent = parent }
}

extension WebViewCoordinator: WKNavigationDelegate {
    func webView(_ webView: WKWebView, didFinish navigation: WKNavigation!) {
        parent.isError = false
        defaultJS(webView)
        parent.onLoadComplete?()
    }
    func webView(_ webView: WKWebView, didFail navigation: WKNavigation!, withError error: Error) {
        handleLoadError(webView: webView, error: error)
    }
    func webView(_ webView: WKWebView, didFailProvisionalNavigation navigation: WKNavigation!, withError error: Error) {
        handleLoadError(webView: webView, error: error)
    }

    private var retried = false
    private func handleLoadError(webView: WKWebView, error: Error) {
        let nsError = error as NSError
        // 네트워크 오류이고 아직 재시도 안 했으면 → 캐시 전용으로 재시도
        if !retried && (nsError.code == NSURLErrorNotConnectedToInternet ||
                        nsError.code == NSURLErrorTimedOut ||
                        nsError.code == NSURLErrorNetworkConnectionLost) {
            retried = true
            if let url = parent.url {
                var request = URLRequest(url: url)
                request.cachePolicy = .returnCacheDataDontLoad  // 캐시만 사용
                webView.load(request)
                return
            }
        }
        parent.isError = true
    }

    // 외부 URL Scheme 처리 (iosamap://, tel:, mailto: 등)
    func webView(_ webView: WKWebView, decidePolicyFor navigationAction: WKNavigationAction, decisionHandler: @escaping (WKNavigationActionPolicy) -> Void) {
        guard let url = navigationAction.request.url else {
            decisionHandler(.allow)
            return
        }

        let scheme = url.scheme ?? ""

        // 외부 앱 URL Scheme
        if ["iosamap", "baidumap", "comgooglemaps", "diditaxi", "didipublic", "tel", "mailto", "sms"].contains(scheme) {
            UIApplication.shared.open(url, options: [:], completionHandler: nil)
            decisionHandler(.cancel)
            return
        }

        // 외부 웹사이트 (Safari에서 열기)
        if navigationAction.navigationType == .linkActivated,
           let host = url.host,
           !host.contains("revuplan.com") && !host.contains("localhost") {
            UIApplication.shared.open(url, options: [:], completionHandler: nil)
            decisionHandler(.cancel)
            return
        }

        decisionHandler(.allow)
    }
}

extension WebViewCoordinator: WKScriptMessageHandler {
    func userContentController(_ userContentController: WKUserContentController, didReceive message: WKScriptMessage) {
        guard message.name == "iOSBridge",
              let body = message.body as? [String: Any],
              let action = body["action"] as? String else { return }
        parent.onMessage?(action, body)
    }
}

extension WebViewCoordinator: WKUIDelegate {
    func webView(_ webView: WKWebView, runJavaScriptAlertPanelWithMessage message: String, initiatedByFrame frame: WKFrameInfo, completionHandler: @escaping () -> Void) {
        let alert = UIAlertController(title: nil, message: message, preferredStyle: .alert)
        alert.addAction(UIAlertAction(title: "확인", style: .default) { _ in completionHandler() })
        if let scene = UIApplication.shared.connectedScenes.first as? UIWindowScene,
           let rootVC = scene.windows.first?.rootViewController { rootVC.present(alert, animated: true) }
        else { completionHandler() }
    }

    func webView(_ webView: WKWebView, runJavaScriptConfirmPanelWithMessage message: String, initiatedByFrame frame: WKFrameInfo, completionHandler: @escaping (Bool) -> Void) {
        let alert = UIAlertController(title: nil, message: message, preferredStyle: .alert)
        alert.addAction(UIAlertAction(title: "취소", style: .cancel) { _ in completionHandler(false) })
        alert.addAction(UIAlertAction(title: "확인", style: .default) { _ in completionHandler(true) })
        if let scene = UIApplication.shared.connectedScenes.first as? UIWindowScene,
           let rootVC = scene.windows.first?.rootViewController { rootVC.present(alert, animated: true) }
        else { completionHandler(false) }
    }

    func webView(_ webView: WKWebView, requestMediaCapturePermissionFor origin: WKSecurityOrigin, initiatedByFrame frame: WKFrameInfo, type: WKMediaCaptureType, decisionHandler: @escaping (WKPermissionDecision) -> Void) {
        decisionHandler(type == .microphone ? .grant : .deny)
    }
}

extension WebViewCoordinator {
    func defaultJS(_ webView: WKWebView) {
        let js = """
        var meta = document.createElement('meta');
        meta.name = 'viewport';
        meta.content = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no';
        document.getElementsByTagName('head')[0].appendChild(meta);
        document.documentElement.style.webkitUserSelect = 'none';
        document.documentElement.style.webkitTouchCallout = 'none';
        """
        webView.evaluateJavaScript(js, completionHandler: nil)
    }
}
