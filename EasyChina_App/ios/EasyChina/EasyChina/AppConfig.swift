import Foundation

struct AppConfig {
    #if DEBUG
    static let userWebURL = URL(string: "http://localhost:5173")!
    static let apiBaseURL = "http://localhost:8000"
    #else
    static let userWebURL = URL(string: "https://easychina.app")!
    static let apiBaseURL = "https://api.easychina.app"
    #endif

    static let amapKey = "YOUR_AMAP_IOS_KEY"
}
