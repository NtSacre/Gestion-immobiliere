<?php
namespace App\Views\admin\buildings;
use App\Utils\Flash;
$msgFlash = new Flash();
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
    <?php if ($flash = $msgFlash->get('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <?= htmlspecialchars($flash) ?>
        </div>
    <?php endif; ?>
    <?php if ($flash = $msgFlash->get('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <div><?= htmlspecialchars($flash) ?></div>
        </div>
    <?php endif; ?>

    <!-- Filtres améliorés -->
    <div class="bg-gray-50 p-4 rounded-lg mb-6">
        <form id="filter-form" method="GET" class="flex flex-col lg:flex-row gap-4 items-end">
            <!-- Barre de recherche -->
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
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                    >
                </div>
            </div>

            <!-- Filtre par statut amélioré -->
            <div class="min-w-0 lg:w-64">
                <label for="statuses" class="block text-sm font-medium text-gray-700 mb-1">Filtrer par statut</label>
                <div class="relative">
                    <select
                        id="statuses"
                        name="statuses[]"
                        multiple
                        class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent bg-white"
                        size="1"
                    >
                        <option value="" <?= empty($selectedStatuses) ? 'selected' : '' ?>>Tous les statuts</option>
                        <?php foreach ($allowedStatuses as $status): ?>
                            <option
                                value="<?= htmlspecialchars($status) ?>"
                                <?= in_array($status, $selectedStatuses ?? []) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars(ucfirst($status)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex gap-2">
                <button
                    type="submit"
                    class="bg-construction-yellow text-construction-black px-4 py-2 rounded-lg hover:bg-yellow-600 transition-colors duration-200 flex items-center"
                >
                    <i class="fas fa-filter mr-2"></i>Filtrer
                </button>
                <a
                    href="/buildings"
                    class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 flex items-center"
                >
                    <i class="fas fa-times mr-2"></i>Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <!-- Informations sur les résultats -->
    <div class="mb-4 text-sm text-gray-600">
        <?php if (!empty($buildings)): ?>
            Affichage de <?= count($buildings) ?> bâtiment(s) sur <?= $totalBuildings ?> au total.
        <?php endif; ?>
    </div>

    <!-- Tableau des bâtiments amélioré -->
    <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
        <table class="w-full table-auto">
            <thead>
                <tr class="bg-construction-black text-white">
                    <th class="p-3 text-left font-semibold">Nom</th>
                    <th class="p-3 text-left font-semibold">Ville</th>
                    <th class="p-3 text-left font-semibold">Statut</th>
                    <th class="p-3 text-left font-semibold">Type</th>
                    <th class="p-3 text-left font-semibold">Créé le</th>
                    <th class="p-3 text-center font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($buildings)): ?>
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">
                            <i class="fas fa-building text-4xl mb-2 block"></i>
                            <p class="text-lg font-medium">Aucun bâtiment trouvé</p>
                            <p class="text-sm">Essayez de modifier vos critères de recherche</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($buildings as $building): ?>
                        <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors duration-150">
                            <td class="p-3">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-construction-yellow rounded-full flex items-center justify-center text-construction-black font-semibold mr-3">
                                        <?= strtoupper(substr($building['data']->getName() ?? 'B', 0, 1)) ?>
                                    </div>
                                    <span class="font-medium"><?= htmlspecialchars($building['data']->getName() ?? 'N/A') ?></span>
                                </div>
                            </td>
                            <td class="p-3">
                                <?php $city = $building['data']->getCity(); ?>
                                <?= $city ? htmlspecialchars($city) : '<span class="text-gray-400 italic">Non défini</span>' ?>
                            </td>
                            <td class="p-3">
                                <?php $status = $building['data']->getStatus(); ?>
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full 
                                    <?= match($status) {
                                        'disponible' => 'bg-green-100 text-green-800',
                                        'vendu' => 'bg-red-100 text-red-800',
                                        'en_construction' => 'bg-yellow-100 text-yellow-800',
                                        'en_renovation' => 'bg-blue-100 text-blue-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    } ?>">
                                    <?= htmlspecialchars(ucfirst($status ?? 'Inconnu')) ?>
                                </span>
                            </td>
                            <td class="p-3">
                                <?php 
                                $type = \App\Models\BuildingType::find($building['data']->getTypeId());
                                $typeName = $type ? $type->getName() : 'Inconnu';
                                ?>
                                <?= htmlspecialchars(ucfirst($typeName)) ?>
                            </td>
                            <td class="p-3 text-sm text-gray-600">
                                <?= date('d/m/Y', strtotime($building['data']->getCreatedAt())) ?>
                            </td>
                            <td class="p-3">
                                <div class="flex justify-center space-x-1">
                                    <!-- Bouton Voir -->
                                    <a
                                        href="/buildings/show/<?= $building['data']->getId() ?>"
                                        class="inline-flex items-center px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors duration-150"
                                        title="Voir les détails"
                                    >
                                        <i class="fas fa-eye mr-1"></i>
                                        Voir
                                    </a>
                                    
                                    <?php if ($building['canManage']): ?>
                                        <!-- Bouton Modifier -->
                                        <a
                                            href="/buildings/edit/<?= $building['data']->getId() ?>"
                                            class="inline-flex items-center px-2 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors duration-150"
                                            title="Modifier le bâtiment"
                                        >
                                            <i class="fas fa-edit mr-1"></i>
                                            Modifier
                                        </a>
                                        
                                        <!-- Bouton Supprimer -->
                                        <button
                                            type="button"
                                            onclick="openDeleteModal('/buildings/delete/', <?= $building['data']->getId() ?>, 'buildings.delete', '<?= htmlspecialchars($building['data']->getName()) ?>')"
                                            class="inline-flex items-center px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors duration-150"
                                            title="Supprimer le bâtiment"
                                        >
                                            <i class="fas fa-trash mr-1"></i>
                                            Supprimer
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

    <!-- Pagination améliorée -->
    <?php if ($totalPages > 1): ?>
        <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-600">
                Page <?= $page ?> sur <?= $totalPages ?>
            </div>
            
            <nav class="flex space-x-1" aria-label="Pagination">
                <?php if ($page > 1): ?>
                    <a
                        href="/buildings?page=1&search=<?= urlencode($search ?? '') ?><?= !empty($selectedStatuses) ? '&' . http_build_query(['statuses' => $selectedStatuses]) : '' ?>"
                        class="px-3 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors duration-150"
                        title="Première page"
                    >
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a
                        href="/buildings?page=<?= $page - 1 ?>&search=<?= urlencode($search ?? '') ?><?= !empty($selectedStatuses) ? '&' . http_build_query(['statuses' => $selectedStatuses]) : '' ?>"
                        class="px-3 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors duration-150"
                        title="Page précédente"
                    >
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php endif; ?>

                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                    <a
                        href="/buildings?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?><?= !empty($selectedStatuses) ? '&' . http_build_query(['statuses' => $selectedStatuses]) : '' ?>"
                        class="px-3 py-2 text-sm rounded transition-colors duration-150 <?= $i === $page ? 'bg-construction-yellow text-construction-black font-semibold' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>"
                    >
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a
                        href="/buildings?page=<?= $page + 1 ?>&search=<?= urlencode($search ?? '') ?><?= !empty($selectedStatuses) ? '&' . http_build_query(['statuses' => $selectedStatuses]) : '' ?>"
                        class="px-3 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors duration-150"
                        title="Page suivante"
                    >
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a
                        href="/buildings?page=<?= $totalPages ?>&search=<?= urlencode($search ?? '') ?><?= !empty($selectedStatuses) ? '&' . http_build_query(['statuses' => $selectedStatuses]) : '' ?>"
                        class="px-3 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors duration-150"
                        title="Dernière page"
                    >
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- JavaScript amélioré -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('filter-form');
    const searchInput = form.querySelector('input[name="search"]');
    const statusSelect = form.querySelector('select[name="statuses[]"]');

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
    if (statusSelect) {
        statusSelect.addEventListener('change', () => {
            form.submit();
        });
    }

    // Amélioration du select multiple
    if (statusSelect) {
        statusSelect.addEventListener('click', (e) => {
            if (e.target.value === '') {
                // Si "Tous les statuts" est sélectionné, désélectionner les autres
                Array.from(statusSelect.options).forEach(option => {
                    if (option.value !== '') {
                        option.selected = false;
                    }
                });
            } else {
                // Si un statut spécifique est sélectionné, désélectionner "Tous les statuts"
                statusSelect.options[0].selected = false;
            }
        });
    }

    // Fonction pour ouvrir le modal de suppression
    window.openDeleteModal = function(baseUrl, id, action, name) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <h2 class="text-lg font-semibold mb-4">Confirmer la suppression</h2>
                <p class="mb-4">Êtes-vous sûr de vouloir supprimer le bâtiment "${name}" ?</p>
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