<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ImmoApp - Admin</title>
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
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <div id="mobileOverlay" class="fixed inset-0 overlay z-40 lg:hidden hidden"></div>
    <div class="flex h-screen">
        <button id="mobileMenuBtn" class="fixed top-4 left-4 z-50 lg:hidden bg-construction-yellow p-2 rounded-lg shadow-lg">
            <svg class="w-6 h-6 text-construction-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        <aside id="sidebar" class="mobile-menu fixed lg:static inset-y-0 left-0 z-50 w-64 bg-construction-black text-white flex-shrink-0 lg:transform-none">
            <button id="closeMobileMenu" class="absolute top-4 right-4 lg:hidden p-2 text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="p-6 h-full flex flex-col">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 gradient-yellow rounded-lg flex items-center justify-center">
                        <span class="text-construction-black font-bold text-xl">I</span>
                    </div>
                    <span class="text-xl font-bold">ImmoApp</span>
                </div>
                <nav class="space-y-2 flex-1">
                    <a href="/agent/dashboard" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg bg-construction-yellow text-construction-black font-semibold">
                        <span class="text-xl">📊</span><span>Dashboard</span>
                    </a>
                    <a href="/agent/buildings" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                        <span class="text-xl">🏢</span><span>Bâtiments</span>
                    </a>
                    <a href="/agent/apartments" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                        <span class="text-xl">🏠</span><span>Appartements</span>
                    </a>
                    <a href="/agent/leases" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                        <span class="text-xl">📄</span><span>Locations</span>
                    </a>
                    <a href="/agent/tenants" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                        <span class="text-xl">👤</span><span>Locataires</span>
                    </a>
                    <a href="/agent/owners" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                        <span class="text-xl">🏠</span><span>Propriétaires</span>
                    </a>
                    <a href="/agent/payments" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                        <span class="text-xl">💸</span><span>Payments</span>
                    </a>
                </nav>
                <div class="p-4 rounded-lg bg-construction-gray">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 gradient-yellow rounded-full flex items-center justify-center">
                            <span class="text-construction-black font-bold">JD</span>
                        </div>
                        <div>
                            <div class="font-semibold text-sm">John Doe</div>
                            <div class="text-gray-400 text-xs">Agent</div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
        <main class="flex-1 overflow-auto lg:ml-0">
            <header class="bg-white shadow-sm p-4 lg:p-6">
                <div class="flex justify-between items-center">
                    <div class="ml-12 lg:ml-0">
                        <h1 class="text-xl lg:text-2xl font-bold text-construction-black">Dashboard Admin Agence</h1>
                        <p class="text-sm lg:text-base text-gray-600">Vue d'ensemble de votre agence</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button class="relative p-2 text-gray-600 hover:text-construction-yellow transition-colors">
                            <span class="text-lg lg:text-xl">🔔</span>
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                        </button>
                        <a href="/agency/buildings/create" class="btn-primary px-4 py-2 rounded-lg font-semibold hover:scale-105 transition-all">Ajouter un bâtiment</a>
                    </div>
                </div>
            </header>
            <div class="p-4 lg:p-6 space-y-6">
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
        document.getElementById('mobileMenuBtn').addEventListener('click', toggleMobileMenu);
        document.getElementById('closeMobileMenu').addEventListener('click', closeMobileMenu);
        document.getElementById('mobileOverlay').addEventListener('click', closeMobileMenu);
        window.addEventListener('resize', () => { if (window.innerWidth >= 1024) closeMobileMenu(); });
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
            }
        });
    </script>
</body>
</html>