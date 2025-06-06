<?php ob_start(); ?>

<div class="container mx-auto px-4 py-8">
    <!-- Métriques -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php 
        $colors = [
            'chiffre_affaires' => 'border-teal-500',
            'baux_actifs' => 'border-blue-500',
            'appartements_disponibles' => 'border-green-500',
            'batiments' => 'border-construction-yellow',
            'agents' => 'border-indigo-500',
            'proprietaires' => 'border-red-500',
            'locataires' => 'border-purple-500',
            'agences' => 'border-orange-500',
            'acheteurs' => 'border-pink-500',
            'revenus' => 'border-teal-500',
            'appartements_loues' => 'border-blue-500',
            'nouveaux_locataires' => 'border-purple-500',
            'loyer' => 'border-teal-500',
            'appartements_occupes' => 'border-green-500',
            'paiements_effectues' => 'border-blue-500',
            'nouveaux_appartements' => 'border-green-500',
            'baux_associes' => 'border-blue-500'
        ];
        $bgColors = [
            'chiffre_affaires' => 'bg-teal-100',
            'baux_actifs' => 'bg-blue-100',
            'appartements_disponibles' => 'bg-green-100',
            'batiments' => 'gradient-yellow',
            'agents' => 'bg-indigo-100',
            'proprietaires' => 'bg-red-100',
            'locataires' => 'bg-purple-100',
            'agences' => 'bg-orange-100',
            'acheteurs' => 'bg-pink-100',
            'revenus' => 'bg-teal-100',
            'appartements_loues' => 'bg-blue-100',
            'nouveaux_locataires' => 'bg-purple-100',
            'loyer' => 'bg-teal-100',
            'appartements_occupes' => 'bg-green-100',
            'paiements_effectues' => 'bg-blue-100',
            'nouveaux_appartements' => 'bg-green-100',
            'baux_associes' => 'bg-blue-100'
        ];
        $textColors = [
            'chiffre_affaires' => 'text-teal-600',
            'baux_actifs' => 'text-blue-600',
            'appartements_disponibles' => 'text-green-600',
            'batiments' => 'text-construction-black',
            'agents' => 'text-indigo-600',
            'proprietaires' => 'text-red-600',
            'locataires' => 'text-purple-600',
            'agences' => 'text-orange-600',
            'acheteurs' => 'text-pink-600',
            'revenus' => 'text-teal-600',
            'appartements_loues' => 'text-blue-600',
            'nouveaux_locataires' => 'text-purple-600',
            'loyer' => 'text-teal-600',
            'appartements_occupes' => 'text-green-600',
            'paiements_effectues' => 'text-blue-600',
            'nouveaux_appartements' => 'text-green-600',
            'baux_associes' => 'text-blue-600'
        ];

        // Afficher les métriques principales
        foreach ($metrics['primary'] as $key => $metric): ?>
            <div class="card-hover bg-white p-6 rounded-xl shadow-lg border-l-4 <?= htmlspecialchars($colors[$key] ?? 'border-gray-500') ?>">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium"><?= htmlspecialchars($metric['label']) ?></p>
                        <p class="text-2xl font-bold text-construction-black"><?= htmlspecialchars($metric['value']) ?></p>
                    </div>
                    <div class="w-12 h-12 <?= htmlspecialchars($bgColors[$key] ?? 'bg-gray-100') ?> rounded-lg flex items-center justify-center">
                        <span class="<?= htmlspecialchars($textColors[$key] ?? 'text-gray-600') ?> font-bold text-xl" aria-hidden="true"><?= htmlspecialchars($metric['emoji']) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Afficher les métriques secondaires (overview) -->
        <?php foreach ($metrics['overview'] ?? [] as $key => $metric): ?>
            <div class="card-hover bg-white p-6 rounded-xl shadow-lg border-l-4 <?= htmlspecialchars($colors[$key] ?? 'border-gray-500') ?>">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium"><?= htmlspecialchars($metric['label']) ?></p>
                        <p class="text-2xl font-bold text-construction-black"><?= htmlspecialchars($metric['value']) ?></p>
                    </div>
                    <div class="w-12 h-12 <?= htmlspecialchars($bgColors[$key] ?? 'bg-gray-100') ?> rounded-lg flex items-center justify-center">
                        <span class="<?= htmlspecialchars($textColors[$key] ?? 'text-gray-600') ?> font-bold text-xl" aria-hidden="true"><?= htmlspecialchars($metric['emoji']) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Activité récente et Actions rapides -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-6">
        <!-- Activité récente -->
        <div class="xl:col-span-2 bg-white p-6 rounded-xl shadow-lg">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-construction-black">Activité récente</h3>
                <a href="/admin/audit-logs" class="text-construction-yellow font-semibold hover:underline" aria-label="Voir toutes les activités">Voir tout</a>
            </div>
            <div class="space-y-4">
                <?php if (empty($activities)): ?>
                    <p class="text-gray-600 text-sm">Aucune activité récente.</p>
                <?php else: ?>
                    <?php foreach ($activities as $activity): ?>
                        <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-bold text-base" aria-hidden="true">📋</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-base">
                                    <?= htmlspecialchars(ucfirst($activity['action'])) ?> sur <?= htmlspecialchars(strtolower($activity['table_name'])) ?>
                                </p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars(date('d M Y à H:i', strtotime($activity['created_at']))) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions rapides et Tâches urgentes -->
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <?php if ($showActions): ?>
                <h3 class="text-xl font-bold text-construction-black mb-6">Actions rapides</h3>
                <div class="space-y-3">
                    <a href="/admin/users/create?role=agent" class="w-full btn-primary-yellow p-3 rounded-lg font-semibold hover:scale-105 transition-all flex items-center justify-center space-x-2" aria-label="Ajouter un agent">
                        <span>👥</span><span>Ajouter un agent</span>
                    </a>
                    <a href="/admin/apartments" class="w-full btn-secondary p-3 rounded-lg font-semibold hover:bg-gray-200 transition-all flex items-center justify-center space-x-2" aria-label="Gérer les appartements">
                        <span>🏠</span><span>Gérer les appartements</span>
                    </a>
                    <a href="/admin/leases" class="w-full btn-secondary p-3 rounded-lg font-semibold hover:bg-gray-200 transition-all flex items-center justify-center space-x-2" aria-label="Valider les baux">
                        <span>📄</span><span>Valider les baux</span>
                    </a>
                </div>
            <?php endif; ?>

            <?php if (!empty($tasks)): ?>
                <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <h4 class="font-semibold text-red-800 mb-2">Tâches urgentes</h4>
                    <div class="space-y-2 text-sm">
                        <?php foreach ($tasks as $task): ?>
                            <?php if ($task['value'] > 0): ?>
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                    <span class="text-red-700"><?= htmlspecialchars($task['value']) ?> <?= htmlspecialchars($task['label']) ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
echo $content;
?>