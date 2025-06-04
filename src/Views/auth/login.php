<div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-lg card-hover">
    <h2 class="text-2xl font-bold text-construction-black mb-6 text-center">Connexion</h2>
    <?php if ($error = \App\Utils\flash('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    <?php if ($success = \App\Utils\flash('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="/login" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" id="email" required
                   class="mt-1 w-full p-3 border border-gray-300 rounded-lg input-focus">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
        <input type="password" name="password" id="password" required
               class="mt-1 w-full p-3 border border-gray-300 rounded-lg input-focus">
        </div>
        <button type="submit" class="w-full btn-primary px-4 py-3 rounded-lg">Se connecter</button>
    </form>
    <p class="mt-4 text-center text-sm text-gray-600">
        Pas de compte ? <a href="/auth/register" class="text-orange-500 hover:text-orange-600 hover:underline">Inscrivez-vous</a>
    </p>
</div>