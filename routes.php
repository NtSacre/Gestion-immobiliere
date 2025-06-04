<?php
// routes.php
// Définit les routes de l'application, mappant les URLs aux contrôleurs et actions.
// Organisé par rôle : publiques (tous), agent (agent immobilier).
// Chaque route inclut le contrôleur, l'action, la méthode HTTP et un nom pour générer des URLs.

$routes = [
    // Routes publiques
    '' => [
        'controller' => 'App\Controllers\Public\HomeController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'home'
    ],
    'auth/login' => [
        'controller' => 'App\Controllers\Public\AuthController',
        'action' => 'showLogin',
        'method' => 'GET',
        'name' => 'auth.login'
    ],
    'login' => [
        'controller' => 'App\Controllers\Public\AuthController',
        'action' => 'login',
        'method' => 'POST',
        'name' => 'auth.login.post'
    ],
    'auth/register' => [
        'controller' => 'App\Controllers\Public\AuthController',
        'action' => 'showRegister',
        'method' => 'GET',
        'name' => 'auth.register'
    ],
    'register' => [
        'controller' => 'App\Controllers\Public\AuthController',
        'action' => 'register',
        'method' => 'POST',
        'name' => 'auth.register.post'
    ],
    'logout' => [
        'controller' => 'App\Controllers\Public\AuthController',
        'action' => 'logout',
        'method' => 'GET',
        'name' => 'auth.logout'
    ],

    // Route admin
    'admin/dashboard' => [
    'controller' => 'App\Controllers\DashboardController',
    'action' => 'index',
    'method' => 'GET',
    'name' => 'admin.dashboard'
    ],

    // Routes agent
    'agent/dashboard' => [
        'controller' => 'App\Controllers\DashboardController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'agent.dashboard'
    ],

    // Buildings
    'agent/buildings' => [
        'controller' => 'App\Controllers\BuildingsController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'agent.buildings.index'
    ],
    'agent/buildings/create' => [
        'controller' => 'App\Controllers\BuildingsController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'agent.buildings.create'
    ],
    'agent/buildings/store' => [
        'controller' => 'App\Controllers\BuildingsController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'agent.buildings.store'
    ],
    'agent/buildings/:id' => [
        'controller' => 'App\Controllers\BuildingsController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'agent.buildings.show'
    ],
    'agent/buildings/edit/:id' => [
        'controller' => 'App\Controllers\BuildingsController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'agent.buildings.edit'
    ],
    'agent/buildings/update/:id' => [
        'controller' => 'App\Controllers\BuildingsController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'agent.buildings.update'
    ],
    'agent/buildings/delete/:id' => [
        'controller' => 'App\Controllers\BuildingsController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'agent.buildings.delete'
    ],

    // Apartments
    'agent/apartments' => [
        'controller' => 'App\Controllers\ApartmentsController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'agent.apartments.index'
    ],
    'agent/apartments/create' => [
        'controller' => 'App\Controllers\ApartmentsController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'agent.apartments.create'
    ],
    'agent/apartments/store' => [
        'controller' => 'App\Controllers\ApartmentsController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'agent.apartments.store'
    ],
    'agent/apartments/:id' => [
        'controller' => 'App\Controllers\ApartmentsController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'agent.apartments.show'
    ],
    'agent/apartments/edit/:id' => [
        'controller' => 'App\Controllers\ApartmentsController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'agent.apartments.edit'
    ],
    'agent/apartments/update/:id' => [
        'controller' => 'App\Controllers\ApartmentsController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'agent.apartments.update'
    ],
    'agent/apartments/delete/:id' => [
        'controller' => 'App\Controllers\ApartmentsController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'agent.apartments.delete'
    ],

    // Tenants
    'agent/tenants' => [
        'controller' => 'App\Controllers\TenantsController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'agent.tenants.index'
    ],
    'agent/tenants/create' => [
        'controller' => 'App\Controllers\TenantsController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'agent.tenants.create'
    ],
    'agent/tenants/store' => [
        'controller' => 'App\Controllers\TenantsController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'agent.tenants.store'
    ],
    'agent/tenants/:id' => [
        'controller' => 'App\Controllers\TenantsController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'agent.tenants.show'
    ],
    'agent/tenants/edit/:id' => [
        'controller' => 'App\Controllers\TenantsController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'agent.tenants.edit'
    ],
    'agent/tenants/update/:id' => [
        'controller' => 'App\Controllers\TenantsController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'agent.tenants.update'
    ],
    'agent/tenants/delete/:id' => [
        'controller' => 'App\Controllers\TenantsController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'agent.tenants.delete'
    ],

    // Leases
    'agent/leases' => [
        'controller' => 'App\Controllers\LeasesController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'agent.leases.index'
    ],
    'agent/leases/create' => [
        'controller' => 'App\Controllers\LeasesController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'agent.leases.create'
    ],
    'agent/leases/store' => [
        'controller' => 'App\Controllers\LeasesController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'agent.leases.store'
    ],
    'agent/leases/:id' => [
        'controller' => 'App\Controllers\LeasesController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'agent.leases.show'
    ],
    'agent/leases/edit/:id' => [
        'controller' => 'App\Controllers\LeasesController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'agent.leases.edit'
    ],
    'agent/leases/update/:id' => [
        'controller' => 'App\Controllers\LeasesController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'agent.leases.update'
    ],
    'agent/leases/delete/:id' => [
        'controller' => 'App\Controllers\LeasesController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'agent.leases.delete'
    ],

    // Owners
    'agent/owners' => [
        'controller' => 'App\Controllers\OwnersController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'agent.owners.index'
    ],
    'agent/owners/create' => [
        'controller' => 'App\Controllers\OwnersController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'agent.owners.create'
    ],
    'agent/owners/store' => [
        'controller' => 'App\Controllers\OwnersController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'agent.owners.store'
    ],
    'agent/owners/:id' => [
        'controller' => 'App\Controllers\OwnersController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'agent.owners.show'
    ],
    'agent/owners/edit/:id' => [
        'controller' => 'App\Controllers\OwnersController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'agent.owners.edit'
    ],
    'agent/owners/update/:id' => [
        'controller' => 'App\Controllers\OwnersController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'agent.owners.update'
    ],
    'agent/owners/delete/:id' => [
        'controller' => 'App\Controllers\OwnersController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'agent.owners.delete'
    ],

    // Payments
    'agent/payments' => [
        'controller' => 'App\Controllers\PaymentsController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'agent.payments.index'
    ],
    'agent/payments/create' => [
        'controller' => 'App\Controllers\PaymentsController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'agent.payments.create'
    ],
    'agent/payments/store' => [
        'controller' => 'App\Controllers\PaymentsController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'agent.payments.store'
    ],
    'agent/payments/:id' => [
        'controller' => 'App\Controllers\PaymentsController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'agent.payments.show'
    ],
    'agent/payments/edit/:id' => [
        'controller' => 'App\Controllers\PaymentsController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'agent.payments.edit'
    ],
    'agent/payments/update/:id' => [
        'controller' => 'App\Controllers\PaymentsController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'agent.payments.update'
    ],
    'agent/payments/delete/:id' => [
        'controller' => 'App\Controllers\PaymentsController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'agent.payments.delete'
    ],
    'agent/payments/quittance/:id' => [
        'controller' => 'App\Controllers\PaymentsController',
        'action' => 'createQuittance',
        'method' => 'GET',
        'name' => 'agent.payments.quittance'
    ],
];

return $routes;
?>