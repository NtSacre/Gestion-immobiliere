<?php
$title = 'Modifier le bail';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-construction-black mb-6">Modifier le bail</h1>

    <!-- Messages flash -->
    <?php if ($flash = $this->flash->get('error')): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-xl">
            <?php echo htmlspecialchars($flash); ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire de modification -->
    <form action="/leases/update/<?php echo $lease->getId(); ?>" method="POST" class="bg-white rounded-xl shadow-lg p-6">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="apartment_id" class="block text-sm font-medium text-gray-700">Appartement</label>
                <select name="apartment_id" id="apartment_id" class="w-full p-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Sélectionner un appartement</option>
                    <?php foreach ($apartments as $apartment): ?>
                        <option value="<?php echo $apartment->getId(); ?>" <?php echo $apartment->getId() === $lease->getApartmentId() ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($apartment->getNumber()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="tenant_id" class="block text-sm font-medium text-gray-700">Locataire</label>
                <select name="tenant_id" id="tenant_id" class="w-full p-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Sélectionner un locataire</option>
                    <?php foreach ($tenants as $tenant): ?>
                        <option value="<?php echo $tenant->getId(); ?>" <?php echo $tenant->getId() === $lease->getTenantId() ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tenant->user()->getFirstName() . ' ' . $tenant->user()->getLastName()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Date de début</label>
                <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($lease->getStartDate()); ?>" class="w-full p-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">Date de fin (optionnel)</label>
                <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($lease->getEndDate()); ?>" class="w-full p-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="rent_amount" class="block text-sm font-medium text-gray-700">Loyer (FCFA)</label>
                <input type="number" name="rent_amount" id="rent_amount" step="0.01" value="<?php echo htmlspecialchars($lease->getRentAmount()); ?>" class="w-full p-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="charges_amount" class="block text-sm font-medium text-gray-700">Charges (FCFA)</label>
                <input type="number" name="charges_amount" id="charges_amount" step="0.01" value="<?php echo htmlspecialchars($lease->getChargesAmount()); ?>" class="w-full p-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="deposit_amount" class="block text-sm font-medium text-gray-700">Dépôt (FCFA)</label>
                <input type="number" name="deposit_amount" id="deposit_amount" step="0.01" value="<?php echo htmlspecialchars($lease->getDepositAmount()); ?>" class="w-full p-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="payment_frequency" class="block text-sm font-medium text-gray-700">Fréquence de paiement</label>
                <select name="payment_frequency" id="payment_frequency" class="w-full p-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="mensuel" <?php echo $lease->getPaymentFrequency() === 'mensuel' ? 'selected' : ''; ?>>Mensuel</option>
                    <option value="trimestriel" <?php echo $lease->getPaymentFrequency() === 'trimestriel' ? 'selected' : ''; ?>>Trimestriel</option>
                    <option value="annuel" <?php echo $lease->getPaymentFrequency() === 'annuel' ? 'selected' : ''; ?>>Annuel</option>
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
                <select name="status" id="status" class="w-full p-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="actif" <?php echo $lease->getStatus() === 'actif' ? 'selected' : ''; ?>>Actif</option>
                    <option value="terminé" <?php echo $lease->getStatus() === 'terminé' ? 'selected' : ''; ?>>Terminé</option>
                    <option value="annulé" <?php echo $lease->getStatus() === 'annulé' ? 'selected' : ''; ?>>Annulé</option>
                </select>
            </div>
        </div>
        <div class="mt-6">
            <button type="submit" class="btn-primary">Mettre à jour</button>
            <a href="/leases" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div>