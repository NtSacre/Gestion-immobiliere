<?php
namespace App\Views\admin\buildings;

use App\Utils\Flash;
use App\Models\BuildingType;

$msgFlash = new Flash();
$csrfToken = $helpers->generateCsrfToken('buildings.delete');
?>

<div class="bg-white rounded-lg shadow-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-construction-black">Liste des bâtiments</h1>
        <?php if (in_array($role, ['superadmin', 'admin', 'agent'])): ?>
            <a href="/buildings/create" class="bg-construction-yellow text-construction-black px-4 py-2 rounded hover:bg-yellow-600 transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>Ajouter un bâtiment
            </a>
        <?php endif; ?>
    </div>

    <!-- Messages flash -->
    <?php if ($flash && $flash->get('success') !== null): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <?= htmlspecialchars($flash->get('success')) ?>
        </div>
    <?php endif; ?>
    <?php if ($flash && $flash->get('error') !== null): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= htmlspecialchars($flash->get('error')) ?>
        </div>
    <?php endif; ?>

    <!-- Filtres -->
    <div class="bg-gray-50 p-4 rounded-lg mb-6">
        <form id="searching" method="GET" action="/buildings" class="flex flex-col lg:flex-row gap-4 items-end">
            <div class="flex-1 min-w-0">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="<?= htmlspecialchars($search ?? '') ?>"
                        placeholder="Nom, ville, pays..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow"
                    >
                </div>
            </div>

            <div class="min-w-0 lg:w-64">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Filtrer par statut</label>
                <select
                    id="status"
                    name="status[]"
                    multiple
                    class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow bg-white"
                >
                    <option value="" <?= empty($selectedStatuses) ? 'selected' : '' ?>>Tous les statuts</option>
                    <?php foreach ($allowedStatuses as $status): ?>
                        <option value="<?= htmlspecialchars($status) ?>" <?= in_array($status, $selectedStatuses) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($status)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-construction-yellow text-construction-black px-4 py-2 rounded-lg hover:bg-yellow-600 flex items-center">
                    <i class="fas fa-filter mr-2"></i>Filtrer
                </button>
                <a href="/buildings" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 flex items-center">
                    <i class="fas fa-times mr-2"></i>Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <!-- Résultats -->
    <div class="mb-4 text-sm text-gray-600">
        <?php if (!empty($buildings)): ?>
            Affichage de <?= count($buildings) ?> bâtiment(s) sur <?= htmlspecialchars($totalBuildings) ?> au total.
        <?php else: ?>
            Aucun bâtiment à afficher.
        <?php endif; ?>
    </div>

    <!-- Tableau -->
    <div class="overflow-x-auto bg-white">
        <table class="w-full table-auto">
            <thead>
                <tr class="bg-construction-black text-white">
                    <th class="p-3 text-center font-semibold">Image</th>
                    <th class="flex items-center justify-between p-3">
                        <span class="font-semibold">Nom</span>
                        <i class="fas fa-sort"></i>
                    </th>
                    <th class="flex items-center justify-between p-3">
                        <span class="font-semibold">Ville</span>
                        <i class="fas fa-sort"></i>
                    </th>
                    <th class="flex items-center justify-between p-3">
                        <span class="font-semibold">Statut</span>
                        <i class="fas fa-sort"></i>
                    </th>
                    <th class="flex items-center justify-between p-3">
                        <span class="font-semibold">Type</span>
                        <i class="fas fa-sort"></i>
                    </th>
                    <th class="flex items-center justify-between p-3">
                        <span class="font-semibold">Prix</span>
                        <i class="fas fa-sort"></i>
                    </th>
                    <th class="flex items-center justify-between p-3">
                        <span class="font-semibold">Créé le</span>
                        <i class="fas fa-sort"></i>
                    </th>
                    <th class="p-3 text-center font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($buildings)): ?>
                    <tr>
                        <td colspan="8" class="p-8 text-center text-gray-500">
                            <i class="fas fa-building text-4xl mb-2 block"></i>
                            <p class="text-lg font-semibold">Aucun projet trouvé</p>
                            <p class="text-sm">Essayez de modifier vos critères de recherche</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($buildings as $building): ?>
                        <?php
                        $images = \App\Models\Images::findByEntity('building', $building->getId());
                        $firstImage = !empty($images) ? $images[0] : null;
                        ?>
                        <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors duration-150">
                            <td class="p-3">
                                <?php if ($firstImage): ?>
                                    <img src="/assets/<?= htmlspecialchars($firstImage->getPath()) ?>" alt="<?= htmlspecialchars($firstImage->getAltText() ?? $building->getName()) ?>" class="w-16 h-16 object-cover rounded">
                                <?php else: ?>
                                    <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center text-gray-500">Aucune image</div>
                                <?php endif; ?>
                            </td>
                            <td class="p-3">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-construction-yellow rounded-full flex items-center justify-center text-construction-black font-semibold mr-3">
                                        <?= strtoupper(substr($building->getName() ?? 'B', 0, 1)) ?>
                                    </div>
                                    <span class="font-medium"><?= htmlspecialchars($building->getName() ?? 'N/A') ?></span>
                                </div>
                            </td>
                            <td class="p-3"><?= htmlspecialchars($building->getCity() ?? 'N/A') ?></td>
                            <td class="p-3">
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full 
                                    <?= match($building->getStatus()) {
                                        'disponible' => 'bg-green-100 text-green-800',
                                        'vendu' => 'bg-red-100 text-red-800',
                                        'en_construction' => 'bg-yellow-100 text-yellow-800',
                                        'en_renovation' => 'bg-orange-100 text-orange-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    } ?>">
                                    <?= htmlspecialchars(ucfirst($building->getStatus() ?? 'Inconnu')) ?>
                                </span>
                            </td>
                            <td class="p-3">
                                <?php
                                    $buildingType = BuildingType::find($building->getTypeId());
                                    echo htmlspecialchars($buildingType ? $buildingType->getName() : 'N/A');
                                ?>
                            </td>
                            <td class="p-3"><?= number_format($building->getPrice() ?? 0, 2, ',', ' ') ?> €</td>
                            <td class="p-3 text-sm text-gray-600"><?= date('d/m/Y', strtotime($building->getCreatedAt())) ?></td>
                            <td class="p-3">
                                <div class="flex justify-center space-x-1">
                                    <a href="/buildings/<?= $building->getId() ?>" class="inline-flex items-center px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200" title="Voir les détails">
                                        <i class="fas fa-eye mr-1"></i>Voir
                                    </a>
                                    <?php if (in_array($role, ['superadmin', 'admin', 'agent'])): ?>
                                        <a href="/buildings/edit/<?= $building->getId() ?>" class="inline-flex items-center px-2 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200" title="Modifier le bâtiment">
                                            <i class="fas fa-edit mr-1"></i>Modifier
                                        </a>
                                        <button onclick="openDeleteModal('/buildings/delete/<?= htmlspecialchars($building->getId()) ?>', '<?= htmlspecialchars($building->getName()) ?>', '<?= htmlspecialchars($csrfToken) ?>')" 
                                            class="inline-flex items-center px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200" title="Supprimer le bâtiment">
                                            <i class="fas fa-trash mr-1"></i>Supprimer
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-600">Page <?= $page ?> sur <?= $totalPages ?></div>
            <nav class="flex space-x-1" aria-label="Pagination">
                <?php if ($page > 1): ?>
                    <a href="/buildings?page=1&search=<?= urlencode($search ?? '') ?><?= !empty($selectedStatuses) ? '&' . http_build_query(['status' => $selectedStatuses]) : '' ?>" 
                       class="px-3 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300" title="Première page">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="/buildings?page=<?= $page - 1 ?>&search=<?= urlencode($search ?? '') ?><?= !empty($selectedStatuses) ? '&' . http_build_query(['status' => $selectedStatuses]) : '' ?>" 
                       class="px-3 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300" title="Page précédente">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php endif; ?>
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="/buildings?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?><?= !empty($selectedStatuses) ? '&' . http_build_query(['status' => $selectedStatuses]) : '' ?>" 
                       class="px-3 py-2 text-sm rounded <?= $i === $page ? 'bg-construction-yellow text-construction-black font-semibold' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="/buildings?page=<?= $page + 1 ?>&search=<?= urlencode($search ?? '') ?><?= !empty($selectedStatuses) ? '&' . http_build_query(['status' => $selectedStatuses]) : '' ?>" 
                       class="px-3 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300" title="Page suivante">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="/buildings?page=<?= $totalPages ?>&search=<?= urlencode($search ?? '') ?><?= !empty($selectedStatuses) ? '&' . http_build_query(['status' => $selectedStatuses]) : '' ?>" 
                       class="px-3 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300" title="Dernière page">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('searching');
    const searchInput = form.querySelector('input[name="search"]');
    const statusSelect = form.querySelector('select[name="status[]"]');

    let debounceTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => form.submit(), 800);
        });
    }
    if (statusSelect) {
        statusSelect.addEventListener('change', () => form.submit());
    }

    if (statusSelect) {
        statusSelect.addEventListener('click', (e) => {
            if (e.target.value === '') {
                Array.from(statusSelect.options).forEach(option => {
                    if (option.value !== '') option.selected = false;
                });
            } else {
                statusSelect.options[0].selected = false;
            }
        });
    }
});

function openDeleteModal(url, buildingName, csrfToken) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le bâtiment "${buildingName}" ?`)) {
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': csrfToken
            },
            body: 'csrf_token=' + encodeURIComponent(csrfToken)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Bâtiment supprimé avec succès.');
                window.location.href = '/buildings';
            } else {
                alert('Erreur : ' + (data.error || 'Suppression échouée.'));
            }
        })
        .catch(error => {
            console.error('Erreur lors de la suppression :', error);
            alert('Une erreur est survenue lors de la suppression.');
        });
    }
}
</script>