<div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-lg card-hover">
    <h3 class="text-2xl font-bold text-construction-black mb-4 text-center">Erreur 403 - Accès interdit</h3>
    <?php if ($flash->has('error')): ?>
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
            <?php echo htmlspecialchars($flash->get('error')); ?>
        </div>
    <?php endif; ?>
    <p class="text-gray-600 mb-6 text-center"><?php echo htmlspecialchars($error_message ?: 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.'); ?></p>
    <div class="flex justify-center">
        <a href="<?php echo $auth->check() ? '/dashboard' : '/'; ?>" class="btn-primary px-6 py-3 rounded-lg">Retour à <?php echo $auth->check() ? 'votre tableau de bord' : 'l\'accueil'; ?></a>
    </div>
</div>