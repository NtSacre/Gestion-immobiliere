<?php
namespace App\Views\admin\leases;
use App\Utils\Flash;
$msgFlash = new Flash();
?>

<div class="bg-white rounded-lg shadow-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-construction-black">Liste des baux</h1>
        <?php if (in_array($user['role'], ['superadmin', 'admin', 'agent'])): ?>
            <a href="/leases/create" class="bg-construction-yellow text-construction-black px-4 py-2 rounded hover:bg-yellow-600 transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>Ajouter un bail
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
                        placeholder="Locataire, appartement..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                    >
                </div>
            </div>

            <!-- Filtre par statut -->
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
                        <option value="active" <?= in_array('active', $selectedStatuses ?? []) ? 'selected' : '' ?>>Actif</option>
                        <option value="terminated" <?= in_array('terminated', $selectedStatuses ?? []) ? 'selected' : '' ?>>Terminé</option>
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
                    href="/leases"
                    class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 flex items-center"
                >
                    <i class="fas fa-times mr-2"></i>Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <!-- Informations sur les résultats -->
    <div class="mb-4 text-sm text-gray-600">
        <?php if (!empty($leases)): ?>
            Affichage de <?= count($leases) ?> bail(s) sur <?= $totalLeases ?> au total.
        <?php endif; ?>
    </div>

    <!-- Affichage conditionnel : tableau pour superadmin/admin/agent, cartes pour proprietaire/locataire/acheteur -->
    <?php if (in_array($user['role'], ['superadmin', 'admin', 'agent'])): ?>
        <!-- Tableau des baux -->
        <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-construction-black text-white">
                        <th class="p-3 text-left font-semibold">ID</th>
                        <th class="p-3 text-left font-semibold">Appartement</th>
                        <th class="p-3 text-left font-semibold">Locataire</th>
                        <th class="p-3 text-left font-semibold">Date de début</th>
                        <th class="p-3 text-left font-semibold">Loyer</th>
                        <th class="p-3 text-left font-semibold">Statut</th>
                        <th class="p-3 text-center font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leases)): ?>
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500">
                                <i class="fas fa-file-contract text-4xl mb-2 block"></i>
                                <p class="text-lg font-medium">Aucun bail trouvé</p>
                                <p class="text-sm">Essayez de modifier vos critères de recherche</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leases as $lease): ?>
                            <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors duration-150">
                                <td class="p-3">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-construction-yellow rounded-full flex items-center justify-center text-construction-black font-semibold mr-3">
                                            <?= strtoupper(substr($lease->getId(), 0, 1)) ?>
                                        </div>
                                        <span class="font-medium"><?= htmlspecialchars($lease->getId()) ?></span>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <?php $apartment = $lease->apartment(); ?>
                                    <?= $apartment ? htmlspecialchars($apartment->getNumber()) : '<span class="text-gray-400 italic">Non défini</span>' ?>
                                </td>
                                <td class="p-3">
                                    <?php $tenant = $lease->tenant(); ?>
                                    <?= $tenant ? htmlspecialchars($tenant->user()->getFirstName() . ' ' . $tenant->user()->getLastName()) : '<span class="text-gray-400 italic">Non défini</span>' ?>
                                </td>
                                <td class="p-3 text-sm text-gray-600">
                                    <?= date('d/m/Y', strtotime($lease->getStartDate())) ?>
                                </td>
                                <td class="p-3">
                                    <?= number_format($lease->getRentAmount(), 2) ?> €
                                </td>
                                <td class="p-3">
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full 
                                        <?= $lease->getIsActive() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $lease->getIsActive() ? 'Actif' : 'Terminé' ?>
                                    </span>
                                </td>
                                <td class="p-3">
                                    <div class="flex justify-center space-x-1">
                                        <a
                                            href="/leases/<?= $lease->getId() ?>"
                                            class="inline-flex items-center px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors duration-150"
                                            title="Voir les détails"
                                        >
                                            <i class="fas fa-eye mr-1"></i>
                                            Voir
                                        </a>
                                        <?php if (in_array($user['role'], ['superadmin', 'admin', 'agent'])): ?>
                                            <a
                                                href="/leases/edit/<?= $lease->getId() ?>"
                                                class="inline-flex items-center px-2 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors duration-150"
                                                title="Modifier le bail"
                                            >
                                                <i class="fas fa-edit mr-1"></i>
                                                Modifier
                                            </a>
                                            <button
                                                type="button"
                                                onclick="openDeleteModal('/leases/delete/', <?= $lease->getId() ?>, 'leases.delete', 'Bail #<?= $lease->getId() ?>')"
                                                class="inline-flex items-center px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors duration-150"
                                                title="Supprimer le bail"
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
    <?php else: ?>
        <!-- Affichage en cartes pour proprietaire, locataire, acheteur -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($leases)): ?>
                <div class="col-span-full text-center text-gray-500 p-8">
                    <i class="fas fa-file-contract text-4xl mb-2 block"></i>
                    <p class="text-lg font-medium">Aucun bail trouvé</p>
                    <p class="text-sm">Vous n'avez aucun bail associé à votre compte.</p>
                </div>
            <?php else: ?>
                <?php foreach ($leases as $lease): ?>
                    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-construction-yellow rounded-full flex items-center justify-center text-construction-black font-semibold mr-3">
                                <?= strtoupper(substr($lease->getId(), 0, 1)) ?>
                            </div>
                            <h3 class="text-lg font-semibold">Bail #<?= htmlspecialchars($lease->getId()) ?></h3>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">
                            <strong>Appartement :</strong>
                            <?php $apartment = $lease->apartment(); ?>
                            <?= $apartment ? htmlspecialchars($apartment->getNumber()) : '<span class="text-gray-400 italic">Non défini</span>' ?>
                        </p>
                        <p class="text-sm text-gray-600 mb-2">
                            <strong>Locataire :</strong>
                            <?php $tenant = $lease->tenant(); ?>
                            <?= $tenant ? htmlspecialchars($tenant->user()->getFirstName() . ' ' . $tenant->user()->getLastName()) : '<span class="text-gray-400 italic">Non défini</span>' ?>
                        </p>
                        <p class="text-sm text-gray-600 mb-2">
                            <strong>Date de début :</strong> <?= date('d/m/Y', strtotime($lease->getStartDate())) ?>
                        </p>
                        <p class="text-sm text-gray-600 mb-2">
                            <strong>Loyer :</strong> <?= number_format($lease->getRentAmount(), 2) ?> €
                        </p>
                        <p class="text-sm mb-4">
                            <strong>Statut :</strong>
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full 
                                <?= $lease->getIsActive() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $lease->getIsActive() ? 'Actif' : 'Terminé' ?>
                            </span>
                        </p>
                        <div class="flex justify-start space-x-2">
                            <a
                                href="/leases/show/<?= $lease->getId() ?>"
                                class="inline-flex items-center px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors duration-150"
                            >
                                <i class="fas fa-eye mr-1"></i> Voir
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Pagination améliorée -->
    <?php if ($totalPages > 1): ?>
        <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-600">
                Page <?= $page ?> sur <?= $totalPages ?>
            </div>
            <nav class="flex space-x-1" aria-label="Pagination">
                <?php if ($page > 1): ?>
                    <a
                        href="/leases?page=1&search=<?= urlencode($search ?? '') ?><?= !empty($selectedStatuses) ? '&' . http_build_query(['statuses' => $selectedStatuses]) : '' ?>"
                        class="px-3 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors duration-150"
                        title="Première page"
                    >
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a
                        href="/leases?page=<?= $page - 1 ?>&search=<?= urlencode($search ?? '') ?><?= !empty($selectedStatuses) ? '&' . http_build_query(['statuses' => $selectedStatuses]) : '' ?>"
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
                        href="/leases?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?><?= !empty($selectedStatuses) ? '&' . http_build_query(['statuses' => $selectedStatuses]) : '' ?>"
                        class="px-3 py-2 text-sm rounded transition-colors duration-150 <?= $i === $page ? 'bg-construction-yellow text-construction-black font-semibold' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>"
                    >
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a
                        href="/leases?page=<?= $page + 1 ?>&search=<?= urlencode($search ?? '') ?><?= !empty($selectedStatuses) ? '&' . http_build_query(['statuses' => $selectedStatuses]) : '' ?>"
                        class="px-3 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors duration-150"
                        title="Page suivante"
                    >
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a
                        href="/leases?page=<?= $totalPages ?>&search=<?= urlencode($search ?? '') ?><?= !empty($selectedStatuses) ? '&' . http_build_query(['statuses' => $selectedStatuses]) : '' ?>"
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
            }, 800);
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
                Array.from(statusSelect.options).forEach(option => {
                    if (option.value !== '') {
                        option.selected = false;
                    }
                });
            } else {
                statusSelect.options[0].selected = false;
            }
        });
    }


});
</script>