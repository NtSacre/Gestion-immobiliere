<?php
namespace App\Views\admin\apartments;
use App\Utils\Flash;
$msgFlash = new Flash();
$title = "Liste des appartements";
?>

<div class="container mx-auto px-4 py-8">
    <!-- Messages flash -->
    <?php if ($msgFlash->has('success')): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
            <p class="font-bold">Succès</p>
            <p><?= htmlspecialchars($msgFlash->get('success')); ?></p>
        </div>
    <?php endif; ?>
    <?php if ($msgFlash->has('error')): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
            <p class="font-bold">Erreur</p>
            <p><?= htmlspecialchars($msgFlash->get('error')); ?></p>
        </div>
    <?php endif; ?>

    <!-- Filtres et recherche -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-construction-black">Liste des bâtiments</h1>
        <?php if (in_array($role, ['superadmin', 'admin', 'agent'])): ?>
            <a href="/apartments/create" class="bg-construction-yellow text-construction-black px-4 py-2 rounded hover:bg-yellow-600 transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>Ajouter un appartement
            </a>
        <?php endif; ?>
    </div>
        <form id="filter-form" method="GET" action="/apartments" class="flex flex-col md:flex-row md:items-end gap-4">
            <!-- Recherche -->
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700">Rechercher</label>
                <input type="text" name="search" id="search" value="<?= htmlspecialchars($search ?? '') ?>" 
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent" 
                       placeholder="Numéro, nom du bâtiment ou ville">
            </div>
            <!-- Filtres par statut -->
            <div class="flex-1">
                <label for="statuses" class="block text-sm font-medium text-gray-700">Statut</label>
                <div class="mt-1 flex flex-wrap gap-4">
                    <?php 
                    $availableStatuses = ['disponible', 'loué', 'réservé', 'en_maintenance'];
                    foreach ($availableStatuses as $status): ?>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="statuses[]" value="<?= $status ?>" 
                                   <?= in_array($status, $statuses ?? []) ? 'checked' : '' ?> 
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            <span class="ml-2 capitalize"><?= htmlspecialchars($status) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Bouton de recherche -->
            <div>
                <button type="submit" class="bg-construction-yellow text-construction-black px-4 py-2 rounded hover:bg-yellow-600 transition-colors duration-200">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Tableau des appartements -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Numéro</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bâtiment</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Étage</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Superficie</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pièces</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loyer</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($apartments)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">Aucun appartement trouvé.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($apartments as $apartment): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <img src="<?= htmlspecialchars($imageCache[$apartment->getId()] ?? '/assets/images/apartments/default-apartment.jpg') ?>" 
                                     alt="Image de l’appartement <?= htmlspecialchars($apartment->getNumber()) ?>" 
                                     class="h-12 w-12 object-cover rounded">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($apartment->getNumber()) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $building = $apartment->building();
                                echo $building ? htmlspecialchars($building->getName()) : 'Inconnu';
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($apartment->getFloor()) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($apartment->getArea() ?? '-') ?> m²</td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($apartment->getRooms() ?? '-') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($apartment->getRentAmount() ? number_format($apartment->getRentAmount(), 2) . ' €' : '-') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?= $apartment->getStatus() === 'disponible' ? 'bg-green-100 text-green-800' : 
                                        ($apartment->getStatus() === 'loué' ? 'bg-red-100 text-red-800' : 
                                        ($apartment->getStatus() === 'réservé' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) ?>">
                                    <?= htmlspecialchars(ucfirst($apartment->getStatus())) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="/apartments/<?= $apartment->getId() ?>" 
                                   class="text-blue-600 hover:text-blue-800 mr-2">Voir</a>
                                <?php if ($apartment->getCanManage()): ?>
                                    <a href="/apartments/edit/<?= $apartment->getId() ?>" 
                                       class="text-yellow-600 hover:text-yellow-800 mr-2">Modifier</a>
                                    <a href="#" 
                                       onclick="openDeleteModal('/apartments/delete/', <?= $apartment->getId() ?>, 'delete', '<?= htmlspecialchars($apartment->getNumber()) ?>')" 
                                       class="text-red-600 hover:text-red-800">Supprimer</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="mt-6 flex justify-between items-center">
            <p class="text-sm text-gray-600">
                Affichage de <?= count($apartments) ?> appartement(s) sur <?= $totalApartments ?>
            </p>
            <div class="flex space-x-2">
                <?php if ($page > 1): ?>
                    <a href="/apartments?page=<?= $page - 1 ?>&search=<?= urlencode($search ?? '') ?>&<?= http_build_query(['statuses' => $statuses ?? []]) ?>" 
                       class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Précédent</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="/apartments?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>&<?= http_build_query(['statuses' => $statuses ?? []]) ?>" 
                       class="px-3 py-2 <?= $i === $page ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' ?> rounded hover:bg-blue-500 hover:text-white">
                       <?= $i ?>
                    </a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="/apartments?page=<?= $page + 1 ?>&search=<?= urlencode($search ?? '') ?>&<?= http_build_query(['statuses' => $statuses ?? []]) ?>" 
                       class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Suivant</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('filter-form');
    const searchInput = form.querySelector('input[name="search"]');
    const statusCheckboxes = form.querySelectorAll('input[name="statuses[]"]');

    // Débounce pour la recherche
    let debounceTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => {
                form.submit();
            }, 800); // Débounce de 800ms
        });
    }

    // Soumission lors du changement de statut
    if (statusCheckboxes) {
        statusCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                form.submit();
            });
        });
    }

    // Fonction pour ouvrir le modal de suppression
    window.openDeleteModal = function(baseUrl, id, action, number) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <h2 class="text-lg font-semibold mb-4">Confirmer la suppression</h2>
                <p class="mb-4">Êtes-vous sûr de vouloir supprimer l’appartement "${number}" ?</p>
                <form action="${baseUrl}${id}" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="_method" value="DELETE">
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="this.closest('.fixed').remove()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Annuler</button>
                        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Supprimer</button>
                    </div>
                </form>
            </div>
        `;
        document.body.appendChild(modal);
    };
});
</script>