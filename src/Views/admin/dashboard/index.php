<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
    <div class="card-hover bg-white p-6 rounded-xl shadow-lg border-l-4 border-construction-yellow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Bâtiments gérés</p>
                <p class="text-3xl font-bold text-construction-black">10</p>
                <p class="text-green-600 text-sm font-medium">+2 ce mois</p>
            </div>
            <div class="w-12 h-12 gradient-yellow rounded-lg flex items-center justify-center">
                <span class="text-construction-black font-bold text-xl">🏢</span>
            </div>
        </div>
    </div>
    <div class="card-hover bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Appartements disponibles</p>
                <p class="text-3xl font-bold text-construction-black">50</p>
                <p class="text-green-600 text-sm font-medium">+5 ce mois</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <span class="text-green-600 font-bold text-xl">🏠</span>
            </div>
        </div>
    </div>
    <div class="card-hover bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Locations actives</p>
                <p class="text-3xl font-bold text-construction-black">20</p>
                <p class="text-blue-600 text-sm font-medium">+3 ce mois</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <span class="text-blue-600 font-bold text-xl">📄</span>
            </div>
        </div>
    </div>
    <div class="card-hover bg-white p-6 rounded-xl shadow-lg border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Locataires</p>
                <p class="text-3xl font-bold text-construction-black">15</p>
                <p class="text-purple-600 text-sm font-medium">+1 ce mois</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <span class="text-purple-600 font-bold text-xl">👤</span>
            </div>
        </div>
    </div>
    <div class="card-hover bg-white p-6 rounded-xl shadow-lg border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Propriétaires</p>
                <p class="text-3xl font-bold text-construction-black">8</p>
                <p class="text-red-600 text-sm font-medium">+1 ce mois</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <span class="text-red-600 font-bold text-xl">🏠</span>
            </div>
        </div>
    </div>
</div>
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 bg-white p-6 rounded-xl shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-construction-black">Activité récente</h3>
            <a href="#" class="text-construction-yellow font-semibold hover:underline">Voir tout</a>
        </div>
        <div class="space-y-4">
            <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <span class="text-green-600 font-bold text-base">🏢</span>
                </div>
                <div>
                    <p class="font-semibold text-gray-800 text-base">Nouveau bâtiment ajouté</p>
                    <p class="text-sm text-gray-600">Résidence Victor Hugo, Paris</p>
                    <p class="text-xs text-gray-500">Il y a 1 jour</p>
                </div>
            </div>
            <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 font-bold text-base">📄</span>
                </div>
                <div>
                    <p class="font-semibold text-gray-800 text-base">Contrat signé</p>
                    <p class="text-sm text-gray-600">Apt 3B, Rue de la Paix</p>
                    <p class="text-xs text-gray-500">Il y a 2 jours</p>
                </div>
            </div>
            <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                    <span class="text-purple-600 font-bold text-base">👤</span>
                </div>
                <div>
                    <p class="font-semibold text-gray-800 text-base">Locataire ajouté</p>
                    <p class="text-sm text-gray-600">Sophie Durand</p>
                    <p class="text-xs text-gray-500">Il y a 3 jours</p>
                </div>
            </div>
        </div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <h3 class="text-xl font-bold text-construction-black mb-6">Actions rapides</h3>
        <div class="space-y-3">
            <a href="/agent/buildings/create" class="w-full btn-primary p-3 rounded-lg font-semibold hover:scale-105 transition-all flex items-center justify-center space-x-2">
                <span>🏢</span><span>Ajouter un bâtiment</span>
            </a>
            <a href="/agency/apartments" class="w-full btn-secondary p-3 rounded-lg font-semibold hover:bg-gray-200 transition-all flex items-center justify-center space-x-2">
                <span>🏠</span><span>Gérer les appartements</span>
            </a>
            <a href="/agency/leases/create" class="w-full btn-secondary p-3 rounded-lg font-semibold hover:bg-gray-200 transition-all flex items-center justify-center space-x-2">
                <span>📄</span><span>Créer une location</span>
            </a>
            <a href="/agency/leases" class="w-full btn-secondary p-3 rounded-lg font-semibold hover:bg-gray-200 transition-all flex items-center justify-center space-x-2">
                <span>📥</span><span>Télécharger un contrat</span>
            </a>
        </div>
        <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <h4 class="font-semibold text-red-800 mb-2">Tâches urgentes</h4>
            <div class="space-y-2 text-sm">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                    <span class="text-red-700">1 contrat à valider</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                    <span class="text-orange-700">2 appartements à inspecter</span>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="confirmModal" class="modal fixed inset-0 z-50 hidden flex items-center justify-center p-4">
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
            <p class="text-gray-700 mb-6">Êtes-vous sûr de vouloir supprimer cette notification ?</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeModal('confirmModal')" class="btn-secondary px-4 py-2 rounded-lg">Annuler</button>
                <button class="btn-danger px-4 py-2 rounded-lg">Supprimer</button>
            </div>
        </div>
    </div>
</div>