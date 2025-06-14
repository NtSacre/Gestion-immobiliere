<?php
$title = 'Liste des baux';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-construction-black mb-6">Gestion des baux</h1>

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

    <!-- Barre de recherche -->
    <div class="mb-6">
        <form method="GET" action="/leases" class="flex items-center gap-4">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Rechercher un bail..." class="w-full p-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="btn-primary">Rechercher</button>
        </form>
    </div>

    <!-- Bouton de création (selon le rôle) -->
    <?php if (in_array($role, ['superadmin', 'admin', 'agent'])): ?>
        <div class="mb-6">
            <a href="/leases/create" class="btn-primary">Ajouter un bail</a>
        </div>
    <?php endif; ?>

    <!-- Tableau des baux -->
    <div class="bg-white rounded-xl shadow-lg overflow-x-auto">
        <table class="w-full table-auto">
            <thead>
                <tr class="bg-gray-100 text-left text-sm font-semibold text-construction-black">
                    <th class="p-4">Appartement</th>
                    <th class="p-4">Locataire</th>
                    <th class="p-4">Loyer</th>
                    <th class="p-4">Statut</th>
                    <th class="p-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leases)): ?>
                    <tr>
                        <td colspan="5" class="p-4 text-center text-gray-500">Aucun bail trouvé.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($leases as $lease): ?>
                        <tr class="border-t">
                            <td class="p-4"><?php echo htmlspecialchars($lease->apartment()->number ?? 'Non spécifié'); ?></td>
                            <td class="p-4"><?php echo htmlspecialchars($lease->tenant()->user()->getFirstName() . ' ' . $lease->tenant()->user()->getLastName()); ?></td>
                            <td class="p-4"><?php echo number_format($lease->getRentAmount(), 2) . ' FCFA'; ?></td>
                            <td class="p-4"><?php echo htmlspecialchars($this->formatStatus($lease->getStatus())); ?></td>
                            <td class="p-4 flex gap-2">
                                <a href="/leases/show/<?php echo $lease->getId(); ?>" class="btn-primary">Voir</a>
                                <?php if (in_array($role, ['superadmin', 'admin', 'agent'])): ?>
                                    <a href="/leases/edit/<?php echo $lease->getId(); ?>" class="btn-primary">Modifier</a>
                                    <button onclick="openDeleteModal(<?php echo $lease->getId(); ?>, 'leases.delete')" class="btn-danger">Supprimer</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="mt-6 flex justify-center gap-2">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="/leases?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="px-4 py-2 rounded-xl <?php echo $i === $page ? 'bg-blue-500 text-white' : 'bg-gray-200'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
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