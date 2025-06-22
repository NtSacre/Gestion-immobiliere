<?php
// Le mot de passe à hasher
$password = "admin";

// Génération du hash avec Bcrypt
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Affichage dans la console (pour CLI)
echo   $hashedPassword ;