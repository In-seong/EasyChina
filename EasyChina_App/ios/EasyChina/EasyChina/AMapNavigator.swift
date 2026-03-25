import Foundation
import UIKit

class AMapNavigator {
    static let shared = AMapNavigator()

    func navigate(to lat: Double, lng: Double, name: String) {
        // AMap 네비게이션 앱으로 연동
        // 고덕지도 앱이 설치되어 있으면 앱으로, 아니면 웹으로
        let encodedName = name.addingPercentEncoding(withAllowedCharacters: .urlQueryAllowed) ?? name

        // 고덕지도 앱 URL Scheme
        if let amapURL = URL(string: "iosamap://path?sourceApplication=EasyChina&dlat=\(lat)&dlon=\(lng)&dname=\(encodedName)&dev=0&t=0"),
           UIApplication.shared.canOpenURL(amapURL) {
            UIApplication.shared.open(amapURL)
            return
        }

        // 고덕지도 웹 버전
        if let webURL = URL(string: "https://uri.amap.com/navigation?to=\(lng),\(lat),\(encodedName)&mode=car&src=EasyChina") {
            UIApplication.shared.open(webURL)
        }
    }

    func openLocation(lat: Double, lng: Double, name: String) {
        let encodedName = name.addingPercentEncoding(withAllowedCharacters: .urlQueryAllowed) ?? name

        if let amapURL = URL(string: "iosamap://viewMap?sourceApplication=EasyChina&poiname=\(encodedName)&lat=\(lat)&lon=\(lng)&dev=0"),
           UIApplication.shared.canOpenURL(amapURL) {
            UIApplication.shared.open(amapURL)
            return
        }

        if let webURL = URL(string: "https://uri.amap.com/marker?position=\(lng),\(lat)&name=\(encodedName)&src=EasyChina") {
            UIApplication.shared.open(webURL)
        }
    }
}
