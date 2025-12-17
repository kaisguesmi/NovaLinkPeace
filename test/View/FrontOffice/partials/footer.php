<!-- Footer PeaceLink (Thème Esprit) -->
<footer style="background-color: #2c3e50; color: #ecf0f1; padding: 60px 20px 30px; margin-top: 80px; border-top: 5px solid #7bd389; font-family: 'Segoe UI', sans-serif;">
    
    <div style="max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 40px;">
        
        <!-- Colonne 1 : Identité -->
        <div>
            <h3 style="color: white; margin-bottom: 20px; font-size: 24px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-leaf" style="color: #7bd389;"></i> 
                <span style="color: #5dade2;">Peace</span>Link
            </h3>
            <p style="color: #bdc3c7; line-height: 1.8; font-size: 14px;">
                Une plateforme communautaire née au cœur du pôle technologique. Partagez, agissez et connectez-vous pour un impact durable.
            </p>
        </div>
        
        <!-- Colonne 2 : Liens Rapides -->
        <div>
            <h4 style="color: #7bd389; margin-bottom: 20px; text-transform: uppercase; font-size: 16px; letter-spacing: 1px;">Navigation</h4>
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 12px;">
                    <a href="index.php" style="color: #bdc3c7; text-decoration: none; transition: 0.3s; display: flex; align-items: center; gap: 10px;" 
                       onmouseover="this.style.color='#5dade2'; this.style.paddingLeft='5px'" 
                       onmouseout="this.style.color='#bdc3c7'; this.style.paddingLeft='0'">
                        <i class="fas fa-chevron-right" style="font-size: 10px; color: #5dade2;"></i> Accueil
                    </a>
                </li>
                <li style="margin-bottom: 12px;">
                    <a href="histoires.php" style="color: #bdc3c7; text-decoration: none; transition: 0.3s; display: flex; align-items: center; gap: 10px;"
                       onmouseover="this.style.color='#5dade2'; this.style.paddingLeft='5px'" 
                       onmouseout="this.style.color='#bdc3c7'; this.style.paddingLeft='0'">
                        <i class="fas fa-chevron-right" style="font-size: 10px; color: #5dade2;"></i> Histoires
                    </a>
                </li>
                <li style="margin-bottom: 12px;">
                    <a href="initiatives.php" style="color: #bdc3c7; text-decoration: none; transition: 0.3s; display: flex; align-items: center; gap: 10px;"
                       onmouseover="this.style.color='#5dade2'; this.style.paddingLeft='5px'" 
                       onmouseout="this.style.color='#bdc3c7'; this.style.paddingLeft='0'">
                        <i class="fas fa-chevron-right" style="font-size: 10px; color: #5dade2;"></i> Initiatives
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Colonne 3 : Contact Esprit -->
        <div>
            <h4 style="color: #7bd389; margin-bottom: 20px; text-transform: uppercase; font-size: 16px; letter-spacing: 1px;">Nous trouver</h4>
            <ul style="list-style: none; padding: 0; color: #bdc3c7; font-size: 14px;">
                <li style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 15px;">
                    <i class="fas fa-map-marker-alt" style="color: #5dade2; margin-top: 4px;"></i>
                    <span>Pôle Technologique El Ghazela,<br>Route de Raoued, Ariana, Tunisie</span>
                </li>
                <li style="margin-bottom: 15px; display: flex; align-items: center; gap: 15px;">
                    <i class="fas fa-phone" style="color: #5dade2;"></i>
                    <span>+216 70 250 000</span>
                </li>
                <li style="display: flex; align-items: center; gap: 15px;">
                    <i class="fas fa-envelope" style="color: #5dade2;"></i>
                    <span>contact@peacelink.tn</span>
                </li>
            </ul>
        </div>
    </div>
    
    <!-- Copyright -->
    <div style="max-width: 1100px; margin: 40px auto 0; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); text-align: center; color: #7f8c8d; font-size: 13px;">
        <p>&copy; <?= date('Y') ?> PeaceLink. Tous droits réservés.</p>
    </div>

    <style>
        @keyframes heartbeat {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
    </style>
</footer>