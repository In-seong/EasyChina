package com.easychina.app.feature.webView.di;

import android.Manifest;
import android.app.Activity;
import android.content.Intent;
import android.content.pm.PackageInfo;
import android.content.pm.PackageManager;
import android.net.Uri;
import android.os.Build;
import android.os.Handler;
import android.os.Looper;
import android.provider.Settings;
import android.util.Log;
import android.webkit.JavascriptInterface;
import android.webkit.WebView;

import androidx.core.app.NotificationManagerCompat;
import androidx.core.content.ContextCompat;

import com.google.firebase.messaging.FirebaseMessaging;

import org.json.JSONObject;

public class WebAppInterface {

    private static final String TAG = "WebAppInterface";
    private final Activity activity;
    private final WebView webView;
    private final Handler mainHandler;

    public WebAppInterface(Activity activity, WebView webView) {
        this.activity = activity;
        this.webView = webView;
        this.mainHandler = new Handler(Looper.getMainLooper());
    }

    @JavascriptInterface
    public void sendFCMToken() {
        FirebaseMessaging.getInstance().getToken().addOnCompleteListener(task -> {
            if (!task.isSuccessful()) return;
            String token = task.getResult();
            try {
                JSONObject json = new JSONObject();
                json.put("fcm_token", token);
                safeEvaluateJavascript("javascript:window.__handleFCMToken__('" + json.toString().replace("'", "\\'") + "')");
            } catch (Exception e) { Log.e(TAG, "FCM token error", e); }
        });
    }

    @JavascriptInterface
    public void sendDeviceType() {
        try {
            JSONObject json = new JSONObject();
            json.put("device_type", "android");
            json.put("app_version", getAppVersion());
            json.put("device", 0);
            safeEvaluateJavascript("javascript:window.__handleType__('" + json.toString().replace("'", "\\'") + "')");
        } catch (Exception e) { Log.e(TAG, "Device type error", e); }
    }

    @JavascriptInterface
    public void getNotificationPermission() {
        boolean isEnabled;
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            isEnabled = ContextCompat.checkSelfPermission(activity, Manifest.permission.POST_NOTIFICATIONS) == PackageManager.PERMISSION_GRANTED;
        } else {
            isEnabled = NotificationManagerCompat.from(activity).areNotificationsEnabled();
        }
        try {
            JSONObject json = new JSONObject();
            json.put("permission", isEnabled);
            safeEvaluateJavascript("javascript:window.__handleNotificationPermission__('" + json.toString().replace("'", "\\'") + "')");
        } catch (Exception e) { Log.e(TAG, "Permission error", e); }
    }

    @JavascriptInterface
    public void requestNotificationPermission() {
        mainHandler.post(() -> {
            try {
                Intent intent = new Intent();
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                    intent.setAction(Settings.ACTION_APP_NOTIFICATION_SETTINGS);
                    intent.putExtra(Settings.EXTRA_APP_PACKAGE, activity.getPackageName());
                } else {
                    intent.setAction(Settings.ACTION_APPLICATION_DETAILS_SETTINGS);
                    intent.setData(Uri.parse("package:" + activity.getPackageName()));
                }
                activity.startActivity(intent);
            } catch (Exception e) { Log.e(TAG, "Settings error", e); }
        });
    }

    @JavascriptInterface
    public void closeApp() { mainHandler.post(() -> activity.finishAffinity()); }

    private void safeEvaluateJavascript(String script) {
        if (webView == null || activity == null || activity.isFinishing()) return;
        mainHandler.post(() -> {
            try { webView.evaluateJavascript(script.replace("javascript:", ""), null); }
            catch (Exception e) { Log.e(TAG, "JS error", e); }
        });
    }

    private String getAppVersion() {
        try {
            PackageInfo pInfo = activity.getPackageManager().getPackageInfo(activity.getPackageName(), 0);
            return pInfo.versionName;
        } catch (Exception e) { return "1.0.0"; }
    }
}
