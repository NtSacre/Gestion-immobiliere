<?php
namespace App\Views\admin\buildings;
use App\Models\BuildingType;
use App\Models\Agency;
use App\Models\Owner;
?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="/buildings" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>
                    <h1 class="text-2xl font-bold text-construction-black">Modifier le bâtiment</h1>
                </div>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-building mr-1"></i>
                    Modification du bâtiment
                </div>
            </div>
        </div>

        <!-- Messages flash -->
        <?php if ($flash && $flash->get('success') !== null): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <?= htmlspecialchars($flash->get('success')) ?>
            </div>
        <?php endif; ?>
        <?php if ($flash && $flash->get('error') !== null): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <div><?= htmlspecialchars($flash->get('error')) ?></div>
            </div>
        <?php endif; ?>
        <?php if ($flash && $flash->get('warning') !== null): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?= htmlspecialchars($flash->get('warning')) ?>
            </div>
        <?php endif; ?>

        <form id="building-form" method="POST" action="/buildings/update/<?= htmlspecialchars($building->getId()) ?>" class="space-y-6" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <script>
                console.log('Jeton CSRF transmis dans le formulaire:', '<?= htmlspecialchars($csrf_token) ?>');
            </script>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-building text-construction-yellow mr-2"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Informations du bâtiment</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? $building->getName() ?? '') ?>" required minlength="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow" placeholder="Nom du bâtiment">
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div>
                        <label for="agency_id" class="block text-sm font-medium text-gray-700 mb-1">Agence <span class="text-red-500">*</span></label>
                        <select id="agency_id" name="agency_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow bg-white">
                            <option value="">Sélectionnez une agence</option>
                            <?php if (!empty($agencies) && is_array($agencies)): ?>
                                <?php foreach ($agencies as $agency): ?>
                                    <?php
                                    if (!is_object($agency) || !($agency instanceof Agency) || $agency->getId() === null || $agency->getName() === null) {
                                        error_log("Agence invalide détectée dans la vue (edit.php) : " . json_encode($agency));
                                        continue; // Ignorer l'élément invalide
                                    }
                                    ?>
                                    <option value="<?= htmlspecialchars($agency->getId()) ?>" <?= ($_POST['agency_id'] ?? $building->getAgencyId()) == $agency->getId() ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($agency->getName()) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Aucune agence disponible</option>
                            <?php endif; ?>
                        </select>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div>
                        <label for="owner_id" class="block text-sm font-medium text-gray-700 mb-1">Propriétaire <span class="text-red-500">*</span></label>
                        <select name="owner_id" id="owner_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow bg-white">
                            <option value="">Sélectionner un propriétaire</option>
                            <?php foreach ($owners as $owner): ?>
                                <?php if ($owner instanceof Owner && $owner->getId() !== null): ?>
                                    <?php $user = $owner->user(); ?>
                                    <option value="<?= htmlspecialchars($owner->getId()) ?>" <?= ($_POST['owner_id'] ?? $building->getOwnerId()) == $owner->getId() ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user ? ($user->getFirstName() . ' ' . $user->getLastName()) : 'Inconnu') ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1">Ville <span class="text-red-500">*</span></label>
                        <input type="text" id="city" name="city" value="<?= htmlspecialchars($_POST['city'] ?? $building->getCity() ?? '') ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow" placeholder="Ville">
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div>
                        <label for="neighborhood" class="block text-sm font-medium text-gray-700 mb-1">Quartier</label>
                        <input type="text" id="neighborhood" name="neighborhood" value="<?= htmlspecialchars($_POST['neighborhood'] ?? $building->getNeighborhood() ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow" placeholder="Quartier">
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Pays <span class="text-red-500">*</span></label>
                        <input type="text" id="country" name="country" value="<?= htmlspecialchars($_POST['country'] ?? $building->getCountry() ?? '') ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow" placeholder="Pays">
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div>
                        <label for="floors" class="block text-sm font-medium text-gray-700 mb-1">Nombre d’étages <span class="text-red-500">*</span></label>
                        <input type="number" id="floors" name="floors" value="<?= htmlspecialchars($_POST['floors'] ?? $building->getFloors() ?? '') ?>" required min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow" placeholder="0">
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div>
                        <label for="apartment_count" class="block text-sm font-medium text-gray-700 mb-1">Nombre d’appartements <span class="text-red-500">*</span></label>
                        <input type="number" id="apartment_count" name="apartment_count" value="<?= htmlspecialchars($_POST['apartment_count'] ?? $building->getApartmentCount() ?? '') ?>" required min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow" placeholder="0">
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div>
                        <label for="land_area" class="block text-sm font-medium text-gray-700 mb-1">Superficie du terrain (m²)</label>
                        <input type="number" id="land_area" name="land_area" step="0.01" value="<?= htmlspecialchars($_POST['land_area'] ?? $building->getLandArea() ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow" placeholder="Superficie en m²">
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div>
                        <label for="parking" class="block text-sm font-medium text-gray-700 mb-1">Parking <span class="text-red-500">*</span></label>
                        <select id="parking" name="parking" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow bg-white">
                            <option value="">Sélectionnez le type de parking</option>
                            <option value="aucun" <?= ($_POST['parking'] ?? $building->getParking()) === 'aucun' ? 'selected' : '' ?>>Aucun</option>
                            <option value="souterrain" <?= ($_POST['parking'] ?? $building->getParking()) === 'souterrain' ? 'selected' : '' ?>>Souterrain</option>
                            <option value="exterieur" <?= ($_POST['parking'] ?? $building->getParking()) === 'exterieur' ? 'selected' : '' ?>>Extérieur</option>
                            <option value="couvert" <?= ($_POST['parking'] ?? $building->getParking()) === 'couvert' ? 'selected' : '' ?>>Couvert</option>
                        </select>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div>
                        <label for="type_id" class="block text-sm font-medium text-gray-700 mb-1">Type de bâtiment <span class="text-red-500">*</span></label>
                        <select id="type_id" name="type_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow bg-white">
                            <option value="">Sélectionnez le type</option>
                            <?php foreach ($buildingTypes as $type): ?>
                                <option value="<?= htmlspecialchars($type->getId()) ?>" <?= ($_POST['type_id'] ?? $building->getTypeId()) == $type->getId() ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div>
                        <label for="year_built" class="block text-sm font-medium text-gray-700 mb-1">Année de construction</label>
                        <input type="number" id="year_built" name="year_built" value="<?= htmlspecialchars($_POST['year_built'] ?? $building->getYearBuilt() ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow" placeholder="Année">
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Statut <span class="text-red-500">*</span></label>
                        <select id="status" name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow bg-white">
                            <option value="">Sélectionnez le statut</option>
                            <option value="disponible" <?= ($_POST['status'] ?? $building->getStatus()) === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                            <option value="vendu" <?= ($_POST['status'] ?? $building->getStatus()) === 'vendu' ? 'selected' : '' ?>>Vendu</option>
                            <option value="en_construction" <?= ($_POST['status'] ?? $building->getStatus()) === 'en_construction' ? 'selected' : '' ?>>En construction</option>
                            <option value="en_renovation" <?= ($_POST['status'] ?? $building->getStatus()) === 'en_renovation' ? 'selected' : '' ?>>En rénovation</option>
                        </select>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Prix (€)</label>
                        <input type="number" id="price" name="price" step="0.01" value="<?= htmlspecialchars($_POST['price'] ?? $building->getPrice() ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow" placeholder="Prix en €">
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-end space-x-4">
                    <a href="/buildings" class="inline-flex items-center px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-construction-yellow">
                        <i class="fas fa-times mr-2"></i>Annuler
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-construction-black bg-construction-yellow hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-construction-yellow">
                        <i class="fas fa-save mr-2"></i>Mettre à jour le bâtiment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('building-form');
        const inputsToValidate = ['name', 'city', 'country', 'floors', 'apartment_count', 'land_area', 'price'];

        function validateField(input) {
            const errorDiv = input.parentNode.querySelector('.error-message');
            let isValid = true;
            let errorMessage = '';

            switch (input.name) {
                case 'name':
                    if (input.value.length < 3) {
                        isValid = false;
                        errorMessage = 'Le nom doit contenir au moins 3 caractères.';
                    }
                    break;
                case 'city':
                case 'country':
                    if (!input.value) {
                        isValid = false;
                        errorMessage = 'Ce champ est requis.';
                    }
                    break;
                case 'floors':
                case 'apartment_count':
                    if (input.value < 0) {
                        isValid = false;
                        errorMessage = 'La valeur doit être positive.';
                    }
                    break;
                case 'land_area':
                case 'price':
                    if (input.value && input.value < 0) {
                        isValid = false;
                        errorMessage = 'La valeur doit être positive.';
                    }
                    break;
                case 'year_built':
                    if (input.value && (input.value < 1800 || input.value > <?= date('Y') + 5 ?>)) {
                        isValid = false;
                        errorMessage = 'Année de construction invalide.';
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

        inputsToValidate.forEach(name => {
            const input = document.querySelector(`input[name="${name}"]`);
            if (input) {
                input.addEventListener('blur', () => validateField(input));
                input.addEventListener('input', () => {
                    if (input.classList.contains('border-red-500')) validateField(input);
                });
            }
        });

        form.addEventListener('submit', function(e) {
            let isFormValid = true;
            const allInputs = form.querySelectorAll('input[required], select[required]');
            allInputs.forEach(input => {
                if (!input.checkValidity() || !validateField(input)) {
                    isFormValid = false;
                }
            });

            if (!isFormValid) {
                e.preventDefault();
                const firstInvalid = form.querySelector('.border-red-500, :invalid');
                if (firstInvalid) firstInvalid.focus();
            }
        });
    });
</script>