<div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-lg card-hover">
    <h2 class="text-2xl font-bold text-construction-black mb-6 text-center">Inscription Agence</h2>
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
    <form method="POST" action="/register" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <div>
            <label for="agency_name" class="block text-sm font-medium text-gray-700">Nom de l'agence</label>
            <input type="text" name="agency_name" id="agency_name" required
                   class="mt-1 w-full p-3 border border-gray-300 rounded-lg input-focus">
        </div>
        <div>
            <label for="address" class="block text-sm font-medium text-gray-700">Adresse de l'agence (facultatif)</label>
            <textarea name="address" id="address"
                      class="mt-1 w-full p-3 border border-gray-300 rounded-lg input-focus"></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="agency_phone" class="block text-sm font-medium text-gray-700">Téléphone de l'agence (facultatif)</label>
                <input type="tel" name="agency_phone" id="agency_phone"
                       class="mt-1 w-full p-3 border border-gray-300 rounded-lg input-focus">
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone (admin, facultatif)</label>
                <input type="tel" name="phone" id="phone"
                       class="mt-1 w-full p-3 border border-gray-300 rounded-lg input-focus">
            </div>
        </div>
        <div>
            <label for="siret" class="block text-sm font-medium text-gray-700">SIRET (facultatif)</label>
            <input type="text" name="siret" id="siret"
                   class="mt-1 w-full p-3 border border-gray-300 rounded-lg input-focus">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Nom d'utilisateur</label>
                <input type="text" name="username" id="username" required
                       class="mt-1 w-full p-3 border border-gray-300 rounded-lg input-focus">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email (agence et admin)</label>
                <input type="email" name="email" id="email" required
                       class="mt-1 w-full p-3 border border-gray-300 rounded-lg input-focus">
            </div>
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
            <input type="password" name="password" id="password" required
                   class="mt-1 w-full p-3 border border-gray-300 rounded-lg input-focus">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700">Prénom (admin)</label>
                <input type="text" name="first_name" id="first_name" required
                       class="mt-1 w-full p-3 border border-gray-300 rounded-lg input-focus">
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700">Nom (admin)</label>
                <input type="text" name="last_name" id="last_name" required
                       class="mt-1 w-full p-3 border border-gray-300 rounded-lg input-focus">
            </div>
        </div>
        <button type="submit" class="w-full btn-primary px-4 py-3 rounded-lg">S'inscrire</button>
    </form>
    <p class="mt-4 text-center text-sm text-gray-600">
        Déjà un compte ? <a href="/auth/login" class="text-orange-500 hover:text-orange-600 hover:underline">Connectez-vous</a>
    </p>
</div>