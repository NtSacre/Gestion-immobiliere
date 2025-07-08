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
                    <h1 class="text-2xl font-bold text-construction-black">Détails du bail #<?= htmlspecialchars($lease->getId()) ?></h1>
                </div>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-file-contract mr-1"></i>
                    Bail
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

        <!-- Détails du bail -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center mb-4">
                <i class="fas fa-file-contract text-construction-yellow mr-2"></i>
                <h2 class="text-lg font-semibold text-gray-900">Informations générales</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-600 mb-2">
                        <strong>Appartement :</strong>
                        <?php $apartment = $lease->apartment(); ?>
                        <?= $apartment ? htmlspecialchars($apartment->getNumber() . ' (Bâtiment: ' . $apartment->building()->getName() . ')') : '<span class="text-gray-400 italic">Non défini</span>' ?>
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
                        <strong>Date de fin :</strong> <?= $lease->getEndDate() ? date('d/m/Y', strtotime($lease->getEndDate())) : '<span class="text-gray-400 italic">Non définie</span>' ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-2">
                        <strong>Loyer :</strong> <?= number_format($lease->getRentAmount(), 2) ?> €
                    </p>
                    <p class="text-sm text-gray-600 mb-2">
                        <strong>Charges :</strong> <?= number_format($lease->getChargesAmount(), 2) ?> €
                    </p>
                    <p class="text-sm text-gray-600 mb-2">
                        <strong>Dépôt :</strong> <?= number_format($lease->getDepositAmount(), 2) ?> €
                    </p>
                    <p class="text-sm text-gray-600 mb-2">
                        <strong>Fréquence de paiement :</strong> <?= $lease->getPaymentFrequency() === 'monthly' ? 'Mensuel' : 'Trimestriel' ?>
                    </p>
                    <p class="text-sm text-gray-600 mb-2">
                        <strong>Statut :</strong>
                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full 
                            <?= $lease->getIsActive() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                            <?= $lease->getIsActive() ? 'Actif' : 'Terminé' ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Associations -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center mb-4">
                <i class="fas fa-users text-construction-yellow mr-2"></i>
                <h2 class="text-lg font-semibold text-gray-900">Associations</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php if ($lease->getAgencyId()): ?>
                    <p class="text-sm text-gray-600 mb-2">
                        <strong>Agence :</strong>
                        <?php $agency = \App\Models\Agency::find($lease->getAgencyId()); ?>
                        <?= $agency ? htmlspecialchars($agency->getName()) : '<span class="text-gray-400 italic">Non définie</span>' ?>
                    </p>
                <?php endif; ?>
                <?php if ($lease->getAgentId()): ?>
                    <p class="text-sm text-gray-600 mb-2">
                        <strong>Agent :</strong>
                        <?php $agent = \App\Models\User::find($lease->getAgentId()); ?>
                        <?= $agent ? htmlspecialchars($agent->getFirstName() . ' ' . $agent->getLastName()) : '<span class="text-gray-400 italic">Non défini</span>' ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Paiements associés -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center mb-4">
                <i class="fas fa-money-check-alt text-construction-yellow mr-2"></i>
                <h2 class="text-lg font-semibold text-gray-900">Paiements associés</h2>
            </div>
            <?php if (empty($payments)): ?>
                <p class="text-gray-500">Aucun paiement associé à ce bail.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-construction-black text-white">
                                <th class="p-3 text-left font-semibold">ID</th>
                                <th class="p-3 text-left font-semibold">Date de paiement</th>
                                <th class="p-3 text-left font-semibold">Montant</th>
                                <th class="p-3 text-left font-semibold">Type</th>
                                <th class="p-3 text-center font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors duration-150">
                                    <td class="p-3"><?= htmlspecialchars($payment['id']) ?></td>
                                    <td class="p-3"><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
                                    <td class="p-3"><?= number_format($payment['amount'], 2) ?> €</td>
                                    <td class="p-3"><?= htmlspecialchars(ucfirst($payment['type'])) ?></td>
                                    <td class="p-3">
                                        <?php if ($payment['quittance_path']): ?>
                                            <a
                                                href="/leases/downloadQuittance/<?= $payment['id'] ?>"
                                                class="inline-flex items-center px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors duration-150"
                                                title="Télécharger la quittance"
                                            >
                                                <i class="fas fa-download mr-1"></i>
                                                Quittance
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Boutons d'action -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between space-x-4">
                <a
                    href="/leases"
                    class="inline-flex items-center px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-construction-yellow"
                >
                    <i class="fas fa-arrow-left mr-2"></i>
                    Retour
                </a>
                <div class="flex space-x-4">
                    <?php if (in_array($user['role'], ['superadmin', 'admin', 'agent']) && ($user['role'] !== 'agent' || $lease->getAgentId() === $user['id'])): ?>
                        <a
                            href="/leases/edit/<?= htmlspecialchars($lease->getId()) ?>"
                            class="inline-flex items-center px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-construction-yellow"
                        >
                            <i class="fas fa-edit mr-2"></i>
                            Modifier
                        </a>
                        <button
                            type="button"
                            onclick="openDeleteModal('/leases/delete/', <?= htmlspecialchars($lease->getId()) ?>, 'leases.delete', 'Bail #<?= htmlspecialchars($lease->getId()) ?>')"
                            class="inline-flex items-center px-6 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        >
                            <i class="fas fa-trash mr-2"></i>
                            Supprimer
                        </button>
                    <?php endif; ?>
                    <a
                        href="/leases/downloadPDF/<?= htmlspecialchars($lease->getId()) ?>"
                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-construction-black bg-construction-yellow hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-construction-yellow transition-colors duration-200"
                    >
                        <i class="fas fa-download mr-2"></i>
                        Télécharger en PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript pour le modal de suppression -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    window.openDeleteModal = function(baseUrl, id, action, name) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <h2 class="text-lg font-semibold mb-4">Confirmer la suppression</h2>
                <p class="mb-4">Êtes-vous sûr de vouloir supprimer "${name}" ?</p>
                <form action="${baseUrl}${id}" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="_method" value="DELETE">
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="this.closest('.fixed').remove()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Annuler</button>
                        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Supprimer</button>
                    </div>
                </form>
            </div>
        `;
        document.body.appendChild(modal);
    };
});
</script>