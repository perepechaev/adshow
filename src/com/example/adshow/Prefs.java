package com.example.adshow;


import android.os.Bundle;
import android.preference.PreferenceFragment;


public class Prefs extends PreferenceFragment{

    @Override
    public void onCreate(Bundle inst) {
        super.onCreate(inst);
        addPreferencesFromResource(R.xml.settings);
    }

}
