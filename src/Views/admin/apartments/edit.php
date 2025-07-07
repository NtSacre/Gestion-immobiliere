<?php
namespace App\Views\admin\apartments;
use App\Utils\Flash;
$msgFlash = new Flash();
?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="/apartments" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>
                    <h1 class="text-2xl font-bold text-construction-black">Modifier l’appartement</h1>
                </div>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-home mr-1"></i>
                    Édition de l’appartement
                </div>
            </div>
        </div>

        <!-- Messages flash -->
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

        <!-- Formulaire principal -->
        <form id="apartment-form" method="POST" action="/apartments/update/<?= $apartment->getId() ?>" enctype="multipart/form-data" class="space-y-6" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="id" value="<?= htmlspecialchars($apartment->getId()) ?>">

            <!-- Informations générales -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-home text-construction-yellow mr-2"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Informations générales</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Numéro -->
                    <div>
                        <label for="number" class="block text-sm font-medium text-gray-700 mb-1">
                            Numéro <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="number"
                                name="number"
                                value="<?= htmlspecialchars($apartment->getNumber()) ?>"
                                required
                                minlength="1"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Ex. A101"
                            >
                            <i class="fas fa-home absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Étage -->
                    <div>
                        <label for="floor" class="block text-sm font-medium text-gray-700 mb-1">
                            Étage <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="floor"
                                name="floor"
                                value="<?= htmlspecialchars($apartment->getFloor()) ?>"
                                required
                                min="0"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Ex. 3"
                            >
                            <i class="fas fa-layer-group absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Superficie -->
                    <div>
                        <label for="area" class="block text-sm font-medium text-gray-700 mb-1">
                            Superficie (m²)
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="area"
                                name="area"
                                step="0.01"
                                value="<?= htmlspecialchars($apartment->getArea() ?? '') ?>"
                                min="0"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Ex. 75.50"
                            >
                            <i class="fas fa-ruler-combined absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Pièces -->
                    <div>
                        <label for="rooms" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre de pièces
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="rooms"
                                name="rooms"
                                value="<?= htmlspecialchars($apartment->getRooms() ?? '') ?>"
                                min="0"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Ex. 4"
                            >
                            <i class="fas fa-door-open absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Chambres -->
                    <div>
                        <label for="bedrooms" class="block text-sm font-medium text-gray-700 mb-1">
                            Chambres
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="bedrooms"
                                name="bedrooms"
                                value="<?= htmlspecialchars($apartment->getBedrooms() ?? '') ?>"
                                min="0"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Ex. 2"
                            >
                            <i class="fas fa-bed absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Salles de bain -->
                    <div>
                        <label for="bathrooms" class="block text-sm font-medium text-gray-700 mb-1">
                            Salles de bain
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="bathrooms"
                                name="bathrooms"
                                value="<?= htmlspecialchars($apartment->getBathrooms() ?? '') ?>"
                                min="0"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Ex. 1"
                            >
                            <i class="fas fa-bath absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Toilettes -->
                    <div>
                        <label for="toilets" class="block text-sm font-medium text-gray-700 mb-1">
                            Toilettes
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="toilets"
                                name="toilets"
                                value="<?= htmlspecialchars($apartment->getToilets() ?? '') ?>"
                                min="0"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Ex. 1"
                            >
                            <i class="fas fa-toilet absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Salons -->
                    <div>
                        <label for="living_rooms" class="block text-sm font-medium text-gray-700 mb-1">
                            Salons
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="living_rooms"
                                name="living_rooms"
                                value="<?= htmlspecialchars($apartment->getLivingRooms() ?? '') ?>"
                                min="0"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Ex. 1"
                            >
                            <i class="fas fa-couch absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Cuisines -->
                    <div>
                        <label for="kitchens" class="block text-sm font-medium text-gray-700 mb-1">
                            Cuisines
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="kitchens"
                                name="kitchens"
                                value="<?= htmlspecialchars($apartment->getKitchens() ?? '') ?>"
                                min="0"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Ex. 1"
                            >
                            <i class="fas fa-utensils absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Balcon -->
                    <div>
                        <label for="has_balcony" class="block text-sm font-medium text-gray-700 mb-1">
                            Balcon
                        </label>
                        <div class="relative">
                            <select
                                id="has_balcony"
                                name="has_balcony"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent bg-white"
                            >
                                <option value="0" <?= $apartment->getHasBalcony() == 0 ? 'selected' : '' ?>>Non</option>
                                <option value="1" <?= $apartment->getHasBalcony() == 1 ? 'selected' : '' ?>>Oui</option>
                            </select>
                            <i class="fas fa-tree absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Type d'appartement -->
                    <div>
                        <label for="type_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Type d'appartement <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select
                                id="type_id"
                                name="type_id"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent bg-white"
                            >
                                <option value="">Sélectionnez un type</option>
                                <?php foreach ($apartmentTypes as $type): ?>
                                    <option 
                                        value="<?= $type->getId() ?>"
                                        <?= $apartment->getTypeId() == $type->getId() ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars(ucfirst($type->getName())) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-home absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Loyer -->
                    <div>
                        <label for="rent" class="block text-sm font-medium text-gray-700 mb-1">
                            Loyer (€)
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="rent"
                                name="rent"
                                step="0.01"
                                value="<?= htmlspecialchars($apartment->getRentAmount() ?? '') ?>"
                                min="0"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Ex. 1200.00"
                            >
                            <i class="fas fa-euro-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Charges -->
                    <div>
                        <label for="charges_amount" class="block text-sm font-medium text-gray-700 mb-1">
                            Charges (€)
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="charges_amount"
                                name="charges_amount"
                                step="0.01"
                                value="<?= htmlspecialchars($apartment->getChargesAmount() ?? '') ?>"
                                min="0"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Ex. 150.00"
                            >
                            <i class="fas fa-euro-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Statut -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                            Statut <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select
                                id="status"
                                name="status"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent bg-white"
                            >
                                <option value="">Sélectionnez un statut</option>
                                <?php foreach ($allowedStatuses as $status): ?>
                                    <option 
                                        value="<?= htmlspecialchars($status) ?>"
                                        <?= $apartment->getStatus() === $status ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars(ucfirst($status)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-info-circle absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Prix -->
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                            Prix (€)
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="price"
                                name="price"
                                step="0.01"
                                value="<?= htmlspecialchars($apartment->getPrice() ?? '') ?>"
                                min="0"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Ex. 250000.00"
                            >
                            <i class="fas fa-euro-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>

                <!-- Commodités -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Commodités</label>
                    <div class="flex flex-wrap gap-4">
                        <?php 
                        $currentAmenities = $apartment->getAmenities() ? json_decode($apartment->getAmenities(), true) : [];
                        $amenities = ['parking', 'ascenseur', 'climatisation', 'chauffage', 'jardin', 'terrasse', 'cave'];
                        foreach ($amenities as $amenity): ?>
                            <label class="inline-flex items-center">
                                <input
                                    type="checkbox"
                                    name="amenities[]"
                                    value="<?= htmlspecialchars($amenity) ?>"
                                    <?= in_array($amenity, $currentAmenities) ? 'checked' : '' ?>
                                    class="rounded border-gray-300 text-construction-yellow focus:ring-construction-yellow"
                                >
                                <span class="ml-2 capitalize"><?= htmlspecialchars($amenity) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                </div>
            </div>

            <!-- Associations -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-users text-construction-yellow mr-2"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Associations</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Bâtiment -->
                    <div>
                        <label for="building_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Bâtiment <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select
                                id="building_id"
                                name="building_id"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent bg-white"
                            >
                                <option value="">Sélectionnez un bâtiment</option>
                                <?php foreach ($buildings as $building): ?>
                                    <option 
                                        value="<?= $building->getId() ?>"
                                        <?= $apartment->getBuildingId() == $building->getId() ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($building->getName()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-building absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Propriétaire -->
                    <div>
                        <label for="owner_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Propriétaire <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select
                                id="owner_id"
                                name="owner_id"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent bg-white"
                            >
                                <option value="">Sélectionnez un propriétaire</option>
                                <?php foreach ($owners as $owner): ?>
                                    <option 
                                        value="<?= $owner->getId() ?>"
                                        <?= $apartment->getOwnerId() == $owner->getId() ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($owner->getFirstName() . ' ' . $owner->getLastName()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Agence (pour superadmin uniquement) -->
                    <?php if ($user['role'] === 'superadmin'): ?>
                        <div>
                            <label for="agency_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Agence
                            </label>
                            <div class="relative">
                                <select
                                    id="agency_id"
                                    name="agency_id"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent bg-white"
                                >
                                    <option value="">Sélectionnez une agence</option>
                                    <?php foreach ($agencies as $agency): ?>
                                        <option 
                                            value="<?= $agency->getId() ?>"
                                            <?= $apartment->getAgencyId() == $agency->getId() ? 'selected' : '' ?>
                                        >
                                            <?= htmlspecialchars($agency->getName()) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-briefcase absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                            <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                    <?php endif; ?>

                    <!-- Agent (optionnel) -->
                    <?php if (in_array($user['role'], ['superadmin', 'admin'])): ?>
                        <div>
                            <label for="agent_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Agent
                            </label>
                            <div class="relative">
                                <select
                                    id="agent_id"
                                    name="agent_id"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent bg-white"
                                >
                                    <option value="">Aucun agent</option>
                                    <?php foreach ($agents as $agent): ?>
                                        <option 
                                            value="<?= $agent->getId() ?>"
                                            <?= $apartment->getAgentId() == $agent->getId() ? 'selected' : '' ?>
                                        >
                                            <?= htmlspecialchars($agent->getFirstName() . ' ' . $agent->getLastName()) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-user-tie absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                            <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Images -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-images text-construction-yellow mr-2"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Images (maximum 4)</h2>
                </div>
                
                <div class="space-y-4">
                    <!-- Images existantes -->
                    <div id="existing-images" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php if (!empty($images)): ?>
                            <?php foreach ($images as $image): ?>
                                <div class="relative" data-image-id="<?= htmlspecialchars($image->getId()) ?>">
                                    <img src="<?= htmlspecialchars($image->getPath()) ?>" class="w-full h-24 object-cover rounded-lg" alt="Image de l’appartement">
                                    <button type="button" class="delete-image absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600" data-image-id="<?= htmlspecialchars($image->getId()) ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <input type="hidden" name="existing_images[]" value="<?= htmlspecialchars($image->getId()) ?>">
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Nouvelles images -->
                    <div>
                        <input
                            type="file"
                            id="images"
                            name="images[]"
                            accept="image/jpeg,image/png,image/gif"
                            multiple
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow"
                        >
                        <p class="text-xs text-gray-500 mt-1">Formats acceptés : JPEG, PNG, GIF. Taille maximale : 5MB. Maximum 4 images (y compris existantes).</p>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div id="image-preview" class="grid grid-cols-2 md:grid-cols-4 gap-4"></div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-end space-x-4">
                    <a
                        href="/apartments"
                        class="inline-flex items-center px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-construction-yellow"
                    >
                        <i class="fas fa-times mr-2"></i>
                        Annuler
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-construction-black bg-construction-yellow hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-construction-yellow transition-colors duration-200"
                    >
                        <i class="fas fa-save mr-2"></i>
                        Mettre à jour l’appartement
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript pour validation et prévisualisation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('apartment-form');
    const imageInput = document.getElementById('images');
    const imagePreview = document.getElementById('image-preview');
    const existingImagesContainer = document.getElementById('existing-images');
    const maxImages = 4;

    function validateField(input) {
        const name = input.name;
        const errorDiv = input.parentNode.querySelector('.error-message') || input.parentNode.parentNode.querySelector('.error-message');
        let isValid = true;
        let errorMessage = '';

        switch (name) {
            case 'number':
                if (input.value.length < 1) {
                    isValid = false;
                    errorMessage = 'Le numéro doit contenir au moins 1 caractère.';
                }
                break;
            case 'floor':
                if (input.value < 0) {
                    isValid = false;
                    errorMessage = 'L’étage ne peut pas être négatif.';
                }
                break;
            case 'area':
                if (input.value && input.value <= 0) {
                    isValid = false;
                    errorMessage = 'La superficie doit être positive.';
                }
                break;
            case 'rooms':
            case 'bedrooms':
            case 'bathrooms':
            case 'toilets':
            case 'living_rooms':
            case 'kitchens':
                if (input.value && input.value < 0) {
                    isValid = false;
                    errorMessage = `Le nombre de ${name === 'rooms' ? 'pièces' : name === 'bedrooms' ? 'chambres' : name === 'bathrooms' ? 'salles de bain' : name === 'toilets' ? 'toilettes' : name === 'living_rooms' ? 'salons' : 'cuisines'} ne peut pas être négatif.`;
                }
                break;
            case 'rent':
            case 'charges_amount':
            case 'price':
                if (input.value && input.value < 0) {
                    isValid = false;
                    errorMessage = `Le ${name === 'rent' ? 'loyer' : name === 'charges_amount' ? 'montant des charges' : 'prix'} doit être positif.`;
                }
                break;
            case 'building_id':
            case 'owner_id':
            case 'type_id':
            case 'status':
                if (!input.value) {
                    isValid = false;
                    errorMessage = `Le ${name === 'building_id' ? 'bâtiment' : name === 'owner_id' ? 'propriétaire' : name === 'type_id' ? 'type' : 'statut'} est requis.`;
                }
                break;
            case 'agency_id':
                break;
            case 'images[]':
                const existingImages = existingImagesContainer.querySelectorAll('[data-image-id]').length;
                const newImages = input.files.length;
                if (existingImages + newImages > maxImages) {
                    isValid = false;
                    errorMessage = `Maximum ${maxImages} images autorisées (inclus les images existantes).`;
                }
                const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                const maxSize = 5 * 1024 * 1024; // 5MB
                for (const file of input.files) {
                    if (!validTypes.includes(file.type)) {
                        isValid = false;
                        errorMessage = `Le fichier ${file.name} n'est pas une image valide (JPEG, PNG, GIF).`;
                    }
                    if (file.size > maxSize) {
                        isValid = false;
                        errorMessage = `Le fichier ${file.name} dépasse la taille maximale de 5MB.`;
                    }
                }
                break;
        }

        if (errorDiv) {
            if (isValid) {
                errorDiv.classList.add('hidden');
                input.classList.remove('border-red-500');
                input.classList.add('border-green-500');
            } else {
                errorDiv.textContent = errorMessage;
                errorDiv.classList.remove('hidden');
                input.classList.remove('border-green-500');
                input.classList.add('border-red-500');
            }
        }

        return isValid;
    }

    function updateImagePreview() {
        imagePreview.innerHTML = '';
        const files = imageInput.files;
        const existingImages = existingImagesContainer.querySelectorAll('[data-image-id]').length;
        if (existingImages + files.length > maxImages) {
            validateField(imageInput);
            return;
        }

        for (const file of files) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-24 object-cover rounded-lg" alt="Aperçu">
                    <button type="button" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                div.querySelector('button').addEventListener('click', function() {
                    const dt = new DataTransfer();
                    for (const f of imageInput.files) {
                        if (f !== file) {
                            dt.items.add(f);
                        }
                    }
                    imageInput.files = dt.files;
                    updateImagePreview();
                    validateField(imageInput);
                });
                imagePreview.appendChild(div);
            };
            reader.readAsDataURL(file);
        }
    }

    // Gestion de la suppression des images existantes
    existingImagesContainer.querySelectorAll('.delete-image').forEach(button => {
        button.addEventListener('click', function() {
            const imageDiv = this.parentNode;
            const imageId = this.getAttribute('data-image-id');
            // Ajouter un champ caché pour marquer l'image à supprimer
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_images[]';
            input.value = imageId;
            form.appendChild(input);
            // Supprimer l'image du DOM
            imageDiv.remove();
            validateField(imageInput);
        });
    });

    imageInput.addEventListener('change', function() {
        validateField(this);
        updateImagePreview();
    });

    // Validation en temps réel pour certains champs
    ['number', 'floor', 'area', 'rooms', 'bedrooms', 'bathrooms', 'toilets', 'living_rooms', 'kitchens', 'rent', 'charges_amount', 'price', 'building_id', 'owner_id', 'type_id', 'status', 'agency_id', 'agent_id'].forEach(name => {
        const input = form.querySelector(`[name="${name}"]`);
        if (input) {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => {
                if (input.classList.contains('border-red-500')) {
                    validateField(input);
                }
            });
        }
    });

    form.addEventListener('submit', function(e) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], select[required], input[name="images[]"]');
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });
        if (!isValid) {
            e.preventDefault();
            const firstError = form.querySelector('.border-red-500, :invalid');
            if (firstError) {
                firstError.focus();
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
});
</script>