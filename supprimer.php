<?php
session_start();
require_once 'cnx.php';

if (!isset($_GET['id'])) {
    die("ID d'étudiant non spécifié.");
}

$id = (int) $_GET['id'];

// Supprimer d'abord les projets liés à cet étudiant
$db->prepare("DELETE FROM projets WHERE etudiant_id = ?")->execute([$id]);

// Ensuite supprimer l'étudiant
$db->prepare("DELETE FROM etudiants WHERE id = ?")->execute([$id]);

// Redirection vers la page de gestion
header("Location: gestion_utilisateurs.php");
exit;


