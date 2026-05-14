<div class="auth-container">
    <!-- Left Side: Marketing -->
    <div class="auth-left">
        <div class="auth-brand">
            <a href="index.php"><img src="assets/images/logo_texte_blanc.png" alt="RHXtimes"></a>
        </div>
        <div class="auth-marketing">
            <h1>Bienvenue sur<br>RHXtimes</h1>
            <p>La solution de pointage GPS zéro hardware. Certifiez la présence de vos équipes en temps réel, sans équipement, sans tracas.</p>
        </div>
        <div class="trust-badges">
            <div class="trust-badge">
                <i class="fa-solid fa-shield-halved"></i> 100% Sécurisé
            </div>
            <div class="trust-badge">
                <i class="fa-solid fa-satellite-dish"></i> GPS Haute Précision
            </div>
        </div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="auth-right">
        <div class="auth-header">
            <h2>Bon retour !</h2>
            <p>Connectez-vous pour continuer</p>
        </div>

        <form method="POST" action="index.php?page=auth_login">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
            
            <div class="form-group">
                <label>Email ou Téléphone</label>
                <input type="text" name="identifiant" class="form-control" placeholder="nom@entreprise.com ou 6XXXXXXXX" required>
            </div>
            
            <div class="form-group">
                <label>Mot de passe</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                    <i class="fa-solid fa-eye password-toggle" id="toggleIcon" onclick="togglePassword()"></i>
                </div>
            </div>
            
            <div class="form-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" id="remember">
                    Se souvenir de moi
                </label>
                <a href="#" class="auth-link" onclick="Swal.fire('Info', 'Veuillez contacter le Super-Admin ou l\'Owner de votre entreprise pour réinitialiser votre mot de passe.', 'info')">Mot de passe oublié ?</a>
            </div>
            
            <button type="submit" class="btn-auth">
                Connexion <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>
        
        <div class="auth-footer">
            Vous n'avez pas de compte ? <a href="index.php?page=register">Créer un compte</a>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passInput.type === 'password') {
        passInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>