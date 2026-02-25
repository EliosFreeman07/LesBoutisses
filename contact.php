<?php
// Empêcher ABSOLUMENT tout output avant le JSON
ob_start();

// Désactiver TOUS les affichages d'erreurs et warnings
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(0);

// Supprimer tous les handlers d'erreur par défaut
@ini_set('error_prepend_string', '');
@ini_set('error_append_string', '');

// Fonction pour nettoyer et envoyer du JSON
function sendJSON($data) {
    // Nettoyer complètement le buffer de sortie
    while (ob_get_level()) {
        ob_end_clean();
    }
    ob_start();
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    ob_end_flush();
    exit;
}

// Vérification de la méthode POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendJSON([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);
}

// Récupération des données
$nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
$prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validation simple
if (empty($nom) || empty($prenom) || empty($email) || empty($phone) || empty($message)) {
    sendJSON([
        'success' => false,
        'message' => 'Tous les champs sont requis.'
    ]);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJSON([
        'success' => false,
        'message' => 'L\'adresse email n\'est pas valide.'
    ]);
}

// Préparation du contenu
$emailBody = "=== NOUVEAU MESSAGE DE CONTACT ===\n\n";
$emailBody .= "Date: " . date('d/m/Y à H:i:s') . "\n\n";
$emailBody .= "Nom: " . $nom . "\n";
$emailBody .= "Prénom: " . $prenom . "\n";
$emailBody .= "Email: " . $email . "\n";
$emailBody .= "Téléphone: " . $phone . "\n\n";
$emailBody .= "Message:\n" . str_repeat('-', 50) . "\n";
$emailBody .= $message . "\n";
$emailBody .= str_repeat('-', 50) . "\n";

// Détection du mode local (localhost ou 127.0.0.1)
$isLocal = (
    $_SERVER['SERVER_NAME'] === 'localhost' || 
    $_SERVER['SERVER_NAME'] === '127.0.0.1' ||
    $_SERVER['HTTP_HOST'] === 'localhost' ||
    $_SERVER['HTTP_HOST'] === '127.0.0.1'
);

if ($isLocal) {
    // MODE LOCAL : Sauvegarde dans un fichier
    $timestamp = date('Y-m-d_H-i-s');
    $filename = 'message_' . $timestamp . '.txt';
    
    $success = @file_put_contents($filename, $emailBody);
    
    if ($success !== false) {
        sendJSON([
            'success' => true,
            'message' => 'Message enregistré avec succès ! Nous vous répondrons dans les plus brefs délais.' . $filename . ')'
        ]);
    } else {
        sendJSON([
            'success' => false,
            'message' => 'Erreur lors de la sauvegarde du message.'
        ]);
    }
} else {
    // MODE PRODUCTION : Envoi par email
    $to = "giteterrassesdesboutisses@gmail.com";
    $subject = "Nouveau message de contact - Gîte des Boutisses";
    
    // En-têtes email
    $headers = array();
    $headers[] = 'From: noreply@gite-des-boutisses.fr';
    $headers[] = 'Reply-To: ' . $email;
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = 'X-Mailer: PHP/' . phpversion();
    
    // Tentative d'envoi (suppression de TOUS les warnings possibles)
    $mailSent = @mail($to, $subject, $emailBody, implode("\r\n", $headers));
    
    if ($mailSent) {
        sendJSON([
            'success' => true,
            'message' => 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.'
        ]);
    } else {
        // En cas d'échec en production, on sauvegarde quand même dans un fichier
        $timestamp = date('Y-m-d_H-i-s');
        $filename = 'message_backup_' . $timestamp . '.txt';
        @file_put_contents($filename, $emailBody);
        
        sendJSON([
            'success' => false,
            'message' => 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer plus tard ou nous contacter par téléphone.'
        ]);
    }
}
?>

