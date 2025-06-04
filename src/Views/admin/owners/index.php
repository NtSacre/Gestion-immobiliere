<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-construction-black">Liste des propriétaires</h3>
        <a href="/agent/owners/create" class="btn-primary px-4 py-2 rounded-lg font-semibold hover:scale-105 transition-all">Ajouter un propriétaire</a>
    </div>
    <div class="overflow-x-auto">
        <table class="table-striped w-full text-left text-sm">
            <thead>
                <tr class="bg-gray-100 text-gray-700">
                    <th class="p-4 font-semibold">Nom</th>
                    <th class="p-4 font-semibold">Email</th>
                    <th class="p-4 font-semibold">Téléphone</th>
                    <th class="p-4 font-semibold">Statut</th>
                    <th class="p-4 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="p-4">Jean Dupont</td>
                    <td class="p-4">jean.dupont@email.com</td>
                    <td class="p-4">+33 6 23 45 67 89</td>
                    <td class="p-4"><span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Actif</span></td>
                    <td class="p-4">
                        <div class="flex space-x-2">
                            <a href="/agent/owners/1" class="text-blue-600 hover:underline">Voir</a>
                            <a href="/agent/owners/edit/1" class="text-yellow-600 hover:underline">Modifier</a>
                            <button onclick="openModal('confirmDeleteModal')" class="text-red-600 hover:underline">Supprimer</button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="p-4">Marie Leclerc</td>
                    <td class="p-4">marie.leclerc@email.com</td>
                    <td class="p-4">+33 6 87 65 43 21</td>
                    <td class="p-4"><span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">Inactif</span></td>
                    <td class="p-4">
                        <div class="flex space-x-2">
                            <a href="/agent/owners/2" class="text-blue-600 hover:underline">Voir</a>
                            <a href="/agent/owners/edit/2" class="text-yellow-600 hover:underline">Modifier</a>
                            <button onclick="openModal('confirmDeleteModal')" class="text-red-600 hover:underline">Supprimer</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
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
            <p class="text-gray-700 mb-6">Êtes-vous sûr de vouloir supprimer ce propriétaire ?</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeModal('confirmDeleteModal')" class="btn-secondary px-4 py-2 rounded-lg">Annuler</button>
                <a href="/agent/owners/delete/1" class="btn-danger px-4 py-2 rounded-lg">Supprimer</a>
            </div>
        </div>
    </div>
</div>