import UIKit

class AppDelegate: NSObject, UIApplicationDelegate {
    func application(
        _ application: UIApplication,
        didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]? = nil
    ) -> Bool {
        // AMap SDK 초기화
        // AMapServices.shared().apiKey = "YOUR_AMAP_KEY"
        return true
    }
}
