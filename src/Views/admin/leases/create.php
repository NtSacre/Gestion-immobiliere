<div class="bg-white p-6 rounded-xl shadow-lg">
    <h3 class="text-xl font-bold text-construction-black mb-6">Ajouter une location</h3>
    <form action="/agent/leases/store" method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="tenant" class="block text-sm font-medium text-gray-700">Locataire</label>
                <select id="tenant" name="tenant" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
                    <option value="1" selected>Sophie Durand</option>
                    <option value="2">Pierre Martin</option>
                </select>
            </div>
            <div>
                <label for="apartment" class="block text-sm font-medium text-gray-700">Appartement</label>
                <select id="apartment" name="apartment" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
                    <option value="1" selected>3B - Résidence Victor Hugo</option>
                    <option value="2">5A - Tour Eiffel</option>
                </select>
            </div>
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Date de début</label>
                <input type="date" id="start_date" name="start_date" value="2025-01-01" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">Date de fin</label>
                <input type="date" id="end_date" name="end_date" value="2026-01-01" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
            </div>
            <div>
                <label for="rent" class="block text-sm font-medium text-gray-700">Loyer (€)</label>
                <input type="number" id="rent" name="rent" value="1200" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
                <select id="status" name="status" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
                    <option value="actif" selected>Actif</option>
                    <option value="termine">Terminé</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <a href="/agent/leases" class="btn-secondary px-4 py-2 rounded-lg">Annuler</a>
            <button type="submit" class="btn-primary px-4 py-2 rounded-lg">Enregistrer</button>
        </div>
    </form>
</div>