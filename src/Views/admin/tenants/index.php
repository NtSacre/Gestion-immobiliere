<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-construction-black">Liste des locataires</h3>
        <a href="/agent/tenants/create" class="btn-primary px-4 py-2 rounded-lg font-semibold hover:scale-105 transition-all">Ajouter un locataire</a>
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
                    <td class="p-4">Sophie Durand</td>
                    <td class="p-4">sophie.durand@email.com</td>
                    <td class="p-4">+33 6 12 34 56 78</td>
                    <td class="p-4"><span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Actif</span></td>
                    <td class="p-4">
                        <div class="flex space-x-2">
                            <a href="/agent/tenants/1" class="text-blue-600 hover:underline">Voir</a>
                            <a href="/agent/tenants/edit/1" class="text-yellow-600 hover:underline">Modifier</a>
                            <button onclick="openDeleteModal('/agent/tenants/delete/', 2)" class="text-red-600 hover:underline">Supprimer</button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="p-4">Pierre Martin</td>
                    <td class="p-4">pierre.martin@email.com</td>
                    <td class="p-4">+33 6 98 76 54 32</td>
                    <td class="p-4"><span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">Inactif</span></td>
                    <td class="p-4">
                        <div class="flex space-x-2">
                            <a href="/agent/tenants/2" class="text-blue-600 hover:underline">Voir</a>
                            <a href="/agent/tenants/edit/2" class="text-yellow-600 hover:underline">Modifier</a>
                            <button onclick="openDeleteModal('/agent/tenants/delete/', 2)" class="text-red-600 hover:underline">Supprimer</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
