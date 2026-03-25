package com.easychina.app;

import android.app.Activity;
import android.content.Intent;
import android.net.Uri;
import android.speech.tts.TextToSpeech;
import android.webkit.JavascriptInterface;
import android.webkit.WebView;

import org.json.JSONObject;

import java.util.Locale;

public class WebBridgeInterface {

    private final Activity activity;
    private final WebView webView;
    private TextToSpeech tts;

    public WebBridgeInterface(Activity activity, WebView webView) {
        this.activity = activity;
        this.webView = webView;

        // TTS 초기화
        tts = new TextToSpeech(activity, status -> {
            if (status == TextToSpeech.SUCCESS) {
                tts.setLanguage(Locale.CHINESE);
            }
        });
    }

    @JavascriptInterface
    public void postMessage(String jsonStr) {
        try {
            JSONObject json = new JSONObject(jsonStr);
            String action = json.getString("action");
            JSONObject data = json.optJSONObject("data");

            switch (action) {
                case "openMap":
                    openMap();
                    break;

                case "navigateTo":
                    if (data != null) {
                        navigateTo(
                            data.getDouble("lat"),
                            data.getDouble("lng"),
                            data.getString("name")
                        );
                    }
                    break;

                case "showPlaceOnMap":
                    if (data != null) {
                        showPlaceOnMap(
                            data.getDouble("lat"),
                            data.getDouble("lng"),
                            data.getString("name")
                        );
                    }
                    break;

                case "speakChinese":
                    if (data != null) {
                        speakChinese(data.getString("text"));
                    }
                    break;
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    private void openMap() {
        // AMap Activity로 전환
        // Intent intent = new Intent(activity, AMapActivity.class);
        // activity.startActivity(intent);
    }

    private void navigateTo(double lat, double lng, String name) {
        // 고덕지도 앱으로 네비게이션
        try {
            String uri = "amapuri://route/plan/?dlat=" + lat + "&dlon=" + lng
                + "&dname=" + Uri.encode(name) + "&dev=0&t=0";
            Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(uri));
            intent.setPackage("com.autonavi.minimap");
            activity.startActivity(intent);
        } catch (Exception e) {
            // 고덕지도 앱이 없으면 웹으로
            String webUri = "https://uri.amap.com/navigation?to=" + lng + "," + lat
                + "," + Uri.encode(name) + "&mode=car&src=EasyChina";
            Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(webUri));
            activity.startActivity(intent);
        }
    }

    private void showPlaceOnMap(double lat, double lng, String name) {
        try {
            String uri = "amapuri://poi/detail?lat=" + lat + "&lon=" + lng
                + "&name=" + Uri.encode(name) + "&src=EasyChina";
            Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(uri));
            intent.setPackage("com.autonavi.minimap");
            activity.startActivity(intent);
        } catch (Exception e) {
            String webUri = "https://uri.amap.com/marker?position=" + lng + "," + lat
                + "&name=" + Uri.encode(name) + "&src=EasyChina";
            Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(webUri));
            activity.startActivity(intent);
        }
    }

    private void speakChinese(String text) {
        if (tts != null) {
            tts.speak(text, TextToSpeech.QUEUE_FLUSH, null, "easychina_tts");
        }
    }

    public void destroy() {
        if (tts != null) {
            tts.stop();
            tts.shutdown();
        }
    }
}
