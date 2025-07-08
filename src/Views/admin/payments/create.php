<?php
namespace App\Views\admin\payments;
use App\Utils\Flash;
$msgFlash = new Flash();
?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="/payments" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>
                    <h1 class="text-2xl font-bold text-construction-black">Créer un paiement</h1>
                </div>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-money-check-alt mr-1"></i>
                    Nouveau paiement
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
        <form id="payment-form" method="POST" action="/payments/store" class="space-y-6" novalidate>
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
                    <i class="fas fa-money-check-alt text-construction-yellow mr-2"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Informations générales</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Bail -->
                    <div>
                        <label for="lease_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Bail <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select
                                id="lease_id"
                                name="lease_id"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent bg-white"
                            >
                                <option value="">Sélectionnez un bail</option>
                                <?php foreach ($leases as $lease): ?>
                                    <option 
                                        value="<?= htmlspecialchars($lease->getId()) ?>"
                                        data-rent-amount="<?= htmlspecialchars($lease->getRentAmount()) ?>"
                                        data-charges-amount="<?= htmlspecialchars($lease->getChargesAmount()) ?>"
                                        data-deposit-amount="<?= htmlspecialchars($lease->getDepositAmount()) ?>"
                                        <?= isset($form_data['lease_id']) && $form_data['lease_id'] == $lease->getId() ? 'selected' : '' ?>
                                    >
                                        Bail #<?= htmlspecialchars($lease->getId()) ?> (Locataire: <?= htmlspecialchars($lease->tenant()->user()->getFirstName() . ' ' . $lease->tenant()->user()->getLastName()) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-file-contract absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Date de paiement -->
                    <div>
                        <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Date de paiement <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="date"
                                id="payment_date"
                                name="payment_date"
                                value="<?= isset($form_data['payment_date']) ? htmlspecialchars($form_data['payment_date']) : '' ?>"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                            >
                            <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Date d'échéance -->
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Date d'échéance <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="date"
                                id="due_date"
                                name="due_date"
                                value="<?= isset($form_data['due_date']) ? htmlspecialchars($form_data['due_date']) : '' ?>"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                            >
                            <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Montant -->
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                            Montant (€) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="amount"
                                name="amount"
                                step="0.01"
                                min="0"
                                value="<?= isset($form_data['amount']) ? htmlspecialchars($form_data['amount']) : '' ?>"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent"
                                placeholder="Montant en euros"
                            >
                            <i class="fas fa-euro-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Type -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                            Type <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select
                                id="type"
                                name="type"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent bg-white"
                            >
                                <option value="">Sélectionnez un type</option>
                                <option value="loyer" <?= isset($form_data['type']) && $form_data['type'] === 'loyer' ? 'selected' : '' ?>>Loyer</option>
                                <option value="charges" <?= isset($form_data['type']) && $form_data['type'] === 'charges' ? 'selected' : '' ?>>Charges</option>
                                <option value="depot" <?= isset($form_data['type']) && $form_data['type'] === 'depot' ? 'selected' : '' ?>>Dépôt</option>
                                <option value="autre" <?= isset($form_data['type']) && $form_data['type'] === 'autre' ? 'selected' : '' ?>>Autre</option>
                            </select>
                            <i class="fas fa-money-bill-wave absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Mode -->
                    <div>
                        <label for="mode" class="block text-sm font-medium text-gray-700 mb-1">
                            Mode <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select
                                id="mode"
                                name="mode"
                                required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-construction-yellow focus:border-transparent bg-white"
                            >
                                <option value="">Sélectionnez un mode</option>
                                <option value="cash" <?= isset($form_data['mode']) && $form_data['mode'] === 'cash' ? 'selected' : '' ?>>Espèces</option>
                                <option value="mobile" <?= isset($form_data['mode']) && $form_data['mode'] === 'mobile' ? 'selected' : '' ?>>Paiement mobile</option>
                                <option value="carte" <?= isset($form_data['mode']) && $form_data['mode'] === 'carte' ? 'selected' : '' ?>>Carte</option>
                            </select>
                            <i class="fas fa-money-bill-wave absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
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
                                <option value="payer" <?= isset($form_data['status']) && $form_data['status'] === 'payer' ? 'selected' : '' ?>>Payé</option>
                                <option value="en_attente" <?= isset($form_data['status']) && $form_data['status'] === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                <option value="en_retard" <?= isset($form_data['status']) && $form_data['status'] === 'en_retard' ? 'selected' : '' ?>>En retard</option>
                                <option value="annuler" <?= isset($form_data['status']) && $form_data['status'] === 'annuler' ? 'selected' : '' ?>>Annulé</option>
                            </select>
                            <i class="fas fa-info-circle absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
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
                                <?php if (!empty($agents)): ?>
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
                        href="/payments"
                        class="inline-flex items-center px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-construction-yellow"
                    >
                        <i class="fas fa-times mr-2"></i>
                        Annuler
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-construction-black bg-construction-yellow hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-construction-yellow transition-colors duration-200"
                    >
                        <i class="fas fa-money-check-alt mr-2"></i>
                        Créer le paiement
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript pour validation et pré-remplissage -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('payment-form');
    const leaseIdInput = document.getElementById('lease_id');
    const paymentDateInput = document.getElementById('payment_date');
    const dueDateInput = document.getElementById('due_date');
    const amountInput = document.getElementById('amount');
    const typeInput = document.getElementById('type');
    const modeInput = document.getElementById('mode');
    const statusInput = document.getElementById('status');
    const agencyIdInput = document.getElementById('agency_id');
    const agentIdInput = document.getElementById('agent_id');

    function validateField(input) {
        const name = input.name;
        const errorDiv = input.parentNode.querySelector('.error-message') || input.parentNode.parentNode.querySelector('.error-message');
        let isValid = true;
        let errorMessage = '';

        switch (name) {
            case 'lease_id':
            case 'mode':
            case 'type':
            case 'status':
                if (!input.value) {
                    isValid = false;
                    errorMessage = `Le ${name === 'lease_id' ? 'bail' : name === 'mode' ? 'mode' : name === 'type' ? 'type' : 'statut'} est requis.`;
                }
                break;
            case 'payment_date':
                if (!input.value) {
                    isValid = false;
                    errorMessage = 'La date de paiement est requise.';
                }
                break;
            case 'due_date':
                if (!input.value) {
                    isValid = false;
                    errorMessage = 'La date d’échéance est requise.';
                }
                break;
            case 'amount':
                if (!input.value || input.value <= 0) {
                    isValid = false;
                    errorMessage = 'Le montant doit être positif.';
                }
                break;
            case 'agency_id':
                if (!input.value) {
                    isValid = false;
                    errorMessage = 'L\'agence est requise.';
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

    // Pré-remplissage du montant en fonction du type et du bail
    function updateAmount() {
        const selectedOption = leaseIdInput.options[leaseIdInput.selectedIndex];
        const type = typeInput.value;
        let amount = '';
        if (selectedOption && type) {
            if (type === 'loyer') {
                amount = selectedOption.getAttribute('data-rent-amount') || '';
            } else if (type === 'charges') {
                amount = selectedOption.getAttribute('data-charges-amount') || '';
            } else if (type === 'depot') {
                amount = selectedOption.getAttribute('data-deposit-amount') || '';
            }
            amountInput.value = amount;
            validateField(amountInput);
        }
    }

    leaseIdInput.addEventListener('change', updateAmount);
    typeInput.addEventListener('change', updateAmount);

    // Validation en temps réel pour certains champs
    ['lease_id', 'payment_date', 'due_date', 'amount', 'type', 'mode', 'status', 'agency_id'].forEach(name => {
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
        if (!validateField(amountInput)) {
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

    // Déclencher le pré-remplissage si un bail est déjà sélectionné
    if (leaseIdInput.value && typeInput.value) {
        updateAmount();
    }
});
</script>