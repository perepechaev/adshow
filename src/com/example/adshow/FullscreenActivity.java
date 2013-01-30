package com.example.adshow;

import java.io.BufferedInputStream;
import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLConnection;
import java.net.URLEncoder;
import java.util.ArrayList;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import android.app.Activity;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.SharedPreferences.OnSharedPreferenceChangeListener;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Handler;
import android.os.Message;
import android.preference.PreferenceManager;
import android.provider.Settings;
import android.provider.Settings.Secure;
import android.util.Log;
import android.widget.ImageView;

import com.example.adshow.util.SystemUiHider;

/**
 * An example full-screen activity that shows and hides the system UI (i.e.
 * status bar and navigation/system bar) with user interaction.
 *
 * @see SystemUiHider
 */
public class FullscreenActivity extends Activity implements OnSharedPreferenceChangeListener {

    private static final String TAG = "ADSHOW";
    private Loader loader;
    protected SharedPreferences prefs;

    public Handler pingHandler = new Handler() {
        public void handleMessage(Message msg) {
            loader = new Loader();
            loader.execute();
        }
    };

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        Log.d(TAG, "ON CREATE");
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_fullscreen);
        imgView = (ImageView) findViewById(R.id.imageView1);

        prefs = PreferenceManager.getDefaultSharedPreferences(getApplicationContext());
        prefs.registerOnSharedPreferenceChangeListener(this);

        imgView.setSystemUiVisibility(0x2 | 0x4);

        launchIfPossible();
    }

    @Override
    public void onSharedPreferenceChanged(SharedPreferences sharedPreferences, String key) {
        // TODO Auto-generated method stub
        launchIfPossible();
    }

    private void launchIfPossible() {
        if (prefs.contains("name") && prefs.contains("number") && prefs.contains("host")) {

            try {
                Runtime.getRuntime().exec(new String[]{"su","-c","service call activity 79 s16 com.android.systemui"});
            } catch (IOException e) {
                Log.d(TAG, e.getMessage());
            }

            loader = new Loader();
            loader.execute();
        } else {
            Intent i = new Intent(this, SettingActivity.class);
            startActivity(i);
        }
    }

    public ImageView imgView;

    class Loader extends AsyncTask<String, Void, ArrayList<String>> {

        private static final String TAG = "background";

        ArrayList<String> listItems = new ArrayList<String>();
        ArrayList<Bitmap> images = new ArrayList<Bitmap>();
        ArrayList<Integer> delays = new ArrayList<Integer>();

        protected ArrayList<String> doInBackground(String... urls) {
            try {
                String host = URLEncoder.encode(prefs.getString("host", "adshow.local"), "UTF-8");
                URL url = new URL("http://" + host + "/adshow.php?aid="
                        + Secure.getString(getContentResolver(), Settings.Secure.ANDROID_ID)
                        + "&shop=" + URLEncoder.encode(prefs.getString("name", "default_shop"), "UTF-8")
                        + "&point=" + URLEncoder.encode(prefs.getString("number", "default_point"), "UTF-8"));

                URLConnection tc = url.openConnection();
                InputStream stream = tc.getInputStream();
                InputStreamReader input = new InputStreamReader(stream);
                BufferedReader in = new BufferedReader(input);

                String line;
                String str = "";
                while ((line = in.readLine()) != null) {
                    str = str + line;
                }

                JSONArray ja = new JSONArray(str);

                for (int i = 0; i < ja.length(); i++) {
                    JSONObject jo = (JSONObject) ja.get(i);
                    listItems.add(jo.getString("src"));

                    // Load image
                    Bitmap bm = null;
                    URL aURL = new URL(jo.getString("src"));

                    URLConnection conn = aURL.openConnection();
                    conn.connect();
                    InputStream is = conn.getInputStream();
                    BufferedInputStream bis = new BufferedInputStream(is);
                    bm = BitmapFactory.decodeStream(bis);
                    bis.close();
                    is.close();
                    images.add(bm);

                    delays.add(jo.getInt("time"));
                }
                Log.d(TAG, "END LOAD IMAGES");
            } catch (MalformedURLException e) {
                // TODO Auto-generated catch block
                e.printStackTrace();
            } catch (IOException e) {
                // TODO Auto-generated catch block
                e.printStackTrace();
            } catch (JSONException e) {
                // TODO Auto-generated catch block
                e.printStackTrace();
            }
            return listItems;
        }

        Handler changePicHandler = new Handler();
        Runnable changePicRunnable = new Runnable() {

            Integer current = 0;

            @Override
            public void run() {
                current++;

                if (current >= images.size()) {
                    current = 0;
                }

                imgView.setImageBitmap(images.get(current));

                Log.d(TAG, "SET IMAGE " + current);
                changePicHandler.removeCallbacks(changePicRunnable);
                changePicHandler.postDelayed(changePicRunnable, delays.get(current));
            }
        };

        Thread ping;

        private String ping() {
            try {

                String aid = Secure.getString(getContentResolver(), Settings.Secure.ANDROID_ID);
                String host = URLEncoder.encode(prefs.getString("host", "android.local"), "UTF-8");
                String sUrl = "http://" + host + "/ping.php?aid=" + aid;
                URL url = new URL(sUrl);

                URLConnection tc = url.openConnection();
                InputStream stream = tc.getInputStream();
                InputStreamReader input = new InputStreamReader(stream);
                BufferedReader in = new BufferedReader(input);

                String line;
                String str = "";
                while ((line = in.readLine()) != null) {
                    str = str + line;
                }

                JSONObject jo = new JSONObject(str);

                if (jo.has("action") == true) {
                    return jo.getString("action");
                }
                return "ok";
            } catch (MalformedURLException e) {
                e.printStackTrace();
            } catch (IOException e) {
                e.printStackTrace();
            } catch (JSONException e) {
                e.printStackTrace();
            }
            return "error";
        }

        @Override
        protected void onPostExecute(ArrayList<String> listItems) {
            super.onPostExecute(listItems);
            Log.d(TAG, "SET IMAGE 0 FIRST");

            changePicHandler.removeCallbacks(changePicRunnable);
            imgView.setImageBitmap(images.get(0));
            changePicHandler.postDelayed(changePicRunnable, delays.get(0));

            ping = new Thread() {
                public void run() {
                    while (true) {
                        try {
                            sleep(1);
                            String result = ping();

                            if (result.equals("reload")) {
                                changePicHandler.removeCallbacks(changePicRunnable);
                                pingHandler.sendEmptyMessage(0);
                                break;
                            }

                        } catch (InterruptedException e) {
                            // TODO Auto-generated catch block
                            e.printStackTrace();
                        }
                    }
                }
            };
            ping.start();
        }

        public void reload() {
            Log.d(TAG, "RELOAD");
        }
    }

}
