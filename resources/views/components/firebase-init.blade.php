{{-- Firebase Initialization Component --}}
{{-- Include this component in layouts that need Firebase Auth --}}

<!-- Firebase App (the core Firebase SDK) -->
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
<!-- Firebase Authentication -->
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-auth-compat.js"></script>

<script>
    // Initialize Firebase
    const firebaseConfig = {
        apiKey: "{{ config('services.firebase.web_api_key') }}",
        authDomain: "{{ config('services.firebase.auth_domain') }}",
        projectId: "{{ config('services.firebase.project_id') }}",
        storageBucket: "{{ config('services.firebase.storage_bucket') }}",
        messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
        appId: "{{ config('services.firebase.app_id') }}"
    };

    // Initialize Firebase App
    if (!firebase.apps.length) {
        firebase.initializeApp(firebaseConfig);
    }

    // Get Firebase Auth instance
    const auth = firebase.auth();

    // Optional: Enable persistence
    auth.setPersistence(firebase.auth.Auth.Persistence.LOCAL);
</script>
