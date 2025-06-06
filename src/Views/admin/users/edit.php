<?php
namespace App\Views\admin\users;
use App\Utils\Flash;
$msgFlash = new Flash();

// Récupérer les données du propriétaire si applicable
$owner = $targetUser->role()->getName() === 'proprietaire' ? \App\Models\Owner::findByUserId($targetUser->getId()) : null;
?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="/users" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>
                    <h1 class="text-2xl font-bold text-construction-black">Modifier l’utilisateur</h1>
                </div>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-user-edit mr-1"></i>
                    Modification du compte utilisateur
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
        <form id="user-form" method="POST" action="/users/update/<?= $id ?>" class="space-y-6" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <!-- Informations de connexion -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-key text-construction-yellow mr-2"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Informations de connexion</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nom d'utilisateur -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                            Nom d'utilisateur <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="<?= htmlspecialchars($_POST['username'] ?? $targetUser->getUsername()) ?>"
                                required
                                minlength="3"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Saisissez le nom d'utilisateur"
                            >
                            <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Adresse email <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= htmlspecialchars($_POST['email'] ?? $targetUser->getEmail()) ?>"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="exemple@email.com"
                            >
                            <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>
            </div>

            <!-- Informations personnelles -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-id-card text-construction-yellow mr-2"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Informations personnelles</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Prénom -->
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Prénom <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            value="<?= htmlspecialchars($_POST['first_name'] ?? $targetUser->getFirstName()) ?>"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow"
                            placeholder="Prénom"
                        >
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Nom -->
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1"> 
                            Nom<span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            value="<?= htmlspecialchars($_POST['last_name'] ?? $targetUser->getLastName()) ?>"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow"
                            placeholder="Nom de famille"
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Téléphone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Téléphone
                        </label>
                        <div class="relative">
                            <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="<?= htmlspecialchars($_POST['phone'] ?? $targetUser->getPhone()) ?>"
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow"
                            placeholder="01 23 45 67 89"
                        >
                            <i class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Rôle -->
                    <div>
                        <label for="role_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Rôle <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select
                                id="role_id"
                                name="role_id"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none
                                focus:ring-2 focus:ring-construction-yellow focus:border-transparent bg-white"
                            >
                                <option value="">Sélectionnez un rôle</option>
                                <?php if (!empty($roles)): ?>
                                    <?php foreach ($roles as $roleItem): ?>
                                        <option 
                                            value="<?= $roleItem->getId() ?>"
                                            <?= ($_POST['role_id'] ?? $targetUser->getRoleId()) == $roleItem->getId() ? 'selected' : '' ?>
                                        >
                                            <?= htmlspecialchars(ucfirst($roleItem->getName())) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <i class="fas fa-user-tag absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>
            </div>

            <!-- Informations spécifiques pour propriétaire -->
            <div id="owner-fields" class="bg-white rounded-lg shadow-sm p-6 <?= $targetUser->role()->getName() === 'proprietaire' ? '' : 'hidden' ?>">
                <div class="flex items-center mb-4">
                    <i class="fas fa-building text-construction-yellow mr-2"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Informations propriétaire</h2>
                </div>
                
                <div class="space-y-4">
                    <!-- Type de propriétaire -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Type de propriétaire <span class="text-red-500">*</span>
                        </label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input
                                    type="radio"
                                    name="owner_type"
                                    value="particulier"
                                    <?= ($_POST['owner_type'] ?? ($owner ? $owner->getType() : '')) === 'particulier' ? 'checked' : '' ?>
                                    class="form-radio h-4 w-4 text-construction-yellow focus:ring-construction-yellow border-gray-300"
                                >
                                <span class="ml-2 text-sm text-gray-700">Particulier</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input
                                    type="radio"
                                    name="owner_type"
                                    value="entreprise"
                                    <?= ($_POST['owner_type'] ?? ($owner ? $owner->getType() : '')) === 'entreprise' ? 'checked' : '' ?>
                                    class="form-radio h-4 w-4 text-construction-yellow focus:ring-construction-yellow border-gray-300"
                                >
                                <span class="ml-2 text-sm text-gray-700">Entreprise</span>
                            </label>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- SIRET (pour entreprise) -->
                    <div id="siret-field" class="<?= ($owner && $owner->getType() === 'entreprise') ? '' : 'hidden' ?>">
                        <label for="siret" class="block text-sm font-medium text-gray-700 mb-1">
                            Numéro SIRET <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="siret"
                                name="siret"
                                value="<?= htmlspecialchars($_POST['siret'] ?? ($owner ? $owner->getSiret() : '')) ?>"
                                pattern="[0-9]{14}"
                                maxlength="14"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow"
                                placeholder="12345678901234"
                            >
                            <i class="fas fa-building-columns absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                        <p class="text-xs text-gray-500 mt-1">Le SIRET doit contenir exactement 14 chiffres</p>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-end space-x-4">
                    <a
                        href="/users"
                        class="inline-flex items-center px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-construction-yellow"
                    >
                        <i class="fas fa-times mr-2"></i>
                        Annuler
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-construction-black bg-construction-yellow hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-construction-yellow"
                    >
                        <i class="fas fa-save mr-2"></i>
                        Enregistrer les modifications
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript pour la validation et l'interactivité -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('user-form');
    const roleSelect = document.getElementById('role_id');
    const ownerFields = document.getElementById('owner-fields');
    const ownerTypeRadios = document.querySelectorAll('input[name="owner_type"]');
    const siretField = document.getElementById('siret-field');
    const siretInput = document.getElementById('siret');

    // Gestion de l'affichage des champs propriétaire
    function toggleOwnerFields() {
        const selectedRole = roleSelect.options[roleSelect.selectedIndex];
        const roleName = selectedRole ? selectedRole.text.toLowerCase() : '';
        
        if (roleName === 'proprietaire') {
            ownerFields.classList.remove('hidden');
            ownerTypeRadios.forEach(radio => {
                radio.required = true;
            });
        } else {
            ownerFields.classList.add('hidden');
            siretField.classList.add('hidden');
            ownerTypeRadios.forEach(radio => {
                radio.required = false;
                radio.checked = false;
            });
            if (siretInput) {
                siretInput.required = false;
                siretInput.value = '';
            }
        }
    }

    // Gestion de l'affichage du champ SIRET
    function toggleSiretField() {
        const selectedType = document.querySelector('input[name="owner_type"]:checked');
        if (selectedType && selectedType.value === 'entreprise') {
            siretField.classList.remove('hidden');
            if (siretInput) siretInput.required = true;
        } else {
            siretField.classList.add('hidden');
            if (siretInput) {
                siretInput.required = false;
                siretInput.value = '';
            }
        }
    }

    // Validation en temps réel
    function validateField(input) {
        const errorDiv = input.parentNode.parentNode.querySelector('.error-message');
        let isValid = true;
        let errorMessage = '';

        switch (input.name) {
            case 'username':
                if (input.value.length < 3) {
                    isValid = false;
                    errorMessage = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
                }
                break;
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value)) {
                    isValid = false;
                    errorMessage = 'Veuillez saisir une adresse email valide.';
                }
                break;
            case 'siret':
                if (input.required && !/^[0-9]{14}$/.test(input.value)) {
                    isValid = false;
                    errorMessage = 'Le SIRET doit contenir exactement 14 chiffres.';
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

    // Event listeners
    roleSelect.addEventListener('change', toggleOwnerFields);
    ownerTypeRadios.forEach(radio => {
        radio.addEventListener('change', toggleSiretField);
    });

    // Validation en temps réel
    const inputsToValidate = ['username', 'email', 'siret'];
    inputsToValidate.forEach(inputName => {
        const input = document.querySelector(`input[name="${inputName}"]`);
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
        
        const allInputs = form.querySelectorAll('input[required], select[required]');
        allInputs.forEach(input => {
            if (!input.checkValidity() || !validateField(input)) {
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

    // Initialisation
    toggleOwnerFields();
    if (document.querySelector('input[name="owner_type"]:checked')) {
        toggleSiretField();
    }
});
</script>