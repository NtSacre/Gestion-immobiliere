<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-construction-black">Détails du paiement</h3>
        <a href="/agent/payments" class="btn-secondary px-4 py-2 rounded-lg">Retour à la liste</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <p class="text-sm font-medium text-gray-600">Locataire</p>
            <p class="text-lg font-semibold text-construction-black">Sophie Durand</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Location</p>
            <p class="text-lg font-semibold text-construction-black">3B - Résidence Victor Hugo</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Montant (€)</p>
            <p class="text-lg font-semibold text-construction-black">1200</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Date de paiement</p>
            <p class="text-lg font-semibold text-construction-black">01/06/2025</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Statut</p>
            <p class="text-lg font-semibold text-construction-black">
                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-sm">Payé</span>
            </p>
        </div>
    </div>
    <div class="mt-6 flex justify-end space-x-3">
        <a href="/agent/payments/edit/1" class="btn-primary px-4 py-2 rounded-lg">Modifier</a>
        <button onclick="openModal('confirmModal')" class="btn-danger px-4 py-2 rounded-lg">Supprimer</button>
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
            <p class="text-gray-700 mb-6">Êtes-vous sûr de vouloir supprimer ce paiement ?</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeModal('confirmModal')" class="btn-secondary px-4 py-2 rounded-lg">Annuler</button>
                <a href="/agent/payments/delete/1" class="btn-danger px-4 py-2 rounded-lg">Supprimer</a>
            </div>
        </div>
    </div>
</div>