<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-construction-black">Liste des paiements</h3>
        <a href="/agent/payments/create" class="btn-primary px-4 py-2 rounded-lg font-semibold hover:scale-105 transition-all">Ajouter un paiement</a>
    </div>
    <div class="overflow-x-auto">
        <table class="table-striped w-full text-left text-sm">
            <thead>
                <tr class="bg-gray-100 text-gray-700">
                    <th class="p-4 font-semibold">Locataire</th>
                    <th class="p-4 font-semibold">Location</th>
                    <th class="p-4 font-semibold">Montant (€)</th>
                    <th class="p-4 font-semibold">Date de paiement</th>
                    <th class="p-4 font-semibold">Statut</th>
                    <th class="p-4 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="p-4">Sophie Durand</td>
                    <td class="p-4">3B - Résidence Victor Hugo</td>
                    <td class="p-4">1200</td>
                    <td class="p-4">01/06/2025</td>
                    <td class="p-4"><span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Payé</span></td>
                    <td class="p-4">
                        <div class="flex space-x-2">
                            <a href="/agent/payments/1" class="text-blue-600 hover:underline">Voir</a>
                            <a href="/agent/payments/edit/1" class="text-yellow-600 hover:underline">Modifier</a>
                            <button onclick="openDeleteModal('/agent/payments/delete/', 2)" class="text-red-600 hover:underline">Supprimer</button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="p-4">Pierre Martin</td>
                    <td class="p-4">5A - Tour Eiffel</td>
                    <td class="p-4">1500</td>
                    <td class="p-4">15/05/2025</td>
                    <td class="p-4"><span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs">En attente</span></td>
                    <td class="p-4">
                        <div class="flex space-x-2">
                            <a href="/agent/payments/2" class="text-blue-600 hover:underline">Voir</a>
                            <a href="/agent/payments/edit/2" class="text-yellow-600 hover:underline">Modifier</a>
                            <button onclick="openDeleteModal('/agent/payments/delete/', 2)" class="text-red-600 hover:underline">Supprimer</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
