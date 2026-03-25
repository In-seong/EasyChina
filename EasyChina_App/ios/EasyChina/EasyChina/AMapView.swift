import SwiftUI
// import MAMapKit  // AMap SDK - 설치 후 주석 해제

struct AMapView: View {
    let onClose: () -> Void

    var body: some View {
        VStack(spacing: 0) {
            // Header
            HStack {
                Text("지도")
                    .font(.headline)
                Spacer()
                Button(action: onClose) {
                    Image(systemName: "xmark.circle.fill")
                        .font(.title2)
                        .foregroundColor(.gray)
                }
            }
            .padding()
            .background(Color.white)

            // Map placeholder - AMap SDK 연동 후 교체
            ZStack {
                Color.gray.opacity(0.1)
                VStack(spacing: 12) {
                    Image(systemName: "map")
                        .font(.system(size: 60))
                        .foregroundColor(.gray)
                    Text("AMap SDK 연동 후 지도가 표시됩니다")
                        .font(.caption)
                        .foregroundColor(.gray)
                    Text("고덕지도 API Key 필요")
                        .font(.caption2)
                        .foregroundColor(.gray.opacity(0.6))
                }
            }
        }
        .background(Color.white)
    }
}

// MARK: - AMap SDK 연동 후 사용할 실제 맵 뷰
/*
struct NativeAMapView: UIViewRepresentable {
    @Binding var centerCoordinate: CLLocationCoordinate2D
    var markers: [PlaceMarker] = []

    func makeUIView(context: Context) -> MAMapView {
        let mapView = MAMapView(frame: .zero)
        mapView.delegate = context.coordinator
        mapView.showsUserLocation = true
        mapView.userTrackingMode = .follow
        mapView.setZoomLevel(15, animated: false)
        return mapView
    }

    func updateUIView(_ mapView: MAMapView, context: Context) {
        mapView.setCenter(centerCoordinate, animated: true)

        // 마커 업데이트
        mapView.removeAnnotations(mapView.annotations)
        for marker in markers {
            let annotation = MAPointAnnotation()
            annotation.coordinate = CLLocationCoordinate2D(
                latitude: marker.latitude,
                longitude: marker.longitude
            )
            annotation.title = marker.nameKo
            annotation.subtitle = marker.nameCn
            mapView.addAnnotation(annotation)
        }
    }

    func makeCoordinator() -> Coordinator {
        Coordinator()
    }

    class Coordinator: NSObject, MAMapViewDelegate {
        func mapView(_ mapView: MAMapView!, didSelect view: MAAnnotationView!) {
            // 마커 탭 → WebView로 장소 상세 이동
        }
    }
}

struct PlaceMarker {
    let id: Int
    let nameKo: String
    let nameCn: String
    let latitude: Double
    let longitude: Double
    let categoryColor: String
}
*/
