<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-construction-black">Détails du bâtiment</h3>
        <a href="/agent/buildings" class="btn-secondary px-4 py-2 rounded-lg">Retour à la liste</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <p class="text-sm font-medium text-gray-600">Nom</p>
            <p class="text-lg font-semibold text-construction-black">Résidence Victor Hugo</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Ville</p>
            <p class="text-lg font-semibold text-construction-black">Paris</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Propriétaire</p>
            <p class="text-lg font-semibold text-construction-black">Jean Dupont</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Agence</p>
            <p class="text-lg font-semibold text-construction-black">Agence Paris</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Type</p>
            <p class="text-lg font-semibold text-construction-black">Résidentiel</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Nombre d'étages</p>
            <p class="text-lg font-semibold text-construction-black">5</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Nombre de logements</p>
            <p class="text-lg font-semibold text-construction-black">24</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Statut</p>
            <p class="text-lg font-semibold text-construction-black">
                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-sm">Actif</span>
            </p>
        </div>
    </div>
    <div class="mt-6 flex justify-end space-x-3">
        <a href="/agent/buildings/edit/1" class="btn-primary px-4 py-2 rounded-lg">Modifier</a>
        <button onclick="openModal('confirmModal')" class="btn-danger px-4 py-2 rounded-lg">Supprimer</button>
    </div>
</div>
<div id="confirmModal" class="modal fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full">
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
            <p class="text-gray-700 mb-6">Êtes-vous sûr de vouloir supprimer ce bâtiment ?</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeModal('confirmModal')" class="btn-secondary px-4 py-2 rounded-lg">Annuler</button>
                <a href="/agent/buildings/delete/1" class="btn-danger px-4 py-2 rounded-lg">Supprimer</a>
            </div>
        </div>
    </div>
</div>