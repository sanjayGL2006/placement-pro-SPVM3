// firebase-init.js — Initializes Firebase App, Auth & Analytics for spvm3-placement
import { initializeApp } from "https://www.gstatic.com/firebasejs/12.18.0/firebase-app.js";
import { getAnalytics, isSupported } from "https://www.gstatic.com/firebasejs/12.18.0/firebase-analytics.js";
import { getAuth, GoogleAuthProvider, signInWithPopup } from "https://www.gstatic.com/firebasejs/12.18.0/firebase-auth.js";

// Firebase JS SDK configuration for spvm3-placement
const firebaseConfig = {
  apiKey: "AIzaSyDILX8Reuc86UWkKzjbYXyKG5eFFnhCU44",
  authDomain: "spvm3-placement.firebaseapp.com",
  projectId: "spvm3-placement",
  storageBucket: "spvm3-placement.firebasestorage.app",
  messagingSenderId: "600410687686",
  appId: "1:600410687686:web:a4309693e929e756b16fc7",
  measurementId: "G-3C5WQEEGZ4"
};

// Initialize Firebase App
const app = initializeApp(firebaseConfig);
let analytics = null;

isSupported().then(supported => {
  if (supported) {
    try {
      analytics = getAnalytics(app);
      window.firebaseAnalytics = analytics;
    } catch (e) {
      console.info("Firebase Analytics omitted or restricted.");
    }
  }
}).catch(() => {});

const auth = getAuth(app);
const googleProvider = new GoogleAuthProvider();

// Expose on window scope for Placement Pro client modules
window.firebaseApp = app;
window.firebaseAnalytics = analytics;
window.firebaseAuth = auth;
window.googleProvider = googleProvider;

let isSigningIn = false;

window.signInWithGoogle = async function() {
  if (isSigningIn) return { cancelled: true };
  isSigningIn = true;
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
    if (error.code === 'auth/cancelled-popup-request' || error.code === 'auth/popup-closed-by-user') {
      console.info("Google Sign-In popup closed or request superseded.");
      return { cancelled: true };
    }
    console.error("Google Auth error:", error);
    throw error;
  } finally {
    isSigningIn = false;
  }
};

console.log("Firebase initialized (spvm3-placement) with Google Auth capability.");
