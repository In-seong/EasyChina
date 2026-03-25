import SwiftUI
import WebKit
import Network
import UserNotifications

struct MainView: View {
    @State var webView: WKWebView?
    @State var webViewError: Bool = false
    @State var shouldReload: Bool = false
    @State var isWebViewLoaded: Bool = false
    @State var networkState: NWPath.Status = .unsatisfied
    @State var showNetworkAlert: Bool = false

    #if USER
    let WebURL = "https://easyuser.revuplan.com"
    #elseif ADMIN
    let WebURL = "https://easyadmin.revuplan.com"
    #else
    let WebURL = "https://easyuser.revuplan.com"
    #endif

    var body: some View {
        ZStack {
            WebViewContainer(
                url: URL(string: WebURL),
                webView: $webView,
                isError: $webViewError,
                shouldReload: $shouldReload,
                onMessage: { action, body in
                    handleBridgeMessage(action: action, body: body)
                },
                onLoadComplete: {
                    isWebViewLoaded = true
                    executeJavaScriptSequentially()
                }
            )
            .onChange(of: networkState) { _ in
                showNetworkAlert = networkState != .satisfied
            }
            .onReceive(NotificationCenter.default.publisher(for: Notification.Name("FCMTokenReceived"))) { notification in
                if let token = notification.userInfo?["token"] as? String, isWebViewLoaded {
                    sendFCMTokenToWebView(token: token)
                }
            }

            if showNetworkAlert {
                networkAlertOverlay
            }
        }
        .onAppear {
            startNetworkMonitoring()
        }
    }
}

// MARK: - Network Alert UI
extension MainView {
    var networkAlertOverlay: some View {
        ZStack {
            Color.black.opacity(0.5).ignoresSafeArea()
            VStack(spacing: 16) {
                Image(systemName: "wifi.slash")
                    .resizable().scaledToFit().frame(width: 50).foregroundColor(.gray)
                Text("네트워크에 접속할 수 없습니다.\n네트워크 연결 상태를 확인해주세요.")
                    .font(.system(size: 14)).multilineTextAlignment(.center).lineSpacing(6).foregroundColor(.black)
            }
            .padding(24).background(Color.white).cornerRadius(12).padding(.horizontal, 40)
        }
    }
}

// MARK: - JS Bridge Message Handler
extension MainView {
    private func handleBridgeMessage(action: String, body: [String: Any]) {
        switch action {
        case "closeApp":
            UIApplication.shared.perform(#selector(NSXPCConnection.suspend))
            DispatchQueue.main.asyncAfter(deadline: .now() + 0.5) { exit(0) }
        case "sendFCMToken":
            if let savedToken = UserDefaults.standard.string(forKey: "fcmToken") {
                sendFCMTokenToWebView(token: savedToken)
            }
        case "sendDeviceType":
            sendTypeToWebView()
        case "getNotificationPermission":
            let callbackName = body["callback"] as? String ?? "__handleNotificationPermission__"
            sendNotificationPermission(callbackName: callbackName)
        case "requestNotificationPermission":
            if let url = URL(string: UIApplication.openSettingsURLString) {
                UIApplication.shared.open(url)
            }
        case "openExternalUrl":
            if let urlString = body["url"] as? String, let url = URL(string: urlString) {
                UIApplication.shared.open(url)
            }
        default:
            print("[EasyChina] Unknown action: \(action)")
        }
    }
}

// MARK: - JavaScript Execution
extension MainView {
    private func executeJavaScriptSequentially() {
        sendTypeToWebView {
            if let savedToken = UserDefaults.standard.string(forKey: "fcmToken") {
                self.sendFCMTokenToWebView(token: savedToken)
            }
        }
    }

    private func sendTypeToWebView(completion: (() -> Void)? = nil) {
        guard let webView = webView else { completion?(); return }
        let appVersion = Bundle.main.infoDictionary?["CFBundleShortVersionString"] as? String ?? "unknown"
        let osVersion = UIDevice.current.systemVersion
        let deviceType = "\(UIDevice.current.model) (iOS \(osVersion))"
        let js = """
        if (window.__handleType__) {
            window.__handleType__({ device_type: '\(deviceType)', app_version: '\(appVersion)', device: 1 });
        }
        """
        webView.evaluateJavaScript(js) { _, _ in completion?() }
    }

    private func sendFCMTokenToWebView(token: String, completion: (() -> Void)? = nil) {
        guard let webView = webView else { completion?(); return }
        let js = "if (window.__handleFCMToken__) { window.__handleFCMToken__('\(token)'); }"
        webView.evaluateJavaScript(js) { _, _ in completion?() }
    }

    private func sendNotificationPermission(callbackName: String) {
        UNUserNotificationCenter.current().getNotificationSettings { settings in
            let permission = settings.authorizationStatus == .denied ? "denied" : "granted"
            let js = """
            if (window.\(callbackName)) {
                window.\(callbackName)({ success: true, data: JSON.stringify({ status: '\(permission)', enabled: true }) });
            }
            """
            DispatchQueue.main.async {
                self.webView?.evaluateJavaScript(js, completionHandler: nil)
            }
        }
    }

    private func startNetworkMonitoring() {
        let monitor = NWPathMonitor()
        monitor.pathUpdateHandler = { path in
            DispatchQueue.main.async {
                let prev = networkState
                networkState = path.status
                if path.status == .satisfied && prev == .unsatisfied { shouldReload = true }
            }
        }
        monitor.start(queue: DispatchQueue.global())
    }
}
