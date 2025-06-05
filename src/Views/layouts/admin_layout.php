<?php
error_log("Rendu du layout admin pour rôle: " . ($_SESSION['role'] ?? 'guest'));
$flash = new \App\Utils\Flash(); // Instancier Flash
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ImmoApp - <?php echo htmlspecialchars($title ?? 'Tableau de bord'); ?></title>
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
        .notification-dot { animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .search-container { position: relative; }
        .search-results { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #d1d5db; border-radius: 0.5rem; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); max-height: 300px; overflow-y: auto; z-index: 1000; }
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
                    <?php
                    $auth = new \App\Utils\Auth();
                    $role = $auth->user()['role'] ?? 'guest';
                    $activeRoute = $_SERVER['REQUEST_URI'];
                    ?>
                    <!-- Section Principale -->
                    <div class="sidebar-section">
                        <a href="/<?php echo $role; ?>/dashboard" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php echo strpos($activeRoute, '/dashboard') !== false ? 'bg-construction-yellow text-construction-black font-semibold' : 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">📊</span><span class="text-sm">Dashboard</span>
                        </a>
                    </div>

                    <!-- Section Gestion des Biens -->
                    <?php if (in_array($role, ['superadmin', 'admin', 'agent', 'proprietaire', 'acheteur'])) : ?>
                    <div class="sidebar-section">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-4">Biens</h3>
                        <a href="/buildings" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php echo strpos($activeRoute, '/buildings') !== false ? 'bg-construction-yellow text-construction-black font-semibold' : 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">🏢</span><span class="text-sm"><?php echo $role === 'proprietaire' ? 'Mes propriétés' : ($role === 'acheteur' ? 'À vendre' : 'Bâtiments'); ?></span>
                        </a>
                        <a href="/apartments" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php echo strpos($activeRoute, '/apartments') !== false ? 'bg-construction-yellow text-construction-black font-semibold' : 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">🏠</span><span class="text-sm"><?php echo $role === 'proprietaire' ? 'Mes appartements' : ($role === 'locataire' ? 'Mon appartement' : ($role === 'acheteur' ? 'À vendre' : 'Appartements')); ?></span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Section Gestion Client -->
                    <?php if (in_array($role, ['superadmin', 'admin', 'agent'])) : ?>
                    <div class="sidebar-section">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-4">Clients</h3>
                        <a href="/clients" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php echo strpos($activeRoute, '/clients') !== false ? 'bg-construction-yellow text-construction-black font-semibold' : 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">👥</span><span class="text-sm">Tous les clients</span>
                        </a>
                        <?php if (in_array($role, ['superadmin', 'admin'])) : ?>
                        <a href="/owners" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php echo strpos($activeRoute, '/owners') !== false ? 'bg-construction-yellow text-construction-black font-semibold' : 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">🏠</span><span class="text-sm">Propriétaires</span>
                        </a>
                        <a href="/tenants" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php echo strpos($activeRoute, '/tenants') !== false ? 'bg-construction-yellow text-construction-black font-semibold' : 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">👤</span><span class="text-sm">Locataires</span>
                        </a>
                        <a href="/buyers" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php echo strpos($activeRoute, '/buyers') !== false ? 'bg-construction-yellow text-construction-black font-semibold' : 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">🛒</span><span class="text-sm">Acheteurs</span>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Section Transactions -->
                    <?php if (in_array($role, ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire'])) : ?>
                    <div class="sidebar-section">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-4">Transactions</h3>
                        <a href="/leases" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php echo strpos($activeRoute, '/leases') !== false ? 'bg-construction-yellow text-construction-black font-semibold' : 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">📜</span><span class="text-sm"><?php echo in_array($role, ['proprietaire', 'locataire']) ? 'Mes baux' : 'Baux'; ?></span>
                        </a>
                        <a href="/payments" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php echo strpos($activeRoute, '/payments') !== false ? 'bg-construction-yellow text-construction-black font-semibold' : 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">💸</span><span class="text-sm">Paiements</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Section Administration -->
                    <?php if ($role === 'superadmin') : ?>
                    <div class="sidebar-section">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-4">Administration</h3>
                        <a href="/agencies" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php echo strpos($activeRoute, '/agencies') !== false ? 'bg-construction-yellow text-construction-black font-semibold' : 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">🏢</span><span class="text-sm">Agences</span>
                        </a>
                        <a href="/users" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php echo strpos($activeRoute, '/users') !== false ? 'bg-construction-yellow text-construction-black font-semibold' : 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">👤</span><span class="text-sm">Utilisateurs</span>
                        </a>
                        <a href="/roles" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php echo strpos($activeRoute, '/roles') !== false ? 'bg-construction-yellow text-construction-black font-semibold' : 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">👥</span><span class="text-sm">Rôles</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Section Configuration -->
                    <?php if ($role === 'superadmin') : ?>
                    <div class="sidebar-section">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-4">Configuration</h3>
                        <a href="/building-types" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php echo strpos($activeRoute, '/building-types') !== false ? 'bg-construction-yellow text-construction-black font-semibold' : 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">🏗️</span><span class="text-sm">Types bâtiments</span>
                        </a>
                        <a href="/apartment-types" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php echo strpos($activeRoute, '/apartment-types') !== false ? 'bg-construction-yellow text-construction-black font-semibold' : 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">🏠</span><span class="text-sm">Types appartements</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Section Outils -->
                    <?php if ($auth->check()) : ?>
                    <div class="sidebar-section">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-4">Outils</h3>
                        <a href="/leases/download" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php echo strpos($activeRoute, '/leases/download') !== false ? 'bg-construction-yellow text-construction-black font-semibold' : 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">📥</span><span class="text-sm">Télécharger PDF</span>
                        </a>
                        <?php if (in_array($role, ['superadmin', 'admin'])) : ?>
                        <a href="/audit-log" class="sidebar-item flex items-center space-x-3 px-4 py-2.5 rounded-lg <?php echo strpos($activeRoute, '/audit-log') !== false ? 'bg-construction-yellow text-construction-black font-semibold' : 'text-gray-300 hover:text-white'; ?>">
                            <span class="text-base">📋</span><span class="text-sm">Audit</span>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </nav>

                <!-- User Profile Section -->
                <?php if ($auth->check()) : ?>
                <div class="border-t border-gray-700 pt-4 mt-4">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 gradient-yellow rounded-full flex items-center justify-center">
                            <span class="text-construction-black font-bold text-sm"><?php echo strtoupper(substr($auth->user()['first_name'], 0, 1) . substr($auth->user()['last_name'], 0, 1)); ?></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm text-white truncate"><?php echo htmlspecialchars($auth->user()['first_name'] . ' ' . $auth->user()['last_name']); ?></div>
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
                            <h1 class="text-xl lg:text-2xl font-bold text-construction-black"><?php echo htmlspecialchars($title ?? 'Tableau de bord'); ?></h1>
                            <p class="text-sm lg:text-base text-gray-600">
                                <?php
                                if ($role === 'superadmin') {
                                    echo 'Gestion globale du système';
                                } elseif ($role === 'admin') {
                                    echo 'Vue d’ensemble de votre agence';
                                } elseif ($role === 'agent') {
                                    echo 'Gestion de vos clients et biens';
                                } elseif ($role === 'proprietaire') {
                                    echo 'Vue d’ensemble de vos propriétés';
                                } elseif ($role === 'locataire') {
                                    echo 'Gestion de votre location';
                                } elseif ($role === 'acheteur') {
                                    echo 'Biens disponibles à l’achat';
                                } else {
                                    echo 'Bienvenue sur ImmoApp';
                                }
                                ?>
                            </p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <!-- Search Bar -->
                            <div class="search-container hidden lg:block">
                                <div class="relative">
                                    <input type="text" placeholder="Rechercher un bien, client..." class="input-focus w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none">
                                    <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                                </div>
                            </div>
                            <!-- Notifications -->
                            <button class="relative p-2 text-gray-600 hover:text-construction-yellow transition-colors">
                                <span class="text-lg lg:text-xl">🔔</span>
                                <span class="notification-dot absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                            </button>
                            <!-- Quick Actions Menu -->
                            <div class="relative">
                                <button id="quickActionsBtn" class="btn-primary px-4 py-2 rounded-lg font-medium hover:scale-105 transition-all flex items-center space-x-2">
                                    <span>Actions</span>
                                    <span class="text-sm">⚡</span>
                                </button>
                                <div id="quickActionsMenu" class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50 hidden">
                                    <?php if (in_array($role, ['superadmin', 'admin'])) : ?>
                                    <a href="/buildings/create" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                                        <span class="mr-2">🏢</span>Nouveau bâtiment
                                    </a>
                                    <a href="/apartments/create" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                                        <span class="mr-2">🏠</span>Nouvel appartement
                                    </a>
                                    <a href="/users/create" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                                        <span class="mr-2">👤</span>Nouvel utilisateur
                                    </a>
                                    <?php endif; ?>
                                    <?php if (in_array($role, ['superadmin', 'admin', 'agent'])) : ?>
                                    <a href="/clients/create" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                                        <span class="mr-2">👥</span>Nouveau client
                                    </a>
                                    <a href="/leases/create" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                                        <span class="mr-2">📜</span>Nouveau bail
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($auth->check()) : ?>
                                    <div class="border-t border-gray-100 my-2"></div>
                                    <a href="/reports" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                                        <span class="mr-2">📊</span>Rapports
                                    </a>
                                    <a href="/export" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                                        <span class="mr-2">📤</span>Exporter
                                    </a>
                                    <?php endif; ?>
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

    <!-- Modal de confirmation de suppression générale -->
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
                <p class="text-gray-700 mb-6">Êtes-vous sûr de vouloir supprimer cet élément ?</p>
                <form id="deleteEntityForm" method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(\App\Utils\generateCsrfToken()); ?>">
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

        // Modal Functions
        function openDeleteModal(entityRoute, id) {
            const modal = document.getElementById('confirmDeleteModal');
            const form = document.getElementById('deleteEntityForm');
            form.action = entityRoute + id;
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