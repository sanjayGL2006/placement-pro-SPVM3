// firebase-init.js — Initializes Firebase App, Auth & Analytics for pes-iams-placement
import { initializeApp } from "https://www.gstatic.com/firebasejs/12.18.0/firebase-app.js";
import { getAnalytics } from "https://www.gstatic.com/firebasejs/12.18.0/firebase-analytics.js";
import { getAuth, GoogleAuthProvider, signInWithPopup } from "https://www.gstatic.com/firebasejs/12.18.0/firebase-auth.js";

// Firebase JS SDK configuration for pes-iams-placement
const firebaseConfig = {
  apiKey: "AIzaSyCGPMY28gJtqS0hMsfyYa83Q0zFiG4W1gk",
  authDomain: "pes-iams-placement.firebaseapp.com",
  projectId: "pes-iams-placement",
  storageBucket: "pes-iams-placement.firebasestorage.app",
  messagingSenderId: "209828607611",
  appId: "1:209828607611:web:09ed1ae1d5c88a422414de",
  measurementId: "G-E5PWC7YQL9"
};

// Initialize Firebase App
const app = initializeApp(firebaseConfig);
let analytics = null;
try {
  analytics = getAnalytics(app);
} catch (e) {
  console.info("Firebase Analytics omitted in non-browser/offline environment.");
}
const auth = getAuth(app);
const googleProvider = new GoogleAuthProvider();

// Expose on window scope for Placement Pro client modules
window.firebaseApp = app;
window.firebaseAnalytics = analytics;
window.firebaseAuth = auth;
window.googleProvider = googleProvider;

window.signInWithGoogle = async function() {
  try {
    const result = await signInWithPopup(auth, googleProvider);
    const user = result.user;
    localStorage.setItem('token', await user.getIdToken());
    localStorage.setItem('user', JSON.stringify({
      name: user.displayName || 'Google Admin User',
      email: user.email,
      role: 'admin',
      photo: user.photoURL
    }));
    return { success: true, user };
  } catch (error) {
    console.error("Google Auth error:", error);
    throw error;
  }
};

console.log("Firebase initialized (pes-iams-placement) with Google Auth capability.");
