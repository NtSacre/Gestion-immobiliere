<div class="bg-white p-6 rounded-xl shadow-lg">
    <h3 class="text-xl font-bold text-construction-black mb-6">Modifier un appartement</h3>
    <form action="/agent/apartments/update/1" method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="building" class="block text-sm font-medium text-gray-700">Bâtiment</label>
                <select id="building" name="building" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
                    <option value="1" selected>Résidence Victor Hugo</option>
                    <option value="2">Tour Eiffel</option>
                </select>
            </div>
            <div>
                <label for="number" class="block text-sm font-medium text-gray-700">Numéro</label>
                <input type="text" id="number" name="number" value="3B" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
            </div>
            <div>
                <label for="floor" class="block text-sm font-medium text-gray-700">Étage</label>
                <input type="number" id="floor" name="floor" value="3" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
            </div>
            <div>
                <label for="surface" class="block text-sm font-medium text-gray-700">Surface (m²)</label>
                <input type="number" id="surface" name="surface" value="75" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
            </div>
            <div>
                <label for="rooms" class="block text-sm font-medium text-gray-700">Nombre de chambres</label>
                <input type="number" id="rooms" name="rooms" value="2" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
                <select id="status" name="status" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
                    <option value="disponible" selected>Disponible</option>
                    <option value="loue">Loué</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <a href="/agent/apartments" class="btn-secondary px-4 py-2 rounded-lg">Annuler</a>
            <button type="submit" class="btn-primary px-4 py-2 rounded-lg">Mettre à jour</button>
        </div>
    </form>
</div>