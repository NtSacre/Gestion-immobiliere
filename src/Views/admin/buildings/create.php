<?php
namespace App\Views\admin\buildings;
use App\Utils\Flash;
$msgFlash = new Flash();
?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="/buildings" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>
                    <h1 class="text-2xl font-bold text-construction-black">Ajouter un bâtiment</h1>
                </div>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-building mr-1"></i>
                    Nouveau bâtiment
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
            <div class="bg-red-100 border border-green-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <div><?= $flash ?></div>
            </div>
        <?php endif; ?>

        <!-- Formulaire principal -->
        <form id="building-form" method="POST" action="/buildings/store" enctype="multipart/form-data" class="space-y-6" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <!-- Informations générales -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-building text-construction-yellow mr-2"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Informations générales</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nom -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nom du bâtiment <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                required
                                minlength="3"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Nom du bâtiment"
                            >
                            <i class="fas fa-building absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Ville -->
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1">
                            Ville <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="city"
                                name="city"
                                value="<?= htmlspecialchars($_POST['city'] ?? '') ?>"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Ville"
                            >
                            <i class="fas fa-city absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Quartier -->
                    <div>
                        <label for="neighborhood" class="block text-sm font-medium text-gray-700 mb-1">
                            Quartier
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="neighborhood"
                                name="neighborhood"
                                value="<?= htmlspecialchars($_POST['neighborhood'] ?? '') ?>"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Quartier"
                            >
                            <i class="fas fa-map-marker-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Pays -->
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-1">
                            Pays <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="country"
                                name="country"
                                value="<?= htmlspecialchars($_POST['country'] ?? '') ?>"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Pays"
                            >
                            <i class="fas fa-globe absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Nombre d'étages -->
                    <div>
                        <label for="floors" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre d'étages <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="floors"
                                name="floors"
                                value="<?= htmlspecialchars($_POST['floors'] ?? '') ?>"
                                required
                                min="0"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Nombre d'étages"
                            >
                            <i class="fas fa-layer-group absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Nombre d'appartements -->
                    <div>
                        <label for="apartment_count" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre d'appartements <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="apartment_count"
                                name="apartment_count"
                                value="<?= htmlspecialchars($_POST['apartment_count'] ?? '') ?>"
                                required
                                min="0"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Nombre d'appartements"
                            >
                            <i class="fas fa-home absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Superficie du terrain -->
                    <div>
                        <label for="land_area" class="block text-sm font-medium text-gray-700 mb-1">
                            Superficie du terrain (m²)
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="land_area"
                                name="land_area"
                                step="0.01"
                                value="<?= htmlspecialchars($_POST['land_area'] ?? '') ?>"
                                min="0"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Superficie en m²"
                            >
                            <i class="fas fa-ruler-combined absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Parking -->
                    <div>
                        <label for="parking" class="block text-sm font-medium text-gray-700 mb-1">
                            Parking disponible <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center space-x-4">
                            <label class="inline-flex items-center">
                                <input
                                    type="checkbox"
                                    id="parking"
                                    name="parking"
                                    value="1"
                                    <?= isset($_POST['parking']) ? 'checked' : '' ?>
                                    class="form-checkbox h-4 w-4 text-construction-yellow focus:ring-construction-yellow border-gray-300"
                                >
                                <span class="ml-2 text-sm text-gray-700">Oui</span>
                            </label>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Type de bâtiment -->
                    <div>
                        <label for="type_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Type de bâtiment <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select
                                id="type_id"
                                name="type_id"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent bg-white"
                            >
                                <option value="">Sélectionnez un type</option>
                                <?php if (!empty($buildingTypes)): ?>
                                    <?php foreach ($buildingTypes as $type): ?>
                                        <option 
                                            value="<?= $type->getId() ?>"
                                            <?= (isset($_POST['type_id']) && $_POST['type_id'] == $type->getId()) ? 'selected' : '' ?>
                                        >
                                            <?= htmlspecialchars(ucfirst($type->getName())) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <i class="fas fa-home absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Année de construction -->
                    <div>
                        <label for="year_built" class="block text-sm font-medium text-gray-700 mb-1">
                            Année de construction
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="year_built"
                                name="year_built"
                                value="<?= htmlspecialchars($_POST['year_built'] ?? '') ?>"
                                min="1800"
                                max="<?= date('Y') + 1 ?>"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Année"
                            >
                            <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
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
                                        <?= (isset($_POST['status']) && $_POST['status'] === $status) ? 'selected' : '' ?>
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
                                value="<?= htmlspecialchars($_POST['price'] ?? '') ?>"
                                min="0"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Prix en euros"
                            >
                            <i class="fas fa-euro-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>
            </div>

            <!-- Associations -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-users text-construction-yellow mr-2"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Associations</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Agence (pour superadmin uniquement) -->
                    <?php if ($role === 'superadmin'): ?>
                        <div>
                            <label for="agency_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Agence <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select
                                    id="agency_id"
                                    name="agency_id"
                                    required
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent bg-white"
                                >
                                    <option value="">Sélectionnez une agence</option>
                                    <?php foreach ($agencies as $agency): ?>
                                        <option 
                                            value="<?= $agency->getId() ?>"
                                            <?= (isset($_POST['agency_id']) && $_POST['agency_id'] == $agency->getId()) ? 'selected' : '' ?>
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

                    <!-- Agent (pour superadmin et admin) -->
                    <?php if (in_array($role, ['superadmin', 'admin'])): ?>
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
                                            <?= (isset($_POST['agent_id']) && $_POST['agent_id'] == $agent->getId()) ? 'selected' : '' ?>
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
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                            >
                                <option value="">Sélectionnez un propriétaire</option>
                                <?php foreach ($owners as $owner): ?>
                                    <option 
                                        value="<?= $owner->id() ?>"
                                        <?= (isset($_POST['owner_id']) && $_POST['owner_id'] == $owner->id()) ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($owner->getFirstName() . ' ' . $owner->getLastName()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-images text-construction-yellow mr-2"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Images (maximum 4)</h2>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <input
                            type="file"
                            id="images"
                            name="images[]"
                            accept="image/jpeg,image/png,image/gif"
                            multiple
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow"
                        >
                        <p class="text-xs text-gray-500 mt-1">Formats acceptés : JPEG, PNG, GIF. Taille maximale : 5MB. Maximum 4 images.</p>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div id="image-preview" class="grid grid-cols-2 md:grid-cols-4 gap-4"></div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-end space-x-4">
                    <a
                        href="/buildings"
                        class="inline-flex items-center px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-construction-yellow"
                    >
                        <i class="fas fa-times mr-2"></i>
                        Annuler
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-construction-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-construction-yellow"
                    >
                        <i class="fas fa-building mr-2"></i>
                        Créer le bâtiment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript pour la validation et l'interactivité -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('building-form');
    const imageInput = document.getElementById('images');
    const imagePreview = document.getElementById('image-preview');

    // Validation en temps réel
    function validateField(field) {
        const input = field instanceof Event ? this : field;
        const name = input.name;
        const errorDiv = input.parentNode.querySelector('.error-message') || input.parentNode.parentNode.querySelector('.error-message');
        let isValid = true;
        let errorMessage = '';

        switch (name) {
            case 'name':
                if (input.value.length < 3) {
                    isValid = false;
                    errorMessage = 'Le nom doit contenir au moins 3 caractères.';
                }
                break;
            case 'city':
            case 'country':
                if (!input.value.trim()) {
                    isValid = false;
                    errorMessage = `Le ${name === 'city' ? 'ville' : 'pays'} est requis.`;
                }
                break;
            case 'floors':
            case 'apartment_count':
                if (input.value < 0) {
                    isValid = false;
                    errorMessage = `Le ${name === 'floors' ? 'nombre d’étages' : 'nombre d’appartements'} ne peut pas être négatif.`;
                }
                break;
            case 'land_area':
                if (input.value && input.value < 0) {
                    isValid = false;
                    errorMessage = 'La superficie du terrain doit être positive.';
                }
                break;
            case 'year_built':
                if (input.value && (input.value < 1800 || input.value > <?= date('Y') + 1 ?>)) {
                    isValid = false;
                    errorMessage = 'L’année de construction est invalide.';
                }
                break;
            case 'price':
                if (input.value && input.value < 0) {
                    isValid = false;
                    errorMessage = 'Le prix doit être positif.';
                }
                break;
            case 'status':
            case 'type_id':
            case 'owner_id':
                if (!input.value) {
                    isValid = false;
                    errorMessage = `Le ${name === 'status' ? 'statut' : name === 'type_id' ? 'type' : 'propriétaire'} est requis.`;
                }
                break;
            case 'agency_id':
                if (input.required && !input.value) {
                    isValid = false;
                    errorMessage = 'L’agence est requise.';
                }
                break;
            case 'images[]':
                if (input.files.length > 4) {
                    isValid = false;
                    errorMessage = 'Maximum 4 images autorisées.';
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

    // Aperçu des images
    function updateImagePreview() {
        imagePreview.innerHTML = '';
        const files = imageInput.files;
        if (files.length > 4) {
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

    // Event listeners
    imageInput.addEventListener('change', function() {
        validateField(this);
        updateImagePreview();
    });

    // Validation en temps réel
    const inputsToValidate = ['name', 'city', 'country', 'floors', 'apartment_count', 'land_area', 'year_built', 'price', 'status', 'type_id', 'owner_id', 'agency_id'];
    inputsToValidate.forEach(inputName => {
        const input = document.querySelector(`[name="${inputName}"]`);
        if (input) {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => {
                if (input.classList.contains('border-red-500')) {
                    validateField(input);
                }
            });
        }
    });

    // Validation du formulaire avant soumission
    form.addEventListener('submit', function(e) {
        let isFormValid = true;
        
        // Vérifier tous les champs
        const allInputs = form.querySelectorAll('input[required], select[required], input[name="images[]"]');
        allInputs.forEach(input => {
            if (!validateField(input)) {
                isFormValid = false;
            }
        });

        if (!isFormValid) {
            e.preventDefault();
            const firstInvalid = form.querySelector('.border-red-500, :invalid');
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
});
</script>