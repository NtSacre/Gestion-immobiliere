<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-construction-black">Détails de l’appartement</h3>
        <a href="/apartments" class="btn-secondary px-4 py-2 rounded-lg">← Retour à la liste</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <p class="text-sm font-medium text-gray-600">Numéro</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['apartment']->getNumber()) ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Étage</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['apartment']->getFloor()) ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Superficie</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['apartment']->getArea() ?? 'Non spécifié') ?> m²</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Nombre de pièces</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['apartment']->getRooms() ?? 'Non spécifié') ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Chambres</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['apartment']->getBedrooms() ?? 'Non spécifié') ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Salles de bain</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['apartment']->getBathrooms() ?? 'Non spécifié') ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Toilettes</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['apartment']->getToilets() ?? 'Non spécifié') ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Salons</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['apartment']->getLivingRooms() ?? 'Non spécifié') ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Cuisines</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['apartment']->getKitchens() ?? 'Non spécifié') ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Balcon</p>
            <p class="text-lg font-semibold text-construction-black"><?= $apartment_data['apartment']->getHasBalcony() ? 'Oui' : 'Non' ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Loyer</p>
            <p class="text-lg font-semibold text-construction-black"><?= $apartment_data['apartment']->getRentAmount() ? number_format($apartment_data['apartment']->getRentAmount(), 0, ',', ' ') . ' FCFA' : 'Non spécifié' ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Charges</p>
            <p class="text-lg font-semibold text-construction-black"><?= $apartment_data['apartment']->getChargesAmount() ? number_format($apartment_data['apartment']->getChargesAmount(), 0, ',', ' ') . ' FCFA' : 'Non spécifié' ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Statut</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars(ucfirst($apartment_data['apartment']->getStatus())) ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Prix</p>
            <p class="text-lg font-semibold text-construction-black"><?= $apartment_data['apartment']->getPrice() ? number_format($apartment_data['apartment']->getPrice(), 0, ',', ' ') . ' FCFA' : 'Non spécifié' ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Date de création</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['apartment']->getCreatedAt()) ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Commodités</p>
            <p class="text-lg font-semibold text-construction-black">
                <?php
                $amenities = $apartment_data['apartment']->getAmenities() ? json_decode($apartment_data['apartment']->getAmenities(), true) : [];
                echo !empty($amenities) ? htmlspecialchars(implode(', ', array_map('ucfirst', $amenities))) : 'Aucune';
                ?>
            </p>
        </div>
    </div>

    <!-- TYPE -->
    <?php if (!empty($apartment_data['type'])): ?>
        <div class="mt-8">
            <h4 class="text-lg font-bold text-construction-black mb-2">Type d’appartement</h4>
            <p class="text-sm font-medium text-gray-600">Nom</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['type']->getName()) ?></p>
            <p class="text-sm font-medium text-gray-600 mt-2">Description</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['type']->getDescription() ?? 'Aucune description') ?></p>
        </div>
    <?php endif; ?>

    <!-- BÂTIMENT -->
    <?php if (!empty($apartment_data['building'])): ?>
        <div class="mt-8">
            <h4 class="text-lg font-bold text-construction-black mb-2">Bâtiment</h4>
            <p class="text-sm font-medium text-gray-600">Nom</p>
            <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['building']->getName()) ?></p>
        </div>
    <?php endif; ?>

    <!-- AGENCE -->
    <?php if (!empty($apartment_data['agency'])): ?>
        <div class="mt-8">
            <h4 class="text-lg font-bold text-construction-black mb-2">Agence</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-600">Nom</p>
                    <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['agency']->getName()) ?></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Email</p>
                    <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['agency']->getEmail()) ?></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Téléphone</p>
                    <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['agency']->getPhone()) ?></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Adresse</p>
                    <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['agency']->getAddress()) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- PROPRIÉTAIRE -->
    <?php if (!empty($apartment_data['ownerUser'])): ?>
        <div class="mt-8">
            <h4 class="text-lg font-bold text-construction-black mb-2">Propriétaire</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-600">Nom complet</p>
                    <p class="text-lg font-semibold text-construction-black">
                        <?= htmlspecialchars($apartment_data['ownerUser']->getFirstName() . ' ' . $apartment_data['ownerUser']->getLastName()) ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Email</p>
                    <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['ownerUser']->getEmail()) ?></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Téléphone</p>
                    <p class="text-lg font-semibold text-construction-black"><?= htmlspecialchars($apartment_data['ownerUser']->getPhone()) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- IMAGES -->
    <?php if (!empty($apartment_data['images'])): ?>
        <div class="mt-8">
            <h4 class="text-lg font-bold text-construction-black mb-4">Images</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($apartment_data['images'] as $image): ?>
                    <img src="<?= htmlspecialchars($image->getPath()) ?>"
                         alt="<?= htmlspecialchars($image->getAltText()) ?>"
                         class="rounded-lg shadow-md w-full h-auto object-cover">
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Boutons d’action -->
    <div class="mt-6 flex justify-end space-x-3">
        <a href="/apartments/edit/<?= $apartment_data['apartment']->getId() ?>" class="btn-primary px-4 py-2 rounded-lg">Modifier</a>
        <button onclick="openModal('confirmDeleteModal')" class="btn-danger px-4 py-2 rounded-lg">Supprimer</button>
    </div>
</div>