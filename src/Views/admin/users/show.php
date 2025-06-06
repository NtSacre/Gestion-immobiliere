<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-construction-black">Détails de l’utilisateur</h3>
        <a href="/users" class="btn-secondary px-4 py-2 rounded-lg">Retour à la liste</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <p class="text-sm font-medium text-gray-600">Prénom</p>
            <p class="text-lg font-semibold text-construction-black"><?php echo htmlspecialchars($user_data['first_name']); ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Nom</p>
            <p class="text-lg font-semibold text-construction-black"><?php echo htmlspecialchars($user_data['last_name']); ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Email</p>
            <p class="text-lg font-semibold text-construction-black"><?php echo htmlspecialchars($user_data['email']); ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Téléphone</p>
            <p class="text-lg font-semibold text-construction-black"><?php echo htmlspecialchars($user_data['phone']); ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Rôle</p>
            <p class="text-lg font-semibold text-construction-black"><?php echo htmlspecialchars($user_data['role']); ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">ID de l’agence</p>
            <p class="text-lg font-semibold text-construction-black"><?php echo htmlspecialchars($user_data['agency_id']); ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Date de création</p>
            <p class="text-lg font-semibold text-construction-black"><?php echo htmlspecialchars($user_data['created_at']); ?></p>
        </div>
    </div>
    <div class="mt-6 flex justify-end space-x-3">
        <a href="/users/edit/<?php echo htmlspecialchars($user_data['id']); ?>" class="btn-primary px-4 py-2 rounded-lg">Modifier</a>
        <button onclick="openModal('confirmModal')" class="btn-danger px-4 py-2 rounded-lg">Supprimer</button>
    </div>
</div>

<div id="confirmModal" class="modal fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
        <div class="p-6">
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <span class="text-red-600 text-xl">⚠️</span>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-construction-black">Confirmer la suppression</h3>
                    <p class="text-sm text-gray-600">Cette action est irréversible</p>
                </div>
            </div>
            <p class="text-gray-700 mb-6">Êtes-vous sûr de vouloir supprimer cet utilisateur ?</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeModal('confirmModal')" class="btn-secondary px-4 py-2 rounded-lg">Annuler</button>
                <form action="/users/delete/<?php echo htmlspecialchars($user_data['id']); ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($helpers->csrf_generate('users.delete')); ?>">
                    <button type="submit" class="btn-danger px-4 py-2 rounded-lg">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}
</script>
