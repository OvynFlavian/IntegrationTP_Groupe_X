package com.example.arnaud.integrationprojetv0;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.view.View;
import android.widget.Button;
import android.widget.RatingBar;
import android.widget.RelativeLayout;
import android.widget.TextView;
import android.widget.Toast;

import org.apache.http.NameValuePair;
import org.apache.http.client.HttpClient;
import org.apache.http.client.ResponseHandler;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.BasicResponseHandler;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;
import org.json.JSONObject;

import java.util.ArrayList;

/**
 * Created by Thomas on 19/10/2015.
 */
public class AfficherActivite extends AppCompatActivity {

    private static final String intentCat = "categorie";
    private static String categorie = null;
    private TextView titre = null;
    private TextView description = null;
    private RatingBar note = null;
    private Button btnOk = null;
    private Button btnSuivant = null;
    private Button ajoutActivite = null;
    private Button btnOui = null;
    private SessionManager session = null;
    private TextView note2 = null;
    private String idActivite = null;
    private String idUser = null;
    private RelativeLayout confirmationActivite = null;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activite_layout);

        Intent intent = getIntent();
        titre = (TextView) findViewById(R.id.titre);
        description = (TextView) findViewById(R.id.description);
        note = (RatingBar) findViewById(R.id.note);
        note2 = (TextView) findViewById(R.id.note2);
        btnOk = (Button) findViewById(R.id.ok);
        btnSuivant = (Button) findViewById(R.id.suivant);
        ajoutActivite = (Button) findViewById(R.id.ajouterActivite);
        btnOui = (Button) findViewById(R.id.ouiChangeActivite);
        confirmationActivite = (RelativeLayout) findViewById(R.id.confirmationActivite);
        categorie = intent.getStringExtra(intentCat);

        session = new SessionManager(getApplicationContext());
        idUser = session.getId();
        confirmationActivite.setVisibility(View.INVISIBLE);

        if(!session.isLoggedIn()) {
            ajoutActivite.setVisibility(View.INVISIBLE);
            btnOk.setText("Connectez-vous");
            btnOk.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    startActivity(new Intent(AfficherActivite.this, MainActivity.class));
                }
            });
        } else {
            btnOk.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    enregistrerActivite();
                }
            });
        }

        activiteSuivante(btnSuivant);

        btnSuivant.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                activiteSuivante(v);
            }
        });

    }

    public void ajouterActivite(View view) {
        Intent intent = new Intent(AfficherActivite.this, AjouterActivite.class);
        intent.putExtra(intentCat, categorie);
        startActivity(intent);
    }

    public void activiteSuivante(View view) {
        try{

            HttpClient httpclient = new DefaultHttpClient();
            HttpPost httppost = new HttpPost("http://91.121.151.137/scripts_android/activite.php"); // make sure the url is correct.
            //add your data
            ArrayList<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(1);
            // Always use the same variable name for posting i.e the android side variable name and php side variable name should be similar,
            System.out.println("avant name pair value");
            nameValuePairs.add(new BasicNameValuePair("categorie", categorie.trim()));  // $Edittext_value = $_POST['Edittext_value'];
            System.out.println("après name pair value");
            httppost.setEntity(new UrlEncodedFormEntity(nameValuePairs));
            System.out.println("après setEntity");

            //Execute HTTP Post Request
            // response=httpclient.execute(httppost);
            ResponseHandler<String> responseHandler = new BasicResponseHandler();
            System.out.println("avant execute");
            final String response = httpclient.execute(httppost, responseHandler);
            System.out.println("après execute");
            System.out.println("Response lol : " + response);
            JSONObject jObj = new JSONObject(response);

            final String id = jObj.getString("id");
            idActivite = id;
            final String libelle = jObj.getString("titre");
            final String description = jObj.getString("description");
            Float note = Float.valueOf(jObj.getString("note"));
            if (note != 99) {
                this.note.setVisibility(View.VISIBLE);
                this.note2.setVisibility(View.INVISIBLE);
            } else {
                this.note.setVisibility(View.INVISIBLE);
                this.note2.setVisibility(View.VISIBLE);
            }

            System.out.println("Response : " + id + libelle + description + note);

            titre.setText(libelle);
            this.description.setText(description);
            this.note.setRating(note);

            confirmationActivite.setVisibility(View.INVISIBLE);

        } catch(Exception e) {
            System.out.println("Exception : " + e.getMessage());
        }
    }

    public void enregistrerActivite() {
        try{

            HttpClient httpclient = new DefaultHttpClient();
            HttpPost httppost = new HttpPost("http://91.121.151.137/scripts_android/enregistrerActivite.php"); // make sure the url is correct.
            //add your data
            ArrayList<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(2);
            // Always use the same variable name for posting i.e the android side variable name and php side variable name should be similar,
            nameValuePairs.add(new BasicNameValuePair("idUser", idUser.trim()));  // $Edittext_value = $_POST['Edittext_value'];
            nameValuePairs.add(new BasicNameValuePair("idActivite", idActivite.trim()));
            httppost.setEntity(new UrlEncodedFormEntity(nameValuePairs));

            //Execute HTTP Post Request
            // response=httpclient.execute(httppost);
            ResponseHandler<String> responseHandler = new BasicResponseHandler();
            final String response = httpclient.execute(httppost, responseHandler);
            System.out.println("response : " + response);
            JSONObject jObj = new JSONObject(response);

            System.out.println("response : " + response);

            final String id = jObj.getString("idUser");

            if(id == null) {
                Context context = getApplicationContext();
                CharSequence s = "Activité enregistrée !";
                int duration = Toast.LENGTH_SHORT;
                Toast toast = Toast.makeText(context, s, duration);
                toast.show();
            } else {
                confirmationActivite.setVisibility(View.VISIBLE);
                btnOui.setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View v) {
                        try {
                            HttpClient httpclient = new DefaultHttpClient();
                            HttpPost httppost = new HttpPost("http://91.121.151.137/scripts_android/updateUserActivite.php");
                            ArrayList<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(2);
                            nameValuePairs.add(new BasicNameValuePair("idUser", idUser.trim()));  // $Edittext_value = $_POST['Edittext_value'];
                            nameValuePairs.add(new BasicNameValuePair("idActivite", idActivite.trim()));
                            httppost.setEntity(new UrlEncodedFormEntity(nameValuePairs));

                            ResponseHandler<String> responseHandler = new BasicResponseHandler();
                            httpclient.execute(httppost, responseHandler);

                            Context context = getApplicationContext();
                            CharSequence s = "Activité enregistrée !";
                            int duration = Toast.LENGTH_SHORT;
                            Toast toast = Toast.makeText(context, s, duration);
                            toast.show();

                            confirmationActivite.setVisibility(View.INVISIBLE);

                        } catch(Exception e) {
                            System.out.println("Exception : " + e.getMessage());
                        }
                    }
                });
            }

        }catch(Exception e){
            System.out.println("Exception : " + e.getMessage());
        }
    }

}
