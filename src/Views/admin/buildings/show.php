<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-construction-black">Détails du bâtiment</h3>
        <a href="/admin/buildings" class="btn-secondary px-4 py-2 rounded-lg">← Retour à la liste</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <p class="text-sm font-medium text-gray-600">Nom</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($building_data['building']->getName()) ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Ville</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($building_data['building']->getCity()) ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Quartier</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($building_data['building']->getNeighborhood()) ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Pays</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($building_data['building']->getCountry()) ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Nombre d'étages</p>
            <p class="text-lg font-semibold text-construction-black"><?= $building_data['building']->getFloors() ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Nombre d'appartements</p>
            <p class="text-lg font-semibold text-construction-black"><?= $building_data['building']->getApartmentCount() ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Superficie du terrain</p>
            <p class="text-lg font-semibold text-construction-black"><?= $building_data['building']->getLandArea() ?> m²</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Parking</p>
            <p class="text-lg font-semibold text-construction-black"><?= $building_data['building']->getParking() ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Année de construction</p>
            <p class="text-lg font-semibold text-construction-black"><?= $building_data['building']->getYearBuilt() ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Statut</p>
            <p class="text-lg font-semibold text-construction-black"><?= $building_data['building']->getStatus() ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Prix</p>
            <p class="text-lg font-semibold text-construction-black"><?= number_format($building_data['building']->getPrice(), 0, ',', ' ') ?> FCFA</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Date de création</p>
            <p class="text-lg font-semibold text-construction-black"><?= $building_data['building']->getCreatedAt() ?></p>
        </div>
    </div>

    <!-- TYPE -->
    <?php if (!empty($building_data['type'])): ?>
        <div class="mt-8">
            <h4 class="text-lg font-bold text-construction-black mb-2">Type de bâtiment</h4>
            <p class="text-sm font-medium text-gray-600">Nom</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($building_data['type']->getName()) ?></p>
            <p class="text-sm font-medium text-gray-600 mt-2">Description</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($building_data['type']->getDescription()) ?></p>
        </div>
    <?php endif; ?>

    <!-- AGENCE -->
    <?php if (!empty($building_data['agency'])): ?>
        <div class="mt-8">
            <h4 class="text-lg font-bold text-construction-black mb-2">Agence</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-600">Nom</p>
                    <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($building_data['agency']->getName()) ?></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Email</p>
                    <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($building_data['agency']->getEmail()) ?></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Téléphone</p>
                    <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($building_data['agency']->getPhone()) ?></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Adresse</p>
                    <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($building_data['agency']->getAddress()) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- PROPRIÉTAIRE -->
    <?php if (!empty($building_data['ownerUser'])): ?>
        <div class="mt-8">
            <h4 class="text-lg font-bold text-construction-black mb-2">Propriétaire</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-600">Nom complet</p>
                    <p class="text-lg font-semibold text-construction-black">
                        <?= htmlspecialchars($building_data['ownerUser']->getFirstName() . ' ' . $building_data['ownerUser']->getLastName()) ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Email</p>
                    <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($building_data['ownerUser']->getEmail()) ?></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Téléphone</p>
                    <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($building_data['ownerUser']->getPhone()) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- IMAGES -->
    <?php if (!empty($building_data['images'])): ?>
        <div class="mt-8">
            <h4 class="text-lg font-bold text-construction-black mb-4">Images</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($building_data['images'] as $image): ?>
                    <img src="<?= htmlspecialchars($image->getPath()) ?>"
                         alt="<?= htmlspecialchars($image->getAltText()) ?>"
                         class="rounded-lg shadow-md w-full h-auto object-cover">
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Boutons d’action -->
    <div class="mt-6 flex justify-end space-x-3">
        <a href="/buildings/edit/<?= $building_data['building']->getId() ?>" class="btn-primary px-4 py-2 rounded-lg">Modifier</a>
        <button onclick="openModal('confirmDeleteModal')" class="btn-danger px-4 py-2 rounded-lg">Supprimer</button>
    </div>
</div>
