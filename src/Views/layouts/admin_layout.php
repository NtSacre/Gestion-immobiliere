<?php
namespace App\Views\layouts;

use App\Utils\Flash;
use App\Utils\Auth;
use App\Models\Lease;
use App\Utils\Helpers;
use Exception;

// Logique centralisée en haut
error_log("Rendu du layout admin pour rôle: " . ($_SESSION['role'] ?? 'guest'));

// Authentification et rôle
$flash = new Flash();
$auth = new Auth();
$role = $auth->user()['role'] ?? 'guest';
$activeRoute = $_SERVER['REQUEST_URI'];

// Données utilisateur
$username = $auth->user()['username'] ?? 'Invité';
$firstName = $auth->user()['first_name'] ?? '';
$lastName = $auth->user()['last_name'] ?? '';
$userId = $auth->user()['id'] ?? 0;
$initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
$isAuthenticated = $auth->check();

// Description du rôle pour le header
$roleDescription = match ($role) {
    'superadmin' => 'Gestion globale du système',
    'admin' => 'Vue d’ensemble de votre agence',
    'agent' => 'Gestion de vos clients et biens',
    'proprietaire' => 'Vue d’ensemble de vos propriétés',
    'locataire' => 'Gestion de votre location',
    'acheteur' => 'Biens disponibles à l’achat',
    default => 'Bienvenue sur ImmoApp'
};

// Vérification des baux pour un acheteur
$hasLeasesForBuyer = $role === 'acheteur' && $isAuthenticated ? Lease::hasLeasesForBuyer($userId) : false;

// Données pour quickActionsMenu
$quickActions = [];
if ($isAuthenticated) {
    $quickActions = match ($role) {
        'superadmin' => [
            ['label' => 'Consulter audit', 'icon' => '📋', 'href' => '/audit-log'],
            ['label' => 'Exporter', 'icon' => '📤', 'href' => '/export']
        ],
        'admin' => [
            ['label' => 'Consulter audit', 'icon' => '📋', 'href' => '/audit-log'],
            ['label' => 'Gérer utilisateurs', 'icon' => '👥', 'href' => '/users']
        ],
        'agent' => [
            ['label' => 'Voir paiements', 'icon' => '💸', 'href' => '/payments']
        ],
        'proprietaire' => [
            ['label' => 'Voir baux', 'icon' => '📜', 'href' => '/leases']
        ],
        'locataire' => [], // Plus d'action pour locataire (suppression quittance)
        'acheteur' => [
            ['label' => 'Voir biens', 'icon' => '🏢', 'href' => '/buildings']
        ],
        default => []
    };
}

// CSRF pour le formulaire de suppression
// Réutiliser l'instance de Helpers passée depuis le contrôleur
if (!isset($helpers) && isset($this) && isset($this->helpers)) {
    $helpers = $this->helpers; // Tenter de récupérer depuis le contrôleur si disponible
}

if (!isset($helpers)) {
    // Si $helpers est toujours absent, utiliser une instance avec Logger par défaut (déprécié mais temporaire)
    require_once __DIR__ . '/../../../vendor/autoload.php';
    $logger = new \App\Utils\Logger();
    $helpers = new \App\Utils\Helpers($logger);
    echo "<!-- Warning: Helpers instancié manuellement dans le layout -->";
}

$csrf = $helpers;
// On prépare un tableau des routes utilisées
$csrfRoutes = [
    'users.delete', 
    'buildings.delete', 
    'tenants.delete', 
    'owners.delete', 
    'apartements.delete',
    'roles.delete',
    'agencies.delete',
    'apartment-types.delete',
    'building-types.delete',
    'payments.delete',
    'leases.delete'
];

// Génère un tableau associatif route_name => token
$csrfTokens = [];
foreach ($csrfRoutes as $routeName) {
    $csrfTokens[$routeName] = $csrf->generateCsrfToken($routeName); // Correction ici
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ImmoApp - <?php echo htmlspecialchars($title ?? 'Tableau de bord'); ?></title>

    <link rel="icon" href="/assets/images/favicon.png" type="image/png">
    <!-- Ajout des icônes Font Awesome si pas déjà incluses -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'construction-yellow': '#FFD700',
                        'construction-dark-yellow': '#FFC107',
                        'construction-black': '#1A1A1A',
                        'construction-gray': '#2D2D2D'
                    }
                }
            }
        }
    </script>
    <script>
    const csrfTokens = <?= json_encode($csrfTokens); ?>;
    </script>
    <style>
        .gradient-yellow { background: linear-gradient(135deg, #FFD700 0%, #FFC107 100%); }
        .sidebar-item:hover { background: linear-gradient(90deg, rgba(255, 215, 0, 0.1), transparent); border-left: 3px solid #FFD700; }
        .sidebar-section { border-top: 1px solid #374151; padding-top: 0.75rem; margin-top: 0.75rem; }
        .sidebar-section:first-child { border-top: none; padding-top: 0; margin-top: 0; }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); }
        .mobile-menu { transform: translateX(-100%); transition: transform 0.3s ease; }
        .mobile-menu.active { transform: translateX(0); }
        .overlay { backdrop-filter: blur(4px); background: rgba(0, 0, 0, 0.5); }
        .modal { backdrop-filter: blur(8px); background: rgba(0, 0, 0, 0.6); }
        .input-focus:focus { border-color: #FFD700; box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1); }
        .table-striped tbody tr:nth-child(even) { background-color: rgba(249, 250, 251, 0.5); }
        .btn-primary { background: linear-gradient(135deg, #FFD700 0%, #FFC107 100%); color: #1A1A1A; font-weight: 600; transition: all 0.3s ease; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3); }
        .btn-secondary { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; font-weight: 500; transition: all 0.3s ease; }
        .btn-secondary:hover { background: #e5e7eb; border-color: #9ca3af; }
        .btn-danger { background: #ef4444; color: white; font-weight: 500; transition: all 0.3s ease; }
        .btn-danger:hover { background: #dc2626; transform: translateY(-1px); }
        .breadcrumb-item::after { content: '/'; margin: 0 0.5rem; color: #9ca3af; }
        .breadcrumb-item:last-child::after { display: none; }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <div id="mobileOverlay" class="fixed inset-0 overlay z-40 lg:hidden hidden"></div>
    <div class="flex h-screen">
        <!-- Mobile Menu Button -->
        <button id="mobileMenuBtn" class="fixed top-4 left-4 z-50 lg:hidden bg-construction-yellow p-2 rounded-lg shadow-lg">
            <svg class="w-6 h-6 text-construction-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <!-- Sidebar -->
        <aside id="sidebar" class="mobile-menu fixed lg:static inset-y-0 left-0 z-50 w-64 bg-construction-black text-white flex-shrink-0 lg:transform-none">
            <button id="closeMobileMenu" class="absolute top-4 right-4 lg:hidden p-2 text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="p-6 h-full flex flex-col">
                <!-- Logo -->
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 gradient-yellow rounded-lg flex items-center justify-center">
                        <span class="text-construction-black font-bold text-xl">I</span>
                    </div>
                    <span class="text-xl font-bold">ImmoApp</span>
                </div>

                <!-- Navigation -->
                <nav class="space-y-1 flex-1 overflow-y-auto">
                    <!-- Dashboard -->
                    <div class="sidebar-section">
                        <a href="/dashboard" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php if (strpos($activeRoute, '/dashboard') !== false) echo 'bg-construction-yellow text-construction-black font-semibold'; else echo 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">🏠</span><span class="text-sm">Dashboard</span>
                        </a>
                    </div>

                    <!-- Biens -->
                    <?php if (in_array($role, ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire', 'acheteur'])) : ?>
                    <div class="sidebar-section">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-4">Biens</h3>
                        <a href="/buildings" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php if (strpos($activeRoute, '/buildings') !== false) echo 'bg-construction-yellow text-construction-black font-semibold'; else echo 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">🏢</span>
                            <span class="text-sm">
                                <?php echo $role === 'proprietaire' ? 'Mes propriétés' : ($role === 'acheteur' ? 'À vendre' : 'Bâtiments'); ?>
                            </span>
                        </a>
                        <?php if (in_array($role, ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire'])) : ?>
                        <a href="/apartments" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php if (strpos($activeRoute, '/apartments') !== false) echo 'bg-construction-yellow text-construction-black font-semibold'; else echo 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">🏠</span>
                            <span class="text-sm">
                                <?php echo $role === 'proprietaire' ? 'Mes appartements' : ($role === 'locataire' ? 'Mon appartement' : 'Appartements'); ?>
                            </span>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Clients -->
                    <?php if (in_array($role, ['superadmin', 'admin', 'agent'])) : ?>
                    <div class="sidebar-section">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-4">Clients</h3>
                        <a href="/users" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php if (strpos($activeRoute, '/users') !== false) echo 'bg-construction-yellow text-construction-black font-semibold'; else echo 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">👥</span>
                            <span class="text-sm"><?php echo $role === 'agent' ? 'Mes clients' : 'Utilisateurs'; ?></span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Transactions -->
                    <?php if (in_array($role, ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire', 'acheteur'])) : ?>
                    <div class="sidebar-section">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-4">Transactions</h3>
                        <?php if (in_array($role, ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire']) || ($role === 'acheteur' && $hasLeasesForBuyer)) : ?>
                        <a href="/leases" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php if (strpos($activeRoute, '/leases') !== false) echo 'bg-construction-yellow text-construction-black font-semibold'; else echo 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">📜</span>
                            <span class="text-sm"><?php echo in_array($role, ['proprietaire', 'locataire', 'acheteur']) ? 'Mes baux' : 'Baux'; ?></span>
                        </a>
                        <?php endif; ?>
                        <?php if (in_array($role, ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire'])) : ?>
                        <a href="/payments" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php if (strpos($activeRoute, '/payments') !== false) echo 'bg-construction-yellow text-construction-black font-semibold'; else echo 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">💸</span><span class="text-sm">Paiements</span>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Administration -->
                    <?php if ($role === 'superadmin') : ?>
                    <div class="sidebar-section">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-4">Administration</h3>
                        <a href="/agencies" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php if (strpos($activeRoute, '/agencies') !== false) echo 'bg-construction-yellow text-construction-black font-semibold'; else echo 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">🏢</span><span class="text-sm">Agences</span>
                        </a>
                        <a href="/users" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php if (strpos($activeRoute, '/users') !== false) echo 'bg-construction-yellow text-construction-black font-semibold'; else echo 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">👥</span><span class="text-sm">Utilisateurs</span>
                        </a>
                        <a href="/audit-log" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php if (strpos($activeRoute, '/audit-log') !== false) echo 'bg-construction-yellow text-construction-black font-semibold'; else echo 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">📋</span><span class="text-sm">Audit</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Configuration -->
                    <?php if ($role === 'superadmin') : ?>
                    <div class="sidebar-section">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-4 flex items-center cursor-pointer" onclick="toggleConfigMenu()">
                            <span class="mr-2">⚙️</span>Configurer
                            <svg id="configArrow" class="w-4 h-4 ml-auto transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </h3>
                        <div id="configMenu" class="hidden pl-4">
                            <a href="/building-types" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php if (strpos($activeRoute, '/building-types') !== false) echo 'bg-construction-yellow text-construction-black font-semibold'; else echo 'text-gray-300 hover:text-white'; ?>">
                                <span class="text-base">🏗️</span><span class="text-sm">Types bâtiments</span>
                            </a>
                            <a href="/apartment-types" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php if (strpos($activeRoute, '/apartment-types') !== false) echo 'bg-construction-yellow text-construction-black font-semibold'; else echo 'text-gray-300 hover:text-white'; ?>">
                                <span class="text-base">🏠</span><span class="text-sm">Types appartements</span>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- User Profile -->
                    <?php if ($isAuthenticated) : ?>
                    <div class="border-t border-gray-700 pt-4 mt-4">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-10 h-10 gradient-yellow rounded-full flex items-center justify-center">
                                <span class="text-construction-black font-bold text-sm"><?php echo htmlspecialchars($initials); ?></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm text-white truncate"><?php echo htmlspecialchars("$firstName $lastName"); ?></div>
                                <div class="text-gray-400 text-xs"><?php echo ucfirst($role); ?></div>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <a href="/profile" class="sidebar-item flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-300 hover:text-white">
                                <span class="text-base">👤</span><span class="text-sm">Profil</span>
                            </a>
                            <a href="/logout" class="sidebar-item flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-300 hover:text-white">
                                <span class="text-base">🚪</span><span class="text-sm">Déconnexion</span>
                            </a>
                        </div>
                    </div>
                    <?php else : ?>
                    <div class="border-t border-gray-700 pt-4 mt-4 space-y-1">
                        <a href="/auth/login" class="sidebar-item flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-300 hover:text-white">
                            <span class="text-base">🔑</span><span class="text-sm">Connexion</span>
                        </a>
                        <a href="/auth/register" class="sidebar-item flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-300 hover:text-white">
                            <span class="text-base">📝</span><span class="text-sm">Inscription</span>
                        </a>
                    </div>
                    <?php endif; ?>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto lg:ml-0">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-4 lg:px-6 py-4">
                    <!-- Breadcrumb -->
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <span class="breadcrumb-item">ImmoApp</span>
                        <span class="breadcrumb-item"><?php echo ucfirst($role); ?></span>
                        <span class="text-construction-yellow font-medium"><?php echo htmlspecialchars($title ?? 'Tableau de bord'); ?></span>
                    </div>
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="ml-12 lg:ml-0">
                            <h1 class="text-xl lg:text-2xl font-bold text-construction-black">
                                <a href="/dashboard" class="hover:underline">Bonjour, <?php echo htmlspecialchars($username); ?> !</a>
                            </h1>
                            <p class="text-sm lg:text-base text-gray-600"><?php echo htmlspecialchars($roleDescription); ?></p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <!-- Quick Actions Menu -->
                            <div class="relative">
                                <button id="quickActionsBtn" class="btn-primary px-4 py-2 rounded-lg font-medium hover:scale-105 transition-all flex items-center space-x-2">
                                    <span>Actions</span>
                                    <span class="text-sm">⚡</span>
                                </button>
                                <div id="quickActionsMenu" class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50 hidden">
                                    <?php foreach ($quickActions as $action) : ?>
                                    <a href="<?php echo $action['href']; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                                        <span class="mr-2"><?php echo $action['icon']; ?></span><?php echo $action['label']; ?>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-4 lg:p-6 space-y-6">
                <?php if ($error = $flash->get('error')) : ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <?php if ($success = $flash->get('success')) : ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                <?php include __DIR__ . '/../' . $content_view; ?>
            </div>
        </main>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="confirmDeleteModal" class="modal fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <span class="text-red-600 text-xl">⚠️</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-construction-black">Confirmer la suppression</h3>
                        <p class="text-sm text-gray-600">Cette action est irréversible</p>
                    </div>
                </div>
                <p class="text-gray-700 mb-6">Êtes-vous sûr de vouloir supprimer cet élément <span id="entityName" class="text-xl font-semibold text-construction-black"></span> ? </p>
                <form id="deleteEntityForm" method="POST" action="">
                    <input class="hidden" name="csrf_token" value="">
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('confirmDeleteModal')" class="btn-secondary px-4 py-2 rounded-lg">Annuler</button>
                        <button type="submit" class="btn-danger px-4 py-2 rounded-lg">Supprimer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Mobile Menu Functions
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const mobileOverlay = document.getElementById('mobileOverlay');
            sidebar.classList.toggle('active');
            mobileOverlay.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');
        }

        function closeMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const mobileOverlay = document.getElementById('mobileOverlay');
            sidebar.classList.remove('active');
            mobileOverlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Quick Actions Menu
        function toggleQuickActions() {
            const menu = document.getElementById('quickActionsMenu');
            menu.classList.toggle('hidden');
        }

        // Config Submenu
        function toggleConfigMenu() {
            const menu = document.getElementById('configMenu');
            const arrow = document.getElementById('configArrow');
            menu.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        }

        // Modal Functions
        function openDeleteModal(entityRoute, id, routeName, entityName) {
            const modal = document.getElementById('confirmDeleteModal');
            const form = document.getElementById('deleteEntityForm');
            const csrfInput = form.querySelector('input[name="csrf_token"]');
            const entityNameSpan = document.getElementById('entityName');
            // Définir l’action complète
            form.action = entityRoute + id;

            // Mettre à jour le token
            if (csrfTokens[routeName]) {
                csrfInput.value = csrfTokens[routeName];
            } else {
                console.error("CSRF token non trouvé pour la route :", routeName);
            }

            // Affiche le nom de l'entité à supprimer
            entityNameSpan.textContent = entityName;

            // Affichage du modal
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        // Event Listeners
        document.getElementById('mobileMenuBtn').addEventListener('click', toggleMobileMenu);
        document.getElementById('closeMobileMenu').addEventListener('click', closeMobileMenu);
        document.getElementById('mobileOverlay').addEventListener('click', closeMobileMenu);
        document.getElementById('quickActionsBtn').addEventListener('click', toggleQuickActions);

        // Close quick actions when clicking outside
        document.addEventListener('click', (e) => {
            const quickActionsBtn = document.getElementById('quickActionsBtn');
            const quickActionsMenu = document.getElementById('quickActionsMenu');
            if (!quickActionsBtn.contains(e.target) && !quickActionsMenu.contains(e.target)) {
                quickActionsMenu.classList.add('hidden');
            }
        });

        // Close modals with escape key or click outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal(modal.id);
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    if (!modal.classList.contains('hidden')) closeModal(modal.id);
                });
                closeMobileMenu();
                document.getElementById('quickActionsMenu').classList.add('hidden');
            }
        });

        // Responsive handling
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                closeMobileMenu();
            }
        });
    </script>
</body>
</html>