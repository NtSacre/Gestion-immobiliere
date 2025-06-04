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

    // Building
    'agent/buildings' => [
        'controller' => 'App\Controllers\BuildingController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'agent.buildings.index'
    ],
    'agent/buildings/create' => [
        'controller' => 'App\Controllers\BuildingController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'agent.buildings.create'
    ],
    'agent/buildings/store' => [
        'controller' => 'App\Controllers\BuildingController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'agent.buildings.store'
    ],
    'agent/buildings/:id' => [
        'controller' => 'App\Controllers\BuildingController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'agent.buildings.show'
    ],
    'agent/buildings/edit/:id' => [
        'controller' => 'App\Controllers\BuildingController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'agent.buildings.edit'
    ],
    'agent/buildings/update/:id' => [
        'controller' => 'App\Controllers\BuildingController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'agent.buildings.update'
    ],
    'agent/buildings/delete/:id' => [
        'controller' => 'App\Controllers\BuildingController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'agent.buildings.delete'
    ],

    // Apartment
    'agent/apartments' => [
        'controller' => 'App\Controllers\ApartmentController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'agent.apartments.index'
    ],
    'agent/apartments/create' => [
        'controller' => 'App\Controllers\ApartmentController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'agent.apartments.create'
    ],
    'agent/apartments/store' => [
        'controller' => 'App\Controllers\ApartmentController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'agent.apartments.store'
    ],
    'agent/apartments/:id' => [
        'controller' => 'App\Controllers\ApartmentController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'agent.apartments.show'
    ],
    'agent/apartments/edit/:id' => [
        'controller' => 'App\Controllers\ApartmentController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'agent.apartments.edit'
    ],
    'agent/apartments/update/:id' => [
        'controller' => 'App\Controllers\ApartmentController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'agent.apartments.update'
    ],
    'agent/apartments/delete/:id' => [
        'controller' => 'App\Controllers\ApartmentController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'agent.apartments.delete'
    ],

    // Tenant
    'agent/tenants' => [
        'controller' => 'App\Controllers\TenantController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'agent.tenants.index'
    ],
    'agent/tenants/create' => [
        'controller' => 'App\Controllers\TenantController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'agent.tenants.create'
    ],
    'agent/tenants/store' => [
        'controller' => 'App\Controllers\TenantController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'agent.tenants.store'
    ],
    'agent/tenants/:id' => [
        'controller' => 'App\Controllers\TenantController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'agent.tenants.show'
    ],
    'agent/tenants/edit/:id' => [
        'controller' => 'App\Controllers\TenantController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'agent.tenants.edit'
    ],
    'agent/tenants/update/:id' => [
        'controller' => 'App\Controllers\TenantController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'agent.tenants.update'
    ],
    'agent/tenants/delete/:id' => [
        'controller' => 'App\Controllers\TenantController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'agent.tenants.delete'
    ],

    // Lease
    'agent/leases' => [
        'controller' => 'App\Controllers\LeaseController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'agent.leases.index'
    ],
    'agent/leases/create' => [
        'controller' => 'App\Controllers\LeaseController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'agent.leases.create'
    ],
    'agent/leases/store' => [
        'controller' => 'App\Controllers\LeaseController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'agent.leases.store'
    ],
    'agent/leases/:id' => [
        'controller' => 'App\Controllers\LeaseController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'agent.leases.show'
    ],
    'agent/leases/edit/:id' => [
        'controller' => 'App\Controllers\LeaseController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'agent.leases.edit'
    ],
    'agent/leases/update/:id' => [
        'controller' => 'App\Controllers\LeaseController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'agent.leases.update'
    ],
    'agent/leases/delete/:id' => [
        'controller' => 'App\Controllers\LeaseController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'agent.leases.delete'
    ],

    // Owner
    'agent/owners' => [
        'controller' => 'App\Controllers\OwnerController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'agent.owners.index'
    ],
    'agent/owners/create' => [
        'controller' => 'App\Controllers\OwnerController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'agent.owners.create'
    ],
    'agent/owners/store' => [
        'controller' => 'App\Controllers\OwnerController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'agent.owners.store'
    ],
    'agent/owners/:id' => [
        'controller' => 'App\Controllers\OwnerController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'agent.owners.show'
    ],
    'agent/owners/edit/:id' => [
        'controller' => 'App\Controllers\OwnerController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'agent.owners.edit'
    ],
    'agent/owners/update/:id' => [
        'controller' => 'App\Controllers\OwnerController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'agent.owners.update'
    ],
    'agent/owners/delete/:id' => [
        'controller' => 'App\Controllers\OwnerController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'agent.owners.delete'
    ],

    // Payment
    'agent/payments' => [
        'controller' => 'App\Controllers\PaymentController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'agent.payments.index'
    ],
    'agent/payments/create' => [
        'controller' => 'App\Controllers\PaymentController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'agent.payments.create'
    ],
    'agent/payments/store' => [
        'controller' => 'App\Controllers\PaymentController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'agent.payments.store'
    ],
    'agent/payments/:id' => [
        'controller' => 'App\Controllers\PaymentController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'agent.payments.show'
    ],
    'agent/payments/edit/:id' => [
        'controller' => 'App\Controllers\PaymentController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'agent.payments.edit'
    ],
    'agent/payments/update/:id' => [
        'controller' => 'App\Controllers\PaymentController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'agent.payments.update'
    ],
    'agent/payments/delete/:id' => [
        'controller' => 'App\Controllers\PaymentController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'agent.payments.delete'
    ],
    'agent/payments/quittance/:id' => [
        'controller' => 'App\Controllers\PaymentController',
        'action' => 'createQuittance',
        'method' => 'GET',
        'name' => 'agent.payments.quittance'
    ],
];

return $routes;
?>