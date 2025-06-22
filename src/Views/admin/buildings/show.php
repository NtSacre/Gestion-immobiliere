<?php
namespace App\Views\admin\buildings;
use App\Utils\Flash;
$msgFlash = new Flash();
?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="/buildings" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>
                    <h1 class="text-2xl font-bold text-construction-black">Détails du bâtiment</h1>
                </div>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-building mr-1"></i>
                    <?= htmlspecialchars($building_data['name']) ?>
                </div>
            </div>
        </div>

        <?php if ($flash = $msgFlash->get('success')): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <?= htmlspecialchars($flash) ?>
            </div>
        <?php endif; ?>
        <?php if ($flash = $msgFlash->get('error')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <div><?= htmlspecialchars($flash) ?></div>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <i class="fas fa-building text-construction-yellow mr-2"></i>
                <h2 class="text-lg font-semibold text-gray-900">Informations générales</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Nom</label>
                    <p class="mt-1 text-base text-gray-900"><?= htmlspecialchars($building_data['name']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Agence</label>
                    <p class="mt-1 text-base text-gray-900"><?= htmlspecialchars($building_data['agency']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Propriétaire</label>
                    <p class="mt-1 text-base text-gray-900"><?= htmlspecialchars($building_data['owner']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Ville</label>
                    <p class="mt-1 text-base text-gray-900"><?= htmlspecialchars($building_data['city']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Quartier</label>
                    <p class="mt-1 text-base text-gray-900"><?= htmlspecialchars($building_data['neighborhood']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Pays</label>
                    <p class="mt-1 text-base text-gray-900"><?= htmlspecialchars($building_data['country']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
            <div class="flex items-center mb-4">
                <i class="fas fa-info-circle text-construction-yellow mr-2"></i>
                <h2 class="text-lg font-semibold text-gray-900">Caractéristiques</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Nombre d’étages</label>
                    <p class="mt-1 text-base text-gray-900"><?= htmlspecialchars($building_data['floors']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Nombre d’appartements</label>
                    <p class="mt-1 text-base text-gray-900"><?= htmlspecialchars($building_data['apartment_count']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Superficie du terrain</label>
                    <p class="mt-1 text-base text-gray-900"><?= htmlspecialchars($building_data['land_area']) ?> m²</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Parking</label>
                    <p class="mt-1 text-base text-gray-900"><?= htmlspecialchars($building_data['parking']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Type de bâtiment</label>
                    <p class="mt-1 text-base text-gray-900"><?= htmlspecialchars($building_data['type']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Année de construction</label>
                    <p class="mt-1 text-base text-gray-900"><?= htmlspecialchars($building_data['year_built']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
            <div class="flex items-center mb-4">
                <i class="fas fa-tags text-construction-yellow mr-2"></i>
                <h2 class="text-lg font-semibold text-gray-900">Statut et prix</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Statut</label>
                    <p class="mt-1 text-base text-gray-900">
                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full 
                            <?= match($building_data['status']) {
                                'Disponible' => 'bg-green-100 text-green-800',
                                'Vendu' => 'bg-red-100 text-red-800',
                                'En construction' => 'bg-yellow-100 text-yellow-800',
                                'En rénovation' => 'bg-orange-100 text-orange-800',
                                default => 'bg-gray-100 text-gray-800'
                            } ?>">
                            <?= htmlspecialchars($building_data['status']) ?>
                        </span>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Prix</label>
                    <p class="mt-1 text-base text-gray-900"><?= htmlspecialchars($building_data['price']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
            <div class="flex items-center mb-4">
                <i class="fas fa-clock text-construction-yellow mr-2"></i>
                <h2 class="text-lg font-semibold text-gray-900">Informations supplémentaires</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Créé le</label>
                    <p class="mt-1 text-base text-gray-900"><?= htmlspecialchars($building_data['created_at']) ?></p>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-4">
            <a href="/buildings" class="inline-flex items-center px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-construction-yellow">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
            <?php if (in_array($role, ['superadmin', 'admin', 'agent'])): ?>
                <a href="/buildings/edit/<?= $building_data['id'] ?>" class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-construction-black bg-construction-yellow hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-construction-yellow">
                    <i class="fas fa-edit mr-2"></i>Modifier
                </a>
                <button onclick="openDeleteModal('/buildings/delete/', <?= $building_data['id'] ?>, 'buildings.delete', '<?= htmlspecialchars($building_data['name']) ?>')" 
                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <i class="fas fa-trash mr-2"></i>Supprimer
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>