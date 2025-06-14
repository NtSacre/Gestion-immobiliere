<?php
$title = 'Détails du bail';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-construction-black mb-6">Détails du bail</h1>

    <!-- Messages flash -->
    <?php if ($flash = $this->flash->get('success')): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-xl">
            <?php echo htmlspecialchars($flash); ?>
        </div>
    <?php elseif ($flash = $this->flash->get('error')): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-xl">
            <?php echo htmlspecialchars($flash); ?>
        </div>
    <?php endif; ?>

    <!-- Détails du bail -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm font-medium text-gray-700">Appartement</p>
                <p class="text-lg"><?php echo htmlspecialchars($lease_data['apartment']); ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Locataire</p>
                <p class="text-lg"><?php echo htmlspecialchars($lease_data['tenant']); ?></p>
            </div>
            <div>
 chicks
                <p class="text-sm font-medium text-gray-700">Date de début</p>
                <p class="text-lg"><?php echo htmlspecialchars($lease_data['start_date']); ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Date de fin</p>
                <p class="text-lg"><?php echo htmlspecialchars($lease_data['end_date']); ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Loyer</p>
                <p class="text-lg"><?php echo htmlspecialchars($lease_data['rent_amount']); ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Charges</p>
                <p class="text-lg"><?php echo htmlspecialchars($lease_data['charges_amount']); ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Dépôt</p>
                <p class="text-lg"><?php echo htmlspecialchars($lease_data['deposit_amount']); ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Fréquence de paiement</p>
                <p class="text-lg"><?php echo htmlspecialchars($lease_data['payment_frequency']); ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Statut</p>
                <p class="text-lg"><?php echo htmlspecialchars($lease_data['status']); ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Créé le</p>
                <p class="text-lg"><?php echo htmlspecialchars($lease_data['created_at']); ?></p>
            </div>
        </div>
        <div class="mt-6">
            <a href="/leases" class="btn-primary">Retour</a>
            <?php if (in_array($role, ['superadmin', 'admin', 'agent'])): ?>
                <a href="/leases/edit/<?php echo $lease_data['id']; ?>" class="btn-primary">Modifier</a>
                <button onclick="openDeleteModal(<?php echo $lease_data['id']; ?>, 'leases.delete')" class="btn-danger">Supprimer</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function openDeleteModal(id, formId) {
    const modal = document.getElementById('global-delete-modal');
    const form = document.getElementById('delete-form');
    form.action = `/leases/delete/${id}`;
    form.querySelector('input[name="csrf_token"]').value = '<?php echo $this->helpers->csrf_token('leases.delete'); ?>';
    modal.classList.remove('hidden');
}
</script>