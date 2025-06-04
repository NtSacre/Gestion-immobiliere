<div class="bg-white p-6 rounded-xl shadow-lg">
    <h3 class="text-xl font-bold text-construction-black mb-6">Ajouter un bâtiment</h3>
    <form action="/agent/buildings/store" method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" id="nom" name="nom" value="Résidence Victor Hugo" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
            </div>
            <div>
                <label for="ville" class="block text-sm font-medium text-gray-700">Ville</label>
                <input type="text" id="ville" name="ville" value="Paris" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
            </div>
            <div>
                <label for="proprietaire" class="block text-sm font-medium text-gray-700">Propriétaire</label>
                <select id="proprietaire" name="proprietaire" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
                    <option value="1" selected>Jean Dupont</option>
                    <option value="2">Marie Martin</option>
                </select>
            </div>
            <div>
                <label for="agence" class="block text-sm font-medium text-gray-700">Agence</label>
                <select id="agence" name="agence" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
                    <option value="1" selected>Agence Paris</option>
                    <option value="2">Agence Lyon</option>
                </select>
            </div>
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                <select id="type" name="type" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
                    <option value="residentiel" selected>Résidentiel</option>
                    <option value="commercial">Commercial</option>
                </select>
            </div>
            <div>
                <label for="etages" class="block text-sm font-medium text-gray-700">Nombre d'étages</label>
                <input type="number" id="etages" name="etages" value="5" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
            </div>
            <div>
                <label for="appartements" class="block text-sm font-medium text-gray-700">Nombre d'appartements</label>
                <input type="number" id="appartements" name="appartements" value="20" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
            </div>
            <div>
                <label for="statut" class="block text-sm font-medium text-gray-700">Statut</label>
                <select id="statut" name="statut" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
                    <option value="actif" selected>Actif</option>
                    <option value="inactif">Inactif</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <a href="/agent/buildings" class="btn-secondary px-4 py-2 rounded-lg">Annuler</a>
            <button type="submit" class="btn-primary px-4 py-2 rounded-lg">Enregistrer</button>
        </div>
    </form>
</div>