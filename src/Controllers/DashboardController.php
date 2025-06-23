<?php

namespace App\Controllers;

use App\Utils\Auth;
use App\Utils\Logger;
use App\Utils\Helpers;
use App\Utils\Flash;
use App\Models\Building;
use App\Models\Apartment;
use App\Models\Lease;
use App\Models\Tenant;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Agency;
use App\Models\Owner;
use App\Models\Buyer;
use App\Models\Payment;
use PDOException;

class DashboardController
{
    protected $auth;
    protected $logger;
    protected $helpers;
    protected $flash;

    public function __construct(Auth $auth, Logger $logger, Helpers $helpers, Flash $flash)
    {
        $this->auth = $auth;
        $this->logger = $logger;
        $this->helpers = $helpers;
        $this->flash = $flash;
    }

    public function index()
    {
        // Vérifier l'authentification
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder au tableau de bord.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        // Récupérer les informations de l'utilisateur
        $user = $this->auth->user();
        $role = $user['role'] ?? 'guest';
        $metrics = [];
        $activities = [];
        $tasks = [];

        // Récupérer les données pour le dashboard
        try {
            switch ($role) {
                case 'superadmin':
                    $metrics = $this->getSuperadminMetrics();
                    $activities = AuditLog::getRecentActivities();
                    $tasks = $this->getSuperadminTasks();
                    break;
                case 'admin':
                    $metrics = $this->getAdminMetrics($user['agency_id']);
                    $activities = AuditLog::getRecentActivitiesByAgency($user['agency_id']); // Méthode manquante
                    $tasks = $this->getAdminTasks($user['agency_id']);
                    break;
                case 'agent':
                    $metrics = $this->getAgentMetrics($user['id'], $user['agency_id']);
                    $activities = AuditLog::findByUserId($user['id'], 3);
                    $tasks = $this->getAgentTasks($user['id'], $user['agency_id']);
                    break;
                case 'proprietaire':
                    $metrics = $this->getProprietaireMetrics($user['id']);
                    break;
                case 'locataire':
                    $metrics = $this->getLocataireMetrics($user['id']);
                    break;
                case 'acheteur':
                    $metrics = $this->getAcheteurMetrics($user['id']);
                    break;
                default:
                    $this->flash->flash('error', 'Rôle non autorisé.');
                    $this->helpers->redirect('/auth/login');
                    return;
            }
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du chargement des données du dashboard : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors du chargement des données.');
            $metrics = ['primary' => [], 'overview' => []];
            $activities = [];
            $tasks = ['baux_en_attente' => 0, 'paiements_en_attente' => 0, 'nouveaux_baux' => 0];
        }

        // Adapter les actions rapides selon le rôle
        $showActions = in_array($role, ['superadmin', 'admin', 'agent']);

        // Charger la vue du dashboard
        $title = 'Tableau de bord';
        $content_view = 'admin/dashboard/index.php';
        require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
    }

    private function getSuperadminMetrics()
    {
        return [
            'primary' => [
                'chiffre_affaires' => [
                    'value' => Lease::calculateTotalRevenue() . ' FCFA',
                    'label' => 'Chiffre d\'affaires',
                    'emoji' => '💸'
                ],
                'baux_actifs' => [
                    'value' => Lease::countActive(),
                    'label' => 'Baux actifs',
                    'emoji' => '📜'
                ],
                'appartements_disponibles' => [
                    'value' => Apartment::countAvailable(),
                    'label' => 'Appartements disponibles',
                    'emoji' => '🏡'
                ],
                'batiments' => [
                    'value' => Building::countAll(),
                    'label' => 'Bâtiments',
                    'emoji' => '🏢'
                ]
            ],
            'overview' => [
                'agences' => [
                    'value' => count(Agency::get()),
                    'label' => 'Agences',
                    'emoji' => '🏢'
                ],
                'proprietaires' => [
                    'value' => count(Owner::get()),
                    'label' => 'Propriétaires',
                    'emoji' => '👥'
                ],
                'locataires' => [
                    'value' => Tenant::countAll(),
                    'label' => 'Locataires',
                    'emoji' => '👥'
                ],
                'acheteurs' => [
                    'value' => count(Buyer::get()),
                    'label' => 'Acheteurs',
                    'emoji' => '👥'
                ]
            ]
        ];
    }

    private function getAdminMetrics($agency_id)
    {
        return [
            'primary' => [
                'chiffre_affaires' => [
                    'value' => Lease::calculateTotalRevenueByAgency($agency_id) . ' FCFA', // Méthode manquante
                    'label' => 'Chiffre d\'affaires',
                    'emoji' => '💸'
                ],
                'baux_actifs' => [
                    'value' => Lease::countActiveByAgency($agency_id), // Méthode manquante
                    'label' => 'Baux actifs',
                    'emoji' => '📜'
                ],
                'appartements_disponibles' => [
                    'value' => Apartment::countAvailableByAgency($agency_id), // Méthode manquante
                    'label' => 'Appartements disponibles',
                    'emoji' => '🏡'
                ],
                'batiments' => [
                    'value' => Building::countByAgency($agency_id), // Méthode manquante
                    'label' => 'Bâtiments',
                    'emoji' => '🏢'
                ]
            ],
            'overview' => [
                'agents' => [
                    'value' => User::countAgentsByAgency($agency_id), // Méthode manquante
                    'label' => 'Agents',
                    'emoji' => '👥'
                ],
                'proprietaires' => [
                    'value' => Owner::countByAgency($agency_id), // Méthode manquante
                    'label' => 'Propriétaires',
                    'emoji' => '👥'
                ],
                'locataires' => [
                    'value' => Tenant::countByAgency($agency_id), // Méthode manquante
                    'label' => 'Locataires',
                    'emoji' => '👥'
                ]
            ]
        ];
    }

    private function getAgentMetrics($agent_id, $agency_id)
    {
        return [
            'primary' => [
                'chiffre_affaires' => [
                    'value' => Lease::calculateTotalRevenueByAgent($agent_id, $agency_id) . ' FCFA', // Méthode manquante
                    'label' => 'Chiffre d\'affaires',
                    'emoji' => '💸'
                ],
                'baux_actifs' => [
                    'value' => Lease::countActiveByAgent($agent_id, $agency_id), // Méthode manquante
                    'label' => 'Baux actifs',
                    'emoji' => '📜'
                ],
                'appartements_disponibles' => [
                    'value' => Apartment::countAvailableByAgent($agent_id, $agency_id), // Méthode manquante
                    'label' => 'Appartements disponibles',
                    'emoji' => '🏡'
                ],
                'batiments' => [
                    'value' => Building::countByAgent($agent_id, $agency_id), // Méthode manquante
                    'label' => 'Bâtiments',
                    'emoji' => '🏢'
                ]
            ],
            'overview' => [
                'proprietaires' => [
                    'value' => Owner::countByAgent($agent_id), // Méthode manquante
                    'label' => 'Propriétaires',
                    'emoji' => '👥'
                ],
                'locataires' => [
                    'value' => Tenant::countByAgent($agent_id), // Méthode manquante
                    'label' => 'Locataires',
                    'emoji' => '👥'
                ]
            ]
        ];
    }

    private function getProprietaireMetrics($user_id)
    {
        $owner = Owner::findByUserId($user_id);

        return [
            'primary' => [
                'revenus' => [
                    'value' => Lease::calculateRevenueByOwner($owner->getId()) . ' FCFA', // Méthode manquante
                    'label' => 'Revenus locatifs',
                    'emoji' => '💸'
                ],
                'appartements_loues' => [
                    'value' => Apartment::countRentedByOwner($owner->getId()), // Méthode manquante
                    'label' => 'Appartements loués',
                    'emoji' => '🏡'
                ],
                'appartements_disponibles' => [
                    'value' => Apartment::countAvailableByOwner($owner->getId()), // Méthode manquante
                    'label' => 'Appartements disponibles',
                    'emoji' => '🏡'
                ],
                'nouveaux_locataires' => [
                    'value' => Tenant::countNewByOwnerThisMonth($owner->getId()), // Méthode manquante
                    'label' => 'Nouveaux locataires ce mois',
                    'emoji' => '👥'
                ]
            ]
        ];
    }

    private function getLocataireMetrics($user_id)
    {
        $tenant = Tenant::findByUserId($user_id);

        return [
            'primary' => [
                'loyer' => [
                    'value' => Lease::getRentAmountByTenant($tenant['id']) . ' FCFA', // Méthode manquante
                    'label' => 'Loyer mensuel',
                    'emoji' => '💸'
                ],
                'baux_actifs' => [
                    'value' => Lease::countActiveByTenant($tenant['id']), // Méthode manquante
                    'label' => 'Baux actifs',
                    'emoji' => '📜'
                ],
                'appartements_occupes' => [
                    'value' => Apartment::countOccupiedByTenant($tenant['id']), // Méthode manquante
                    'label' => 'Appartements occupés',
                    'emoji' => '🏡'
                ],
                'paiements_effectues' => [
                    'value' => Payment::countPaidByTenant($tenant['id']), // Méthode manquante
                    'label' => 'Paiements effectués',
                    'emoji' => '✅'
                ]
            ]
        ];
    }

    private function getAcheteurMetrics($user_id)
    {
        $buyer = Buyer::findByUserId($user_id);

        return [
            'primary' => [
                'appartements_disponibles' => [
                    'value' => Apartment::countAvailable(),
                    'label' => 'Appartements disponibles',
                    'emoji' => '🏡'
                ],
                'batiments' => [
                    'value' => Building::countAll(),
                    'label' => 'Bâtiments disponibles',
                    'emoji' => '🏢'
                ],
                'nouveaux_appartements' => [
                    'value' => Apartment::countNewAvailableThisMonth(),
                    'label' => 'Nouveaux appartements ce mois',
                    'emoji' => '🆕'
                ],
                'baux_associes' => [
                    'value' => Lease::hasLeasesForBuyer($buyer['id']),
                    'label' => 'Baux associés',
                    'emoji' => '📜'
                ]
            ]
        ];
    }

    private function getSuperadminTasks()
    {
        $tasks = AuditLog::getUrgentTasks();

        return [
            'baux_en_attente' => [
                'value' => $tasks['pendingLeases'] ?? 0,
                'label' => 'Baux en attente',
                'emoji' => '📜'
            ],
            'paiements_en_attente' => [
                'value' => Payment::countPending(), // Méthode manquante
                'label' => 'Paiements en attente',
                'emoji' => '⏳'
            ],
            'nouveaux_baux' => [
                'value' => Lease::countNewThisMonth(), // Méthode manquante
                'label' => 'Nouveaux baux ce mois',
                'emoji' => '🆕'
            ]
        ];
    }

    private function getAdminTasks($agency_id)
    {
        $tasks = AuditLog::getUrgentTasksByAgency($agency_id); // Méthode manquante

        return [
            'baux_en_attente' => [
                'value' => $tasks['pendingLeases'] ?? 0,
                'label' => 'Baux en attente',
                'emoji' => '📜'
            ],
            'paiements_en_attente' => [
                'value' => Payment::countPendingByAgency($agency_id), // Méthode manquante
                'label' => 'Paiements en attente',
                'emoji' => '⏳'
            ],
            'nouveaux_baux' => [
                'value' => Lease::countNewByAgencyThisMonth($agency_id), // Méthode manquante
                'label' => 'Nouveaux baux ce mois',
                'emoji' => '🆕'
            ]
        ];
    }

    private function getAgentTasks($agent_id, $agency_id)
    {
        $tasks = AuditLog::getUrgentTasksByAgent($agent_id, $agency_id); // Méthode manquante

        return [
            'baux_en_attente' => [
                'value' => $tasks['pendingLeases'] ?? 0,
                'label' => 'Baux en attente',
                'emoji' => '📜'
            ],
            'paiements_en_attente' => [
                'value' => Payment::countPendingByAgent($agent_id, $agency_id), // Méthode manquante
                'label' => 'Paiements en attente',
                'emoji' => '⏳'
            ],
            'nouveaux_baux' => [
                'value' => Lease::countNewByAgentThisMonth($agent_id, $agency_id), // Méthode manquante
                'label' => 'Nouveaux baux ce mois',
                'emoji' => '🆕'
            ]
        ];
    }
}
?>