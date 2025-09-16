<?php
// send_reservation.php
// Script PHP pour envoyer les réservations par email
// AUCUN MOT DE PASSE REQUIS - utilise le serveur mail d'Hostinger

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gérer les requêtes OPTIONS (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configuration email - AUCUN MOT DE PASSE NÉCESSAIRE
$destinataire = 'bakabi06@gmail.com'; // Adresse qui recevra les réservations
$expediteur_email = 'noreply@' . $_SERVER['HTTP_HOST']; // Utilise votre domaine automatiquement
$expediteur_nom = 'e-Fast VTC - Réservations';

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'message' => 'Méthode non autorisée. Utilisez POST.'
    ]);
    exit;
}

// Récupération des données du formulaire
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Si les données JSON ne sont pas présentes, essayer $_POST
if (empty($data)) {
    $data = $_POST;
}

// Fonction de validation et nettoyage
function nettoyer_donnee($donnee) {
    if (is_string($donnee)) {
        return htmlspecialchars(strip_tags(trim($donnee)), ENT_QUOTES, 'UTF-8');
    }
    return $donnee;
}

// Validation des champs obligatoires
$champs_obligatoires = ['nom', 'prenom', 'telephone', 'email', 'service', 'date', 'heure', 'depart', 'arrivee'];
$erreurs = [];

foreach ($champs_obligatoires as $champ) {
    if (empty($data[$champ])) {
        $erreurs[] = "Le champ '$champ' est obligatoire";
    }
}

// Validation email
if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $erreurs[] = 'L\'adresse email n\'est pas valide';
}

// Validation téléphone français
if (!empty($data['telephone'])) {
    $tel = preg_replace('/[\s.-]/', '', $data['telephone']);
    if (!preg_match('/^(?:(?:\+|00)33|0)[1-9]\d{8}$/', $tel)) {
        $erreurs[] = 'Le numéro de téléphone n\'est pas valide (format français attendu)';
    }
}

// Validation date (pas dans le passé)
if (!empty($data['date'])) {
    $date_reservation = strtotime($data['date']);
    $aujourd_hui = strtotime(date('Y-m-d'));
    if ($date_reservation < $aujourd_hui) {
        $erreurs[] = 'La date de réservation ne peut pas être dans le passé';
    }
}

// Si des erreurs, les retourner
if (!empty($erreurs)) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreurs de validation : ' . implode(', ', $erreurs)
    ]);
    exit;
}

// Nettoyer toutes les données
$nom = nettoyer_donnee($data['nom']);
$prenom = nettoyer_donnee($data['prenom']);
$telephone = nettoyer_donnee($data['telephone']);
$email = nettoyer_donnee($data['email']);
$service = nettoyer_donnee($data['service']);
$vehicule = nettoyer_donnee($data['vehicule'] ?? 'Non spécifié');
$date = nettoyer_donnee($data['date']);
$heure = nettoyer_donnee($data['heure']);
$depart = nettoyer_donnee($data['depart']);
$arrivee = nettoyer_donnee($data['arrivee']);
$passagers = nettoyer_donnee($data['passagers'] ?? '1');
$duree = nettoyer_donnee($data['duree'] ?? 'Non spécifiée');
$message_client = nettoyer_donnee($data['message'] ?? 'Aucun message particulier');

// Formater la date en français
$date_fr = date('d/m/Y', strtotime($date));
$jour_semaine = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
$jour = $jour_semaine[date('w', strtotime($date))];

// Sujet de l'email
$sujet = "🚗 NOUVELLE RÉSERVATION VTC - $prenom $nom - $date_fr";

// Corps de l'email en HTML
$corps_email = "
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Nouvelle Réservation e-Fast VTC</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container { 
            max-width: 700px; 
            margin: 0 auto; 
            background: #ffffff; 
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .header { 
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 50%, #FF8C00 100%); 
            color: #000; 
            padding: 30px; 
            text-align: center; 
        }
        .header h1 { 
            margin: 0; 
            font-size: 28px; 
            font-weight: bold;
        }
        .urgent-badge {
            background: #ff4444;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
            display: inline-block;
        }
        .content { 
            padding: 30px; 
        }
        .section { 
            margin-bottom: 25px; 
            padding: 20px; 
            background: #f8f9fa; 
            border-radius: 10px; 
            border-left: 4px solid #FFD700;
        }
        .section h3 { 
            color: #2c5f3f; 
            margin-bottom: 15px; 
            margin-top: 0;
            font-size: 18px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .info-item {
            background: white;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        .label { 
            font-weight: bold; 
            color: #666; 
            font-size: 14px;
            margin-bottom: 5px;
        }
        .value { 
            color: #333; 
            font-size: 16px;
            font-weight: 500;
        }
        .message-box { 
            background: #e8f4fd; 
            padding: 20px; 
            border-radius: 8px; 
            border-left: 4px solid #2196F3;
            font-style: italic;
        }
        .action-section {
            background: linear-gradient(135deg, #4CAF50, #66BB6A);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
        }
        .action-button {
            background: white;
            color: #4CAF50;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            margin: 0 10px;
            display: inline-block;
        }
        .footer { 
            background: #2c5f3f; 
            color: white; 
            padding: 20px; 
            text-align: center; 
            font-size: 14px;
        }
        .highlight {
            background: #fff3cd;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>⚡ e-Fast VTC</h1>
            <div class='urgent-badge'>NOUVELLE RÉSERVATION À TRAITER</div>
        </div>
        
        <div class='content'>
            <div class='section'>
                <h3>👤 Informations Client</h3>
                <div class='info-grid'>
                    <div class='info-item'>
                        <div class='label'>Nom complet</div>
                        <div class='value'>$prenom $nom</div>
                    </div>
                    <div class='info-item'>
                        <div class='label'>Téléphone</div>
                        <div class='value'><a href='tel:$telephone'>$telephone</a></div>
                    </div>
                    <div class='info-item'>
                        <div class='label'>Email</div>
                        <div class='value'><a href='mailto:$email'>$email</a></div>
                    </div>
                    <div class='info-item'>
                        <div class='label'>Nombre de passagers</div>
                        <div class='value'>$passagers personne(s)</div>
                    </div>
                </div>
            </div>
            
            <div class='section'>
                <h3>🚙 Détails de la Réservation</h3>
                <div class='info-grid'>
                    <div class='info-item'>
                        <div class='label'>Service demandé</div>
                        <div class='value'><span class='highlight'>$service</span></div>
                    </div>
                    <div class='info-item'>
                        <div class='label'>Véhicule souhaité</div>
                        <div class='value'>$vehicule</div>
                    </div>
                    <div class='info-item'>
                        <div class='label'>Date</div>
                        <div class='value'><span class='highlight'>$jour $date_fr</span></div>
                    </div>
                    <div class='info-item'>
                        <div class='label'>Heure</div>
                        <div class='value'><span class='highlight'>$heure</span></div>
                    </div>
                    <div class='info-item' style='grid-column: 1 / -1;'>
                        <div class='label'>Durée estimée</div>
                        <div class='value'>$duree</div>
                    </div>
                </div>
            </div>
            
            <div class='section'>
                <h3>📍 Itinéraire</h3>
                <div class='info-grid'>
                    <div class='info-item'>
                        <div class='label'>🟢 Lieu de départ</div>
                        <div class='value'>$depart</div>
                    </div>
                    <div class='info-item'>
                        <div class='label'>🔴 Lieu d'arrivée</div>
                        <div class='value'>$arrivee</div>
                    </div>
                </div>
            </div>
            
            <div class='section'>
                <h3>💬 Message du Client</h3>
                <div class='message-box'>
                    $message_client
                </div>
            </div>
            
            <div class='action-section'>
                <h3 style='color: white; margin-top: 0;'>🎯 Actions Prioritaires</h3>
                <p><strong>⏰ Réserver ce créneau :</strong> $jour $date_fr à $heure</p>
                <p><strong>📞 Contacter le client rapidement :</strong></p>
                <a href='tel:$telephone' class='action-button'>Appeler $telephone</a>
                <a href='mailto:$email' class='action-button'>Envoyer un email</a>
            </div>
        </div>
        
        <div class='footer'>
            <p><strong>Email automatique du système de réservation e-Fast VTC</strong></p>
            <p>Service Premium 100% Électrique | Côte d'Azur | www.e-fast-vtc.com</p>
            <p><small>Reçu le " . date('d/m/Y à H:i') . "</small></p>
        </div>
    </div>
</body>
</html>
";

// Headers pour email HTML
$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/html; charset=UTF-8',
    'From: ' . $expediteur_nom . ' <' . $expediteur_email . '>',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion(),
    'X-Priority: 2', // Haute priorité
    'Importance: High'
];

// Tentative d'envoi de l'email principal
$email_envoye = mail($destinataire, $sujet, $corps_email, implode("\r\n", $headers));

// Email de confirmation au client
$sujet_client = "✅ Confirmation de votre réservation e-Fast VTC";
$corps_client = "
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; }
        .header { background: linear-gradient(135deg, #FFD700, #FFA500); color: #000; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .highlight { background: #fff3cd; padding: 3px 8px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>✅ e-Fast VTC</h1>
            <h2>Réservation Confirmée</h2>
        </div>
        <div class='content'>
            <p>Bonjour <strong>$prenom $nom</strong>,</p>
            
            <p>Nous avons bien reçu votre demande de réservation :</p>
            
            <ul>
                <li><strong>Date :</strong> $jour $date_fr</li>
                <li><strong>Heure :</strong> $heure</li>
                <li><strong>Service :</strong> $service</li>
                <li><strong>Trajet :</strong> $depart → $arrivee</li>
            </ul>
            
            <p><strong>🚗 Notre équipe vous contactera dans les plus brefs délais au $telephone pour confirmer tous les détails.</strong></p>
            
            <p>Pour toute question urgente :</p>
            <p>📞 <strong>04 93 46 43 66</strong> (disponible 24h/7j)</p>
            <p>📧 <strong>efastvtc@gmail.com</strong></p>
            
            <p>Merci de faire confiance à e-Fast VTC !</p>
            <p><em>Votre service VTC premium 100% électrique sur la Côte d'Azur</em></p>
        </div>
    </div>
</body>
</html>
";

$headers_client = [
    'MIME-Version: 1.0',
    'Content-Type: text/html; charset=UTF-8',
    'From: ' . $expediteur_nom . ' <' . $expediteur_email . '>',
    'X-Mailer: PHP/' . phpversion()
];

// Envoyer confirmation au client
$confirmation_envoyee = mail($email, $sujet_client, $corps_client, implode("\r\n", $headers_client));

// Logger la réservation dans un fichier
$log_entry = date('Y-m-d H:i:s') . " | $prenom $nom | $telephone | $email | $service | $date $heure | $depart → $arrivee\n";
@file_put_contents('reservations.log', $log_entry, FILE_APPEND | LOCK_EX);

// Réponse JSON
if ($email_envoye) {
    echo json_encode([
        'success' => true,
        'message' => 'Réservation envoyée avec succès ! Nous vous contacterons dans les plus brefs délais.',
        'confirmation_client' => $confirmation_envoyee ? 'Email de confirmation envoyé' : 'Confirmation non envoyée'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'envoi. Veuillez réessayer ou nous contacter directement au 04 93 46 43 66.'
    ]);
}
?>