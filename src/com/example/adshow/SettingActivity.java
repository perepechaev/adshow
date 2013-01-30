package com.example.adshow;

import android.os.Build;
import android.os.Bundle;
import android.preference.PreferenceActivity;

public class SettingActivity extends PreferenceActivity{
    @Override
    public void onCreate(Bundle inst) {
        super.onCreate(inst);
        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.HONEYCOMB) {
            getFragmentManager().beginTransaction().replace(android.R.id.content, new Prefs()).commit();
        }
        else {
            addPreferencesFromResource(R.xml.settings);
        }
    }
}
