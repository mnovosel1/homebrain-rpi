if (typeof firebase === 'undefined') throw new Error('hosting/init-error: Firebase SDK not detected. You must include it before /__/firebase/init.js');
firebase.initializeApp({
  "apiKey": "AIzaSyCCCMvNvIzMRo286N8m1TCIiRyytFMKKhY",
  "databaseURL": "https://housebrain-fa299.firebaseio.com",
  "storageBucket": "housebrain-fa299.appspot.com",
  "authDomain": "housebrain-fa299.firebaseapp.com",
  "messagingSenderId": "659938142178",
  "projectId": "housebrain-fa299"
});
