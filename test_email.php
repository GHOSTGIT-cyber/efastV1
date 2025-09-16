<?php
// test_email.php - Script de test pour vérifier l'envoi d'emails
// Créez ce fichier et accédez-y via votre navigateur pour tester

// Configuration
$destinataire = 'bakabi06@gmail.com';
$expediteur = 'noreply@' . $_SERVER['HTTP_HOST'];

// Test simple
$sujet = 'Test e-Fast VTC - Configuration Email';
$message = '
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <h2>✅ Test de Configuration Email</h2>
    <p>Si vous recevez cet email, la configuration fonctionne parfaitement !</p>
    <p><strong>Serveur :</strong> ' . $_SERVER['HTTP_HOST'] . '</p>
    <p><strong>Date/Heure :</strong> ' . date('d/m/Y H:i:s') . '</p>
    <p><strong>IP serveur :</strong> ' . $_SERVER['SERVER_ADDR'] . '</p>
    
    <div style="background: #f0f8ff; padding: 15px; border-radius: 8px; margin: 20px 0;">
        <h3>Informations système :</h3>
        <p>✅ PHP Version : ' . phpversion() . '</p>
        <p>✅ Fonction mail() : ' . (function_exists('mail') ? 'Disponible' : 'Non disponible') . '</p>
        <p>✅ Serveur : ' . $_SERVER['SERVER_SOFTWARE'] . '</p>
    </div>
    
    <p style="color: #2c5f3f; font-weight: bold;">
        Le système de réservation e-Fast VTC est prêt ! 🚗⚡
    </p>
</body>
</html>
';

$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/html; charset=UTF-8',
    'From: e-Fast VTC Test <' . $expediteur . '>',
    'X-Mailer: PHP/' . phpversion()
];

echo '<html><head><meta charset="UTF-8"></head><body>';
echo '<h1>🧪 Test de Configuration Email e-Fast VTC</h1>';

if (function_exists('mail')) {
    echo '<p>✅ La fonction mail() est disponible</p>';
    
    $resultat = mail($destinataire, $sujet, $message, implode("\r\n", $headers));
    
    if ($resultat) {
        echo '<div style="background: #d4edda; color: #155724; padding: 20px; border-radius: 8px; margin: 20px 0;">';
        echo '<h2>✅ Succès !</h2>';
        echo '<p><strong>Email de test envoyé avec succès à : ' . $destinataire . '</strong></p>';
        echo '<p>Vérifiez votre boîte mail (et le dossier spam)</p>';
        echo '<p>Si vous ne recevez pas l\'email dans 5 minutes, contactez le support Hostinger</p>';
        echo '</div>';
    } else {
        echo '<div style="background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; margin: 20px 0;">';
        echo '<h2>❌ Échec</h2>';
        echo '<p>Impossible d\'envoyer l\'email</p>';
        echo '<p>Contactez le support Hostinger pour activer l\'envoi d\'emails</p>';
        echo '</div>';
    }
} else {
    echo '<div style="background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; margin: 20px 0;">';
    echo '<p>❌ La fonction mail() n\'est pas disponible sur ce serveur</p>';
    echo '<p>Contactez Hostinger pour l\'activer</p>';
    echo '</div>';
}

echo '<hr>';
echo '<h3>Informations du serveur :</h3>';
echo '<ul>';
echo '<li><strong>Nom du serveur :</strong> ' . $_SERVER['HTTP_HOST'] . '</li>';
echo '<li><strong>PHP Version :</strong> ' . phpversion() . '</li>';
echo '<li><strong>Serveur web :</strong> ' . $_SERVER['SERVER_SOFTWARE'] . '</li>';
echo '<li><strong>Email expéditeur configuré :</strong> ' . $expediteur . '</li>';
echo '</ul>';

echo '<p><a href="index.html">← Retour au site</a></p>';
echo '</body></html>';
?>
