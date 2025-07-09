<?php
namespace App\Views\admin\leases;
use App\Utils\Flash;
$msgFlash = new Flash();
?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="/leases" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>
                    <h1 class="text-2xl font-bold text-construction-black">Créer un bail</h1>
                </div>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-file-contract mr-1"></i>
                    Nouveau bail
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
        <form id="lease-form" method="POST" action="/leases/store" class="space-y-6" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <?php if ($user['role'] === 'agent'): ?>
                <input type="hidden" name="agent_id" value="<?= htmlspecialchars($user['id']) ?>">
            <?php endif; ?>
            <?php if (in_array($user['role'], ['admin', 'agent'])): ?>
                <input type="hidden" name="agency_id" value="<?= htmlspecialchars($user['agency_id']) ?>">
            <?php endif; ?>

            <!-- Informations générales -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-file-contract text-construction-yellow mr-2"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Informations générales</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Appartement -->
                    <div>
                        <label for="apartment_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Appartement <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select
                                id="apartment_id"
                                name="apartment_id"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent bg-white"
                            >
                                <option value="">Sélectionnez un appartement</option>
                                <?php foreach ($apartments as $apartment): ?>
                                    <option 
                                        value="<?= htmlspecialchars($apartment->getId()) ?>"
                                        data-rent-amount="<?= htmlspecialchars($apartment->getRentAmount()) ?>"
                                        data-charges-amount="<?= htmlspecialchars($apartment->getChargesAmount()) ?>"
                                        <?= isset($form_data['apartment_id']) && $form_data['apartment_id'] == $apartment->getId() ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($apartment->getNumber()) ?> (Bâtiment: <?= htmlspecialchars($apartment->building()->getName()) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-home absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Locataire -->
                    <div>
                        <label for="tenant_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Locataire <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select
                                id="tenant_id"
                                name="tenant_id"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent bg-white"
                            >
                                <option value="">Sélectionnez un locataire</option>
                                <?php foreach ($tenants as $tenant): ?>
                                    <option 
                                        value="<?= htmlspecialchars($tenant->getId()) ?>"
                                        <?= isset($form_data['tenant_id']) && $form_data['tenant_id'] == $tenant->getId() ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($tenant->user()->getFirstName() . ' ' . $tenant->user()->getLastName()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Date de début -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Date de début <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="date"
                                id="start_date"
                                name="start_date"
                                value="<?= isset($form_data['start_date']) ? htmlspecialchars($form_data['start_date']) : '' ?>"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                            >
                            <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Date de fin -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Date de fin
                        </label>
                        <div class="relative">
                            <input
                                type="date"
                                id="end_date"
                                name="end_date"
                                value="<?= isset($form_data['end_date']) ? htmlspecialchars($form_data['end_date']) : '' ?>"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                            >
                            <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Loyer -->
                    <div>
                        <label for="rent_amount" class="block text-sm font-medium text-gray-700 mb-1">
                            Loyer (€) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="rent_amount"
                                name="rent_amount"
                                step="0.01"
                                min="0"
                                value="<?= isset($form_data['rent_amount']) ? htmlspecialchars($form_data['rent_amount']) : '' ?>"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Loyer en euros"
                            >
                            <i class="fas fa-euro-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Charges -->
                    <div>
                        <label for="charges_amount" class="block text-sm font-medium text-gray-700 mb-1">
                            Charges (€) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="charges_amount"
                                name="charges_amount"
                                step="0.01"
                                min="0"
                                value="<?= isset($form_data['charges_amount']) ? htmlspecialchars($form_data['charges_amount']) : '' ?>"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Charges en euros"
                            >
                            <i class="fas fa-euro-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Dépôt -->
                    <div>
                        <label for="deposit_amount" class="block text-sm font-medium text-gray-700 mb-1">
                            Dépôt (€) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="deposit_amount"
                                name="deposit_amount"
                                step="0.01"
                                min="0"
                                value="<?= isset($form_data['deposit_amount']) ? htmlspecialchars($form_data['deposit_amount']) : '' ?>"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Dépôt en euros"
                            >
                            <i class="fas fa-euro-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Fréquence de paiement -->
                    <div>
                        <label for="payment_frequency" class="block text-sm font-medium text-gray-700 mb-1">
                            Fréquence de paiement <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select
                                id="payment_frequency"
                                name="payment_frequency"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent bg-white"
                            >
                                <option value="">Sélectionnez une fréquence</option>
                                <option value="mensuel" <?= isset($form_data['payment_frequency']) && $form_data['payment_frequency'] === 'mensuel' ? 'selected' : '' ?>>Mensuel</option>
                                <option value="trimestriel" <?= isset($form_data['payment_frequency']) && $form_data['payment_frequency'] === 'trimestriel' ? 'selected' : '' ?>>Trimestriel</option>
                            </select>
                            <i class="fas fa-calendar absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Statut actif -->
                    <div class="flex items-center">
                        <input
                            type="checkbox"
                            id="is_active"
                            name="is_active"
                            value="1"
                            <?= isset($form_data['is_active']) && $form_data['is_active'] ? 'checked' : '' ?>
                            class="h-4 w-4 text-construction-yellow focus:ring-construction-yellow border-gray-300 rounded"
                        >
                        <label for="is_active" class="ml-2 text-sm text-gray-700">Bail actif</label>
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
                    <!-- Agence (superadmin uniquement) -->
                    <?php if ($user['role'] === 'superadmin'): ?>
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
                                            value="<?= htmlspecialchars($agency->getId()) ?>"
                                            <?= isset($form_data['agency_id']) && $form_data['agency_id'] == $agency->getId() ? 'selected' : '' ?>
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

                    <!-- Agent (superadmin ou admin) -->
                    <?php if (in_array($user['role'], ['superadmin', 'admin'])): ?>
                        <div>
                            <label for="agent_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Agent
                            </label>
                            <div class="relative">
                                <?php if (count($agents) > 0): ?>
                                    <select
                                        id="agent_id"
                                        name="agent_id"
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent bg-white"
                                    >
                                        <option value="">Utiliser mon compte (par défaut)</option>
                                        <?php foreach ($agents as $agent): ?>
                                            <option 
                                                value="<?= htmlspecialchars($agent->getId()) ?>"
                                                <?= isset($form_data['agent_id']) && $form_data['agent_id'] == $agent->getId() ? 'selected' : '' ?>
                                            >
                                                <?= htmlspecialchars($agent->getFirstName() . ' ' . $agent->getLastName()) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <i class="fas fa-user-tie absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <?php else: ?>
                                    <p class="text-gray-500">Aucun agent disponible. Votre compte sera utilisé.</p>
                                    <input type="hidden" name="agent_id" value="">
                                <?php endif; ?>
                            </div>
                            <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-end space-x-4">
                    <a
                        href="/leases"
                        class="inline-flex items-center px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-construction-yellow"
                    >
                        <i class="fas fa-times mr-2"></i>
                        Annuler
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-construction-black bg-construction-yellow hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-construction-yellow transition-colors duration-200"
                    >
                        <i class="fas fa-file-contract mr-2"></i>
                        Créer le bail
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript pour validation et pré-remplissage -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('lease-form');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const rentAmountInput = document.getElementById('rent_amount');
    const chargesAmountInput = document.getElementById('charges_amount');
    const depositAmountInput = document.getElementById('deposit_amount');
    const apartmentIdInput = document.getElementById('apartment_id');
    const tenantIdInput = document.getElementById('tenant_id');
    const agencyIdInput = document.getElementById('agency_id');
    const agentIdInput = document.getElementById('agent_id');
    const paymentFrequencyInput = document.getElementById('payment_frequency');

    function validateField(input) {
        const name = input.name;
        const errorDiv = input.parentNode.querySelector('.error-message') || input.parentNode.parentNode.querySelector('.error-message');
        let isValid = true;
        let errorMessage = '';

        switch (name) {
            case 'apartment_id':
            case 'tenant_id':
            case 'agency_id':
            case 'payment_frequency':
                if (!input.value) {
                    isValid = false;
                    errorMessage = `Le ${name === 'apartment_id' ? 'appartement' : name === 'tenant_id' ? 'locataire' : name === 'agency_id' ? 'agence' : 'fréquence de paiement'} est requis.`;
                }
                break;
            case 'start_date':
                if (!input.value) {
                    isValid = false;
                    errorMessage = 'La date de début est requise.';
                }
                break;
            case 'end_date':
                if (input.value && new Date(input.value) < new Date(startDateInput.value)) {
                    isValid = false;
                    errorMessage = 'La date de fin ne peut pas être antérieure à la date de début.';
                }
                break;
            case 'rent_amount':
                if (!input.value || input.value <= 0) {
                    isValid = false;
                    errorMessage = 'Le loyer doit être positif.';
                }
                break;
            case 'charges_amount':
                if (input.value === '' || input.value < 0) {
                    isValid = false;
                    errorMessage = 'Les charges ne peuvent pas être négatives.';
                }
                break;
            case 'deposit_amount':
                if (input.value === '' || input.value < 0) {
                    isValid = false;
                    errorMessage = 'Le dépôt ne peut pas être négatif.';
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

    // Pré-remplissage des champs loyer et charges en fonction de l'appartement
    apartmentIdInput.addEventListener('change', function() {
        const selectedOption = apartmentIdInput.options[apartmentIdInput.selectedIndex];
        const rentAmount = selectedOption ? selectedOption.getAttribute('data-rent-amount') : '';
        const chargesAmount = selectedOption ? selectedOption.getAttribute('data-charges-amount') : '';
        rentAmountInput.value = rentAmount || '';
        chargesAmountInput.value = chargesAmount || '';
        validateField(rentAmountInput);
        validateField(chargesAmountInput);
    });

    // Validation en temps réel pour certains champs
    ['apartment_id', 'tenant_id', 'agency_id', 'start_date', 'end_date', 'rent_amount', 'charges_amount', 'deposit_amount', 'payment_frequency'].forEach(name => {
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
        const inputs = form.querySelectorAll('input[required], select[required]');
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });
        if (!validateField(endDateInput) || !validateField(rentAmountInput) || !validateField(chargesAmountInput) || !validateField(depositAmountInput)) {
            isValid = false;
        }
        if (!isValid) {
            e.preventDefault();
            const firstError = form.querySelector('.border-red-500, :invalid');
            if (firstError) {
                firstError.focus();
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    // Déclencher le pré-remplissage si un appartement est déjà sélectionné (par exemple, après une erreur)
    if (apartmentIdInput.value) {
        apartmentIdInput.dispatchEvent(new Event('change'));
    }
});
</script>