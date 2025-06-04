<div class="bg-white p-6 rounded-xl shadow-lg">
    <h3 class="text-xl font-bold text-construction-black mb-6">Ajouter un locataire</h3>
    <form action="/agent/tenants/store" method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700">Prénom</label>
                <input type="text" id="first_name" name="first_name" value="Sophie" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" id="last_name" name="last_name" value="Durand" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" value="sophie.durand@email.com" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                <input type="text" id="phone" name="phone" value="+33 6 12 34 56 78" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
                <select id="status" name="status" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
                    <option value="actif" selected>Actif</option>
                    <option value="inactif">Inactif</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <a href="/agent/tenants" class="btn-secondary px-4 py-2 rounded-lg">Annuler</a>
            <button type="submit" class="btn-primary px-4 py-2 rounded-lg">Enregistrer</button>
        </div>
    </form>
</div>