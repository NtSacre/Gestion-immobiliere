<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-construction-black">Liste des bâtiments</h3>
        <a href="/agent/buildings/create" class="btn-primary px-4 py-2 rounded-lg font-semibold hover:scale-105 transition-all">Ajouter un bâtiment</a>
    </div>
    <div class="overflow-x-auto">
        <table class="table-striped w-full text-left text-sm">
            <thead>
                <tr class="bg-gray-100 text-gray-700">
                    <th class="p-4 font-semibold">Nom</th>
                    <th class="p-4 font-semibold">Ville</th>
                    <th class="p-4 font-semibold">Propriétaire</th>
                    <th class="p-4 font-semibold">Statut</th>
                    <th class="p-4 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="p-4">Résidence Victor Hugo</td>
                    <td class="p-4">Paris</td>
                    <td class="p-4">Jean Dupont</td>
                    <td class="p-4"><span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Actif</span></td>
                    <td class="p-4">
                        <div class="flex space-x-2">
                            <a href="/agent/buildings/1" class="text-blue-600 hover:underline">Voir</a>
                            <a href="/agent/buildings/edit/1" class="text-yellow-600 hover:underline">Modifier</a>
                            <button onclick="openDeleteModal('/agent/buildings/delete/', 1)" class="text-red-600 hover:underline">Supprimer</button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="p-4">Tour Eiffel</td>
                    <td class="p-4">Lyon</td>
                    <td class="p-4">Marie Martin</td>
                    <td class="p-4"><span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">Inactif</span></td>
                    <td class="p-4">
                        <div class="flex space-x-2">
                            <a href="/agent/buildings/2" class="text-blue-600 hover:underline">Voir</a>
                            <a href="/agent/buildings/edit/2" class="text-yellow-600 hover:underline">Modifier</a>
                            <button onclick="openDeleteModal('/agent/buildings/delete/', 2)" class="text-red-600 hover:underline">Supprimer</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>