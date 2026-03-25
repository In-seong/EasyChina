package com.easychina.app;

import com.easychina.app.BuildConfig;

public class AppConfig {
    public static final String USER_WEB_URL = BuildConfig.DEBUG
        ? "http://10.0.2.2:5173"  // Android emulator -> host
        : "https://easychina.app";

    public static final String API_BASE_URL = BuildConfig.DEBUG
        ? "http://10.0.2.2:8000"
        : "https://api.easychina.app";

    public static final String AMAP_KEY = "YOUR_AMAP_ANDROID_KEY";
}
