import { initializeApp } from "firebase/app";
import { getAuth, RecaptchaVerifier, signInWithPhoneNumber } from "firebase/auth";

const firebaseConfig = {
    apiKey: "AIzaSyBfzT0dZZAcgsc0CGKugR2H3jEB_G6jG50",
    authDomain: "miolms.firebaseapp.com",
    databaseURL: "https://miolms-default-rtdb.firebaseio.com",
    projectId: "miolms",
    storageBucket: "miolms.appspot.com",
    messagingSenderId: "720846720525",
    appId: "1:720846720525:web:65747f3c00aef3fbeb4f44",
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
auth.languageCode = 'en';

export { auth, RecaptchaVerifier, signInWithPhoneNumber };