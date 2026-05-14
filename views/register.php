<div class="auth-container">
    <!-- Left Side: Marketing -->
    <div class="auth-left">
        <div class="auth-brand">
            <a href="index.php"><img src="assets/images/logo_texte_blanc.png" alt="RHXtimes"></a>
        </div>
        <div class="auth-marketing">
            <h1>Rejoignez<br>l'innovation</h1>
            <p>Créez votre compte gratuitement et digitalisez le suivi de vos équipes sur le terrain en moins de 5 minutes.</p>
        </div>
        <div class="trust-badges">
            <div class="trust-badge">
                <i class="fa-solid fa-bolt"></i> Installation en 5min
            </div>
            <div class="trust-badge">
                <i class="fa-solid fa-headset"></i> Support 24/7
            </div>
        </div>
    </div>

    <!-- Right Side: Registration Form -->
    <div class="auth-right" style="padding: 30px 40px; overflow-y: auto;">
        <div class="auth-header">
            <h2>Inscription</h2>
            <p>14 jours d'essai gratuit. Sans engagement.</p>
        </div>

        <form method="POST" action="index.php?page=register_submit">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Nom de l'entreprise</label>
                    <input type="text" name="nom_entreprise" class="form-control" placeholder="Acme Corp" required>
                </div>
                
                <div class="form-group">
                    <label>Secteur d'activité</label>
                    <select name="secteur" class="form-control" required>
                        <option value="">-- Sélectionnez --</option>
                        <option value="Agences de sécurité privée">Agences de sécurité privée</option>
                        <option value="BTP et Infrastructures">BTP et Infrastructures</option>
                        <option value="Commerce">Commerce</option>
                        <option value="Communication et Médias">Communication et Médias</option>
                        <option value="Éducation">Éducation</option>
                        <option value="Finance">Finance</option>
                        <option value="Hôtellerie">Hôtellerie</option>
                        <option value="Industrie et Agriculture">Industrie et Agriculture</option>
                        <option value="Logistique et Transport">Logistique et Transport</option>
                        <option value="ONG">ONG</option>
                        <option value="Restaurant">Restaurant</option>
                        <option value="Santé et Bien-être">Santé et Bien-être</option>
                        <option value="Services et Entretien">Services et Entretien</option>
                        <option value="Autres">Autres</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Nom du responsable</label>
                    <input type="text" name="nom_responsable" class="form-control" placeholder="Dr. Jean Dupont" required>
                </div>

                <div class="form-group">
                    <label>Numéro de Tel</label>
                    <input type="tel" name="telephone" class="form-control" placeholder="+237 ..." required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Pays</label>
                    <select name="pays" id="pays" class="form-control" required onchange="updateVilles()">
                        <option value="">-- Sélectionnez --</option>
                        <option value="Cameroun">🇨🇲 Cameroun</option>
                        <option value="Côte d'Ivoire">🇨🇮 Côte d'Ivoire</option>
                        <option value="Sénégal">🇸🇳 Sénégal</option>
                        <option value="RD Congo">🇨🇩 RD Congo</option>
                        <option value="Gabon">🇬🇦 Gabon</option>
                        <option value="Burkina Faso">🇧🇫 Burkina Faso</option>
                        <option value="Niger">🇳🇪 Niger</option>
                        <option value="Togo">🇹🇬 Togo</option>
                        <option value="Bénin">🇧🇯 Bénin</option>
                        <option value="Congo-Brazzaville">🇨🇬 Congo-Brazzaville</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ville</label>
                    <select name="ville" id="ville" class="form-control" required disabled>
                        <option value="">-- Choisissez d'abord un pays --</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Adresse e-mail valide</label>
                <input type="email" name="email" class="form-control" placeholder="nom@gmail.com" required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Mot de passe</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required minlength="6">
                        <i class="fa-solid fa-eye password-toggle" id="toggleIcon" onclick="togglePassword('password', 'toggleIcon')"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirmer le mot de passe</label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="••••••••" required minlength="6">
                        <i class="fa-solid fa-eye password-toggle" id="toggleIconConfirm" onclick="togglePassword('confirm_password', 'toggleIconConfirm')"></i>
                    </div>
                </div>
            </div>
            
            <input type="hidden" name="localisation" value="Standard">
            
            <div class="form-options" style="margin-bottom: 20px;">
                <label class="checkbox-label" style="font-size: 0.85rem; display: flex; align-items: flex-start;">
                    <input type="checkbox" id="accept_terms" name="accept_terms" required style="margin-top: 3px; margin-right: 8px;">
                    <span>J'ai lu et j'accepte les <a href="index.php?page=terms" target="_blank" class="auth-link">Conditions d'utilisation</a> de RHXtimes.</span>
                </label>
            </div>
            
            <button type="submit" class="btn-auth">
                Démarrer l'essai gratuit <i class="fa-solid fa-rocket"></i>
            </button>
        </form>
        
        <div class="auth-footer">
            Avez-vous déjà un compte ? <a href="index.php?page=login">Se connecter</a>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, iconId) {
    const passInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(iconId);
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

const villesParPays = {
    'Cameroun':                  ['Douala','Yaoundé','Bafoussam','Garoua','Bamenda','Maroua','Ngaoundéré','Buea','Ebolowa','Kribi','Limbe','Bertoua'],
    "Côte d'Ivoire":             ['Abidjan','Bouaké','Yamoussoukro','Korhogo','San-Pédro','Man','Daloa','Gagnoa','Abengourou'],
    'Sénégal':                   ['Dakar','Touba','Thiès','Kaolack','Ziguinchor','Saint-Louis','Mbour','Rufisque'],
    'RD Congo':                  ['Kinshasa','Lubumbashi','Mbuji-Mayi','Kananga','Kisangani','Goma','Bukavu'],
    'Gabon':                     ['Libreville','Port-Gentil','Franceville','Oyem','Moanda'],
    'Burkina Faso':              ['Ouagadougou','Bobo-Dioulasso','Koudougou','Ouahigouya','Banfora','Dédougou'],
    'Niger':                     ['Niamey','Zinder','Maradi','Tahoua','Agadez','Dosso'],
    'Togo':                      ['Lomé','Sokodé','Kara','Kpalimé','Atakpamé','Bassar'],
    'Bénin':                     ['Cotonou','Porto-Novo','Parakou','Djougou','Bohicon','Abomey'],
    'Congo-Brazzaville':         ['Brazzaville','Pointe-Noire','Dolisie','Nkayi','Ouesso','Gamboma']
};

function updateVilles() {
    const pays = document.getElementById('pays').value;
    const villeSelect = document.getElementById('ville');
    
    villeSelect.innerHTML = '';
    
    if (!pays || !villesParPays[pays]) {
        villeSelect.disabled = true;
        villeSelect.innerHTML = '<option value="">-- Choisissez d\'abord un pays --</option>';
        return;
    }
    
    villeSelect.disabled = false;
    villeSelect.innerHTML = '<option value="">-- Sélectionnez une ville --</option>';
    
    villesParPays[pays].forEach(function(ville) {
        const opt = document.createElement('option');
        opt.value = ville;
        opt.textContent = ville;
        villeSelect.appendChild(opt);
    });
}
</script>
