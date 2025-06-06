<?php

namespace App\Utils;

use App\Models\User;

class Auth
{
    private $user = null;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->loadUser();
    }

    private function loadUser()
    {
        if (isset($_SESSION['user_id'])) {
            $userModel = new User();
            $userObject = $userModel->find($_SESSION['user_id']);
            if ($userObject) {
                // Convertir l'objet User en tableau
                $this->user = [
                    'id' => $userObject->getId(),
                    'username' => $userObject->getUsername(),
                    'email' => $userObject->getEmail(),
                    'role_id' => $userObject->getRoleId(),
                    'agency_id' => $userObject->getAgencyId(),
                    'first_name' => $userObject->getFirstName(),
                    'last_name' => $userObject->getLastName(),
                    'phone' => $userObject->getPhone(),
                    'created_at' => $userObject->getCreatedAt(),
                    'updated_at' => $userObject->getUpdatedAt(),
                    'is_deleted' => $userObject->getIsDeleted(),
                    // Ajouter les données de session
                    'role' => $_SESSION['role'] ?? null,
                    'owner_id' => $_SESSION['owner_id'] ?? null,
                    'tenant_id' => $_SESSION['tenant_id'] ?? null
                ];
            } else {
                // Utilisateur non trouvé dans la base, déconnexion automatique
                $this->logout();
            }
        }
    }

    public function check(): bool
    {
        return $this->user !== null;
    }

    public function user(): ?array
    {
        return $this->user;
    }

    public function id(): ?int
    {
        return $this->user ? (int)$this->user['id'] : null;
    }

    public function hasRole(string $role): bool
    {
        return $this->check() && isset($this->user['role']) && $this->user['role'] === $role;
    }

    public function restrict(array $allowedRoles): void
    {
        // Autoriser l'accès pour 'guest' si non authentifié
        if (in_array('guest', $allowedRoles, true) && !$this->check()) {
            return;
        }

        // Rediriger si non authentifié
        if (!$this->check()) {
            $helpers = new Helpers();
            $helpers->redirect('/auth/login');
        }

        // Rediriger si rôle non autorisé
        if (!in_array($this->user['role'], $allowedRoles, true)) {
            $flash = new Flash();
            $flash->flash('error', 'Accès non autorisé.');
            $helpers = new Helpers();
            $helpers->redirect('/403');
        }
    }

    public function getRoleRestrictions(string $role): array
    {
        $restrictions = [];
        switch ($role) {
            case 'superadmin':
                break;
            case 'admin':
                $restrictions['agency_id'] = ':agency_id';
                break;
            case 'agent':
                $restrictions['agency_id'] = ':agency_id';
                $restrictions['agent_id'] = ':user_id';
                break;
            case 'proprietaire':
                $restrictions['owner_id'] = ':owner_id';
                break;
            case 'locataire':
                $restrictions['tenant_id'] = ':tenant_id';
                break;
        }
        return $restrictions;
    }

    public function logout(): void
    {
        session_destroy();
        $this->user = null;
    }
}