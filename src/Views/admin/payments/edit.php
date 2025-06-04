<div class="bg-white p-6 rounded-xl shadow-lg">
    <h3 class="text-xl font-bold text-construction-black mb-6">Modifier un paiement</h3>
    <form action="/agent/payments/update/1" method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="lease" class="block text-sm font-medium text-gray-700">Location</label>
                <select id="lease" name="lease" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
                    <option value="1" selected>Sophie Durand - 3B - Résidence Victor Hugo</option>
                    <option value="2">Pierre Martin - 5A - Tour Eiffel</option>
                </select>
            </div>
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700">Montant (€)</label>
                <input type="number" id="amount" name="amount" value="1200" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
            </div>
            <div>
                <label for="payment_date" class="block text-sm font-medium text-gray-700">Date de paiement</label>
                <input type="date" id="payment_date" name="payment_date" value="2025-06-01" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
                <select id="status" name="status" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg input-focus" readonly>
                    <option value="paye" selected>Payé</option>
                    <option value="en_attente">En attente</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <a href="/agent/payments" class="btn-secondary px-4 py-2 rounded-lg">Annuler</a>
            <button type="submit" class="btn-primary px-4 py-2 rounded-lg">Mettre à jour</button>
        </div>
    </form>
</div>