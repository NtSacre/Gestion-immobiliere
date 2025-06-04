<?php namespace App\Views; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ImmoApp - Gestion Immobilière Professionnelle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'construction-yellow': '#FFD700',
                        'construction-dark-yellow': '#FFC107',
                        'construction-black': '#1A1A1A',
                        'construction-gray': '#2D2D2D'
                    }
                }
            }
        }
    </script>
    <style>
        html {
  scroll-behavior: smooth;
}
        .gradient-yellow {
            background: linear-gradient(135deg, #FFD700 0%, #FFC107 100%);
        }
        .shadow-construction {
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.2);
        }
        .hover-lift {
            transition: all 0.3s ease;
        }
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(255, 215, 0, 0.3);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Navigation -->
    <nav class="bg-construction-black shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 gradient-yellow rounded-lg flex items-center justify-center">
                        <span class="text-construction-black font-bold text-xl">I</span>
                    </div>
                    <a href="/?route=" class="text-2xl font-bold text-white">ImmoApp</a>
                </div>
                <div class="hidden md:flex space-x-6">
                    <a href="#features" class="text-gray-300 hover:text-construction-yellow transition-colors">Fonctionnalités</a>
                    <a href="#advantages" class="text-gray-300 hover:text-construction-yellow transition-colors">Avantages</a>
                    <a href="#contact" class="text-gray-300 hover:text-construction-yellow transition-colors">Contact</a>
                </div>
                <div class="space-x-3">
                    <a href="/auth/login" class="text-gray-300 hover:text-construction-yellow transition-colors px-4 py-2">Connexion</a>
                    <a href="/auth/register" class="gradient-yellow text-construction-black px-6 py-2 rounded-lg font-semibold hover:bg-construction-dark-yellow transition-all duration-300 shadow-md">Inscription</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative bg-construction-black text-white py-24 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 w-32 h-32 gradient-yellow rounded-full"></div>
            <div class="absolute bottom-20 right-20 w-24 h-24 gradient-yellow rounded-full"></div>
            <div class="absolute top-1/2 left-1/4 w-16 h-16 gradient-yellow rounded-full"></div>
        </div>
        <div class="container mx-auto px-4 relative">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-5xl md:text-6xl font-bold mb-6 leading-tight">
                    La <span class="text-construction-yellow">Solution Pro</span><br>
                    pour votre Gestion Immobilière
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-gray-300 leading-relaxed">
                    Optimisez la gestion de vos biens immobiliers avec des outils professionnels conçus pour les agences modernes
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/?route=register" class="gradient-yellow text-construction-black px-8 py-4 rounded-lg text-lg font-bold hover:scale-105 transition-all duration-300 shadow-construction">
                        Démarrer gratuitement
                    </a>
                    <a href="#demo" class="border-2 border-construction-yellow text-construction-yellow px-8 py-4 rounded-lg text-lg font-semibold hover:bg-construction-yellow hover:text-construction-black transition-all duration-300">
                        Voir la démo
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistiques -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
                <div class="hover-lift bg-gray-50 p-6 rounded-lg">
                    <div class="text-4xl font-bold text-construction-yellow mb-2">500+</div>
                    <div class="text-gray-600">Agences partenaires</div>
                </div>
                <div class="hover-lift bg-gray-50 p-6 rounded-lg">
                    <div class="text-4xl font-bold text-construction-yellow mb-2">15K+</div>
                    <div class="text-gray-600">Biens gérés</div>
                </div>
                <div class="hover-lift bg-gray-50 p-6 rounded-lg">
                    <div class="text-4xl font-bold text-construction-yellow mb-2">98%</div>
                    <div class="text-gray-600">Satisfaction client</div>
                </div>
                <div class="hover-lift bg-gray-50 p-6 rounded-lg">
                    <div class="text-4xl font-bold text-construction-yellow mb-2">24/7</div>
                    <div class="text-gray-600">Support technique</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Fonctionnalités principales -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-construction-black mb-4">Fonctionnalités Professionnelles</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Des outils puissants pour révolutionner votre gestion immobilière</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="hover-lift bg-white p-8 rounded-xl shadow-lg border-l-4 border-construction-yellow">
                    <div class="w-12 h-12 gradient-yellow rounded-lg flex items-center justify-center mb-4">
                        <span class="text-construction-black font-bold text-xl">🏢</span>
                    </div>
                    <h3 class="text-2xl font-bold text-construction-black mb-4">Gestion de Patrimoine</h3>
                    <p class="text-gray-600 leading-relaxed">Centralisez la gestion de tous vos biens immobiliers avec un tableau de bord unifié et des rapports détaillés.</p>
                </div>
                <div class="hover-lift bg-white p-8 rounded-xl shadow-lg border-l-4 border-construction-yellow">
                    <div class="w-12 h-12 gradient-yellow rounded-lg flex items-center justify-center mb-4">
                        <span class="text-construction-black font-bold text-xl">📄</span>
                    </div>
                    <h3 class="text-2xl font-bold text-construction-black mb-4">Quittances Automatisées</h3>
                    <p class="text-gray-600 leading-relaxed">Génération automatique de quittances professionnelles en PDF avec envoi par email intégré.</p>
                </div>
                <div class="hover-lift bg-white p-8 rounded-xl shadow-lg border-l-4 border-construction-yellow">
                    <div class="w-12 h-12 gradient-yellow rounded-lg flex items-center justify-center mb-4">
                        <span class="text-construction-black font-bold text-xl">📊</span>
                    </div>
                    <h3 class="text-2xl font-bold text-construction-black mb-4">Analytics Avancés</h3>
                    <p class="text-gray-600 leading-relaxed">Suivez vos performances avec des tableaux de bord interactifs et des métriques en temps réel.</p>
                </div>
                <div class="hover-lift bg-white p-8 rounded-xl shadow-lg border-l-4 border-construction-yellow">
                    <div class="w-12 h-12 gradient-yellow rounded-lg flex items-center justify-center mb-4">
                        <span class="text-construction-black font-bold text-xl">👥</span>
                    </div>
                    <h3 class="text-2xl font-bold text-construction-black mb-4">Gestion Locataires</h3>
                    <p class="text-gray-600 leading-relaxed">Interface dédiée pour les locataires avec accès sécurisé à leurs documents et historiques.</p>
                </div>
                <div class="hover-lift bg-white p-8 rounded-xl shadow-lg border-l-4 border-construction-yellow">
                    <div class="w-12 h-12 gradient-yellow rounded-lg flex items-center justify-center mb-4">
                        <span class="text-construction-black font-bold text-xl">🔧</span>
                    </div>
                    <h3 class="text-2xl font-bold text-construction-black mb-4">Maintenance Intégrée</h3>
                    <p class="text-gray-600 leading-relaxed">Planification et suivi des interventions de maintenance avec notifications automatiques.</p>
                </div>
                <div class="hover-lift bg-white p-8 rounded-xl shadow-lg border-l-4 border-construction-yellow">
                    <div class="w-12 h-12 gradient-yellow rounded-lg flex items-center justify-center mb-4">
                        <span class="text-construction-black font-bold text-xl">📱</span>
                    </div>
                    <h3 class="text-2xl font-bold text-construction-black mb-4">Application Mobile</h3>
                    <p class="text-gray-600 leading-relaxed">Accès complet depuis votre smartphone pour une gestion nomade de vos biens.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Avantages -->
    <section id="advantages" class="py-20 bg-construction-black text-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Pourquoi les Professionnels nous choisissent</h2>
                <p class="text-xl text-gray-300 max-w-3xl mx-auto">Une solution complète qui s'adapte à votre métier</p>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-8">
                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 gradient-yellow rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                            <span class="text-construction-black font-bold">✓</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-construction-yellow mb-2">Gain de temps considérable</h3>
                            <p class="text-gray-300">Automatisez 80% de vos tâches répétitives et concentrez-vous sur le développement de votre activité.</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 gradient-yellow rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                            <span class="text-construction-black font-bold">✓</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-construction-yellow mb-2">Conformité garantie</h3>
                            <p class="text-gray-300">Respectez automatiquement toutes les réglementations en vigueur avec nos modèles juridiques à jour.</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 gradient-yellow rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                            <span class="text-construction-black font-bold">✓</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-construction-yellow mb-2">Évolutif avec votre croissance</h3>
                            <p class="text-gray-300">De 10 à 10 000 biens, notre solution s'adapte à la taille de votre portefeuille.</p>
                        </div>
                    </div>
                </div>
                <div class="bg-construction-gray p-8 rounded-xl">
                    <h3 class="text-2xl font-bold text-construction-yellow mb-6">Témoignage</h3>
                    <blockquote class="text-lg italic mb-4">
                        "ImmoApp a révolutionné notre façon de travailler. Nous avons réduit nos tâches administratives de 70% et amélioré notre relation client."
                    </blockquote>
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 gradient-yellow rounded-full flex items-center justify-center">
                            <span class="text-construction-black font-bold">MC</span>
                        </div>
                        <div>
                            <div class="font-semibold">Marie Dubois</div>
                            <div class="text-gray-400">Directrice, Immobilier Plus</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Final -->
    <section class="py-20 gradient-yellow">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold text-construction-black mb-6">Prêt à transformer votre gestion immobilière ?</h2>
            <p class="text-xl text-construction-black mb-8 max-w-2xl mx-auto">
                Rejoignez les centaines d'agences qui font confiance à ImmoApp pour optimiser leur activité.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/?route=register" class="bg-construction-black text-construction-yellow px-8 py-4 rounded-lg text-lg font-bold hover:bg-construction-gray transition-all duration-300 shadow-lg">
                    Essai gratuit 30 jours
                </a>
                <a href="#contact" class="border-2 border-construction-black text-construction-black px-8 py-4 rounded-lg text-lg font-semibold hover:bg-construction-black hover:text-construction-yellow transition-all duration-300">
                    Demander une démo
                </a>
            </div>
            <p class="text-sm text-construction-gray mt-4">Aucun engagement • Configuration en 5 minutes</p>
        </div>
    </section>
    <!-- Section Contact -->
<section id="contact" class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-construction-black mb-4">Contactez-nous</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">Notre équipe d'experts est là pour vous accompagner dans votre projet</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Formulaire de contact -->
            <div class="bg-gray-50 p-8 rounded-xl shadow-lg">
                <h3 class="text-2xl font-bold text-construction-black mb-6">Demandez une démonstration</h3>
                <form class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Prénom *</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-construction-yellow focus:border-transparent transition-all" placeholder="Votre prénom">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nom *</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-construction-yellow focus:border-transparent transition-all" placeholder="Votre nom">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email professionnel *</label>
                        <input type="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-construction-yellow focus:border-transparent transition-all" placeholder="vous@votre-agence.com">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Téléphone</label>
                        <input type="tel" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-construction-yellow focus:border-transparent transition-all" placeholder="+33 1 23 45 67 89">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nom de votre agence *</label>
                        <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-construction-yellow focus:border-transparent transition-all" placeholder="Nom de votre agence">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre de biens gérés</label>
                        <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-construction-yellow focus:border-transparent transition-all">
                            <option>Sélectionnez...</option>
                            <option>1-10 biens</option>
                            <option>11-50 biens</option>
                            <option>51-200 biens</option>
                            <option>201-500 biens</option>
                            <option>Plus de 500 biens</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Message</label>
                        <textarea rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-construction-yellow focus:border-transparent transition-all" placeholder="Décrivez-nous vos besoins..."></textarea>
                    </div>
                    
                    <button type="submit" class="w-full gradient-yellow text-construction-black px-6 py-4 rounded-lg text-lg font-bold hover:scale-105 transition-all duration-300 shadow-construction">
                        Demander une démonstration
                    </button>
                    
                    <p class="text-sm text-gray-500 text-center">
                        * Champs obligatoires. Vos données sont protégées et ne seront jamais partagées.
                    </p>
                </form>
            </div>
            
            <!-- Informations de contact -->
            <div class="space-y-8">
                <div class="bg-construction-black p-8 rounded-xl text-white">
                    <h3 class="text-2xl font-bold text-construction-yellow mb-6">Parlons de votre projet</h3>
                    <p class="text-gray-300 mb-6 leading-relaxed">
                        Nos experts vous accompagnent dans la mise en place de votre solution de gestion immobilière. 
                        Planifiez un rendez-vous pour découvrir comment ImmoApp peut transformer votre activité.
                    </p>
                    
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 gradient-yellow rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="text-construction-black font-bold text-xl">📞</span>
                            </div>
                            <div>
                                <div class="font-semibold text-construction-yellow">Téléphone</div>
                                <div class="text-gray-300">+33 1 23 45 67 89</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 gradient-yellow rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="text-construction-black font-bold text-xl">📧</span>
                            </div>
                            <div>
                                <div class="font-semibold text-construction-yellow">Email</div>
                                <div class="text-gray-300">contact@immoapp.fr</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 gradient-yellow rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="text-construction-black font-bold text-xl">🕒</span>
                            </div>
                            <div>
                                <div class="font-semibold text-construction-yellow">Horaires</div>
                                <div class="text-gray-300">Lun-Ven: 9h-18h</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 gradient-yellow rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="text-construction-black font-bold text-xl">📍</span>
                            </div>
                            <div>
                                <div class="font-semibold text-construction-yellow">Adresse</div>
                                <div class="text-gray-300">123 Rue de la Gestion<br>75001 Paris, France</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Points forts -->
                <div class="bg-gray-50 p-6 rounded-xl">
                    <h4 class="font-bold text-construction-black mb-4">Pourquoi nous contacter ?</h4>
                    <ul class="space-y-3">
                        <li class="flex items-start space-x-3">
                            <div class="w-6 h-6 gradient-yellow rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-construction-black font-bold text-sm">✓</span>
                            </div>
                            <span class="text-gray-700">Démonstration personnalisée de 30 minutes</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <div class="w-6 h-6 gradient-yellow rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-construction-black font-bold text-sm">✓</span>
                            </div>
                            <span class="text-gray-700">Conseils d'experts gratuits</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <div class="w-6 h-6 gradient-yellow rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-construction-black font-bold text-sm">✓</span>
                            </div>
                            <span class="text-gray-700">Devis personnalisé sous 24h</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <div class="w-6 h-6 gradient-yellow rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-construction-black font-bold text-sm">✓</span>
                            </div>
                            <span class="text-gray-700">Support dédié pendant l'implémentation</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Footer -->
    <footer class="bg-construction-black text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-8 h-8 gradient-yellow rounded-lg flex items-center justify-center">
                            <span class="text-construction-black font-bold">I</span>
                        </div>
                        <span class="text-xl font-bold">ImmoApp</span>
                    </div>
                    <p class="text-gray-400">La solution professionnelle pour la gestion immobilière moderne.</p>
                </div>
                <div>
                    <h4 class="font-semibold text-construction-yellow mb-4">Produit</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-construction-yellow transition-colors">Fonctionnalités</a></li>
                        <li><a href="#" class="hover:text-construction-yellow transition-colors">Tarifs</a></li>
                        <li><a href="#" class="hover:text-construction-yellow transition-colors">API</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-construction-yellow mb-4">Support</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-construction-yellow transition-colors">Documentation</a></li>
                        <li><a href="#" class="hover:text-construction-yellow transition-colors">Contact</a></li>
                        <li><a href="#" class="hover:text-construction-yellow transition-colors">Formation</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-construction-yellow mb-4">Entreprise</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-construction-yellow transition-colors">À propos</a></li>
                        <li><a href="#" class="hover:text-construction-yellow transition-colors">Carrières</a></li>
                        <li><a href="#" class="hover:text-construction-yellow transition-colors">Presse</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>© 2025 ImmoApp. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
</body>
</html>