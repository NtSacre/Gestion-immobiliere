<?php

$routes = [
    // Routes publiques (accessibles à tous ou aux non-authentifiés)
    '' => [
        'controller' => 'App\Controllers\Public\HomeController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'home',
        'allowed_roles' => ['guest', 'superadmin', 'admin', 'agent', 'proprietaire', 'locataire', 'acheteur']
    ],
    'auth/login' => [
        'controller' => 'App\Controllers\Public\AuthController',
        'action' => 'showLogin',
        'method' => 'GET',
        'name' => 'auth.login',
        'allowed_roles' => ['guest']
    ],
    'login' => [
        'controller' => 'App\Controllers\Public\AuthController',
        'action' => 'login',
        'method' => 'POST',
        'name' => 'auth.login.post',
        'allowed_roles' => ['guest']
    ],
    'auth/register' => [
        'controller' => 'App\Controllers\Public\AuthController',
        'action' => 'showRegister',
        'method' => 'GET',
        'name' => 'auth.register',
        'allowed_roles' => ['guest']
    ],
    'register' => [
        'controller' => 'App\Controllers\Public\AuthController',
        'action' => 'register',
        'method' => 'POST',
        'name' => 'auth.register.post',
        'allowed_roles' => ['guest']
    ],
    'logout' => [
        'controller' => 'App\Controllers\Public\AuthController',
        'action' => 'logout',
        'method' => 'GET',
        'name' => 'auth.logout',
        'allowed_roles' => ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire', 'acheteur']
    ],
    '403' => [
        'controller' => 'App\Controllers\Public\ErrorController',
        'action' => 'forbidden',
        'method' => 'GET',
        'name' => 'error.forbidden',
        'allowed_roles' => ['guest', 'superadmin', 'admin', 'agent', 'proprietaire', 'locataire', 'acheteur']
    ],

    // Routes génériques (accès selon rôle)
    'dashboard' => [
        'controller' => 'App\Controllers\DashboardController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'dashboard',
        'allowed_roles' => ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire', 'acheteur']
    ],
    'profile' => [
        'controller' => 'App\Controllers\UserController',
        'action' => 'profile',
        'method' => 'GET',
        'name' => 'profile',
        'allowed_roles' => ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire', 'acheteur']
    ],
    'profile/update' => [
        'controller' => 'App\Controllers\UserController',
        'action' => 'updateProfile',
        'method' => 'POST',
        'name' => 'profile.update',
        'allowed_roles' => ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire', 'acheteur']
    ],
    'buildings' => [
        'controller' => 'App\Controllers\BuildingController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'buildings.index',
        'allowed_roles' => ['superadmin', 'admin', 'agent', 'proprietaire', 'acheteur']
    ],
    'buildings/create' => [
        'controller' => 'App\Controllers\BuildingController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'buildings.create',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'buildings/store' => [
        'controller' => 'App\Controllers\BuildingController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'buildings.store',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'buildings/:id' => [
        'controller' => 'App\Controllers\BuildingController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'buildings.show',
        'allowed_roles' => ['superadmin', 'admin', 'agent', 'proprietaire', 'acheteur']
    ],
    'buildings/edit/:id' => [
        'controller' => 'App\Controllers\BuildingController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'buildings.edit',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'buildings/update/:id' => [
        'controller' => 'App\Controllers\BuildingController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'buildings.update',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'buildings/delete/:id' => [
        'controller' => 'App\Controllers\BuildingController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'buildings.delete',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'apartments' => [
        'controller' => 'App\Controllers\ApartmentController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'apartments.index',
        'allowed_roles' => ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire', 'acheteur']
    ],
    'apartments/create' => [
        'controller' => 'App\Controllers\ApartmentController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'apartments.create',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'apartments/store' => [
        'controller' => 'App\Controllers\ApartmentController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'apartments.store',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'apartments/:id' => [
        'controller' => 'App\Controllers\ApartmentController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'apartments.show',
        'allowed_roles' => ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire', 'acheteur']
    ],
    'apartments/edit/:id' => [
        'controller' => 'App\Controllers\ApartmentController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'apartments.edit',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'apartments/update/:id' => [
        'controller' => 'App\Controllers\ApartmentController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'apartments.update',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'apartments/delete/:id' => [
        'controller' => 'App\Controllers\ApartmentController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'apartments.delete',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'leases' => [
        'controller' => 'App\Controllers\LeaseController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'leases.index',
        'allowed_roles' => ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire']
    ],
    'leases/create' => [
        'controller' => 'App\Controllers\LeaseController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'leases.create',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'leases/store' => [
        'controller' => 'App\Controllers\LeaseController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'leases.store',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'leases/:id' => [
        'controller' => 'App\Controllers\LeaseController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'leases.show',
        'allowed_roles' => ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire']
    ],
    'leases/edit/:id' => [
        'controller' => 'App\Controllers\LeaseController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'leases.edit',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'leases/update/:id' => [
        'controller' => 'App\Controllers\LeaseController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'leases.update',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'leases/delete/:id' => [
        'controller' => 'App\Controllers\LeaseController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'leases.delete',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'leases/download' => [
        'controller' => 'App\Controllers\LeaseController',
        'action' => 'download',
        'method' => 'GET',
        'name' => 'leases.download',
        'allowed_roles' => ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire', 'acheteur']
    ],
    'payments' => [
        'controller' => 'App\Controllers\PaymentController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'payments.index',
        'allowed_roles' => ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire']
    ],
    'payments/create' => [
        'controller' => 'App\Controllers\PaymentController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'payments.create',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'payments/store' => [
        'controller' => 'App\Controllers\PaymentController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'payments.store',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'payments/:id' => [
        'controller' => 'App\Controllers\PaymentController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'payments.show',
        'allowed_roles' => ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire']
    ],
    'payments/edit/:id' => [
        'controller' => 'App\Controllers\PaymentController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'payments.edit',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'payments/update/:id' => [
        'controller' => 'App\Controllers\PaymentController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'payments.update',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'payments/delete/:id' => [
        'controller' => 'App\Controllers\PaymentController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'payments.delete',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'payments/quittance/:id' => [
        'controller' => 'App\Controllers\PaymentController',
        'action' => 'createQuittance',
        'method' => 'GET',
        'name' => 'payments.quittance',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'clients' => [
        'controller' => 'App\Controllers\ClientController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'clients.index',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'clients/create' => [
        'controller' => 'App\Controllers\ClientController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'clients.create',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'clients/store' => [
        'controller' => 'App\Controllers\ClientController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'clients.store',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'clients/:id' => [
        'controller' => 'App\Controllers\ClientController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'clients.show',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'clients/edit/:id' => [
        'controller' => 'App\Controllers\ClientController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'clients.edit',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'clients/update/:id' => [
        'controller' => 'App\Controllers\ClientController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'clients.update',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'clients/delete/:id' => [
        'controller' => 'App\Controllers\ClientController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'clients.delete',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'owners' => [
        'controller' => 'App\Controllers\OwnerController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'owners.index',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'owners/create' => [
        'controller' => 'App\Controllers\OwnerController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'owners.create',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'owners/store' => [
        'controller' => 'App\Controllers\OwnerController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'owners.store',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'owners/:id' => [
        'controller' => 'App\Controllers\OwnerController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'owners.show',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'owners/edit/:id' => [
        'controller' => 'App\Controllers\OwnerController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'owners.edit',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'owners/update/:id' => [
        'controller' => 'App\Controllers\OwnerController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'owners.update',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'owners/delete/:id' => [
        'controller' => 'App\Controllers\OwnerController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'owners.delete',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'tenants' => [
        'controller' => 'App\Controllers\TenantController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'tenants.index',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'tenants/create' => [
        'controller' => 'App\Controllers\TenantController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'tenants.create',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'tenants/store' => [
        'controller' => 'App\Controllers\TenantController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'tenants.store',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'tenants/:id' => [
        'controller' => 'App\Controllers\TenantController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'tenants.show',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'tenants/edit/:id' => [
        'controller' => 'App\Controllers\TenantController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'tenants.edit',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'tenants/update/:id' => [
        'controller' => 'App\Controllers\TenantController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'tenants.update',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'tenants/delete/:id' => [
        'controller' => 'App\Controllers\TenantController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'tenants.delete',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'buyers' => [
        'controller' => 'App\Controllers\BuyerController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'buyers.index',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'buyers/create' => [
        'controller' => 'App\Controllers\BuyerController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'buyers.create',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'buyers/store' => [
        'controller' => 'App\Controllers\BuyerController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'buyers.store',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'buyers/:id' => [
        'controller' => 'App\Controllers\BuyerController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'buyers.show',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'buyers/edit/:id' => [
        'controller' => 'App\Controllers\BuyerController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'buyers.edit',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'buyers/update/:id' => [
        'controller' => 'App\Controllers\BuyerController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'buyers.update',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'buyers/delete/:id' => [
        'controller' => 'App\Controllers\BuyerController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'buyers.delete',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'audit-log' => [
        'controller' => 'App\Controllers\AuditLogController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'audit-log.index',
        'allowed_roles' => ['superadmin', 'admin']
    ],
    'building-types' => [
        'controller' => 'App\Controllers\BuildingTypeController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'building-types.index',
        'allowed_roles' => ['superadmin']
    ],
    'building-types/create' => [
        'controller' => 'App\Controllers\BuildingTypeController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'building-types.create',
        'allowed_roles' => ['superadmin']
    ],
    'building-types/store' => [
        'controller' => 'App\Controllers\BuildingTypeController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'building-types.store',
        'allowed_roles' => ['superadmin']
    ],
    'building-types/:id' => [
        'controller' => 'App\Controllers\BuildingTypeController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'building-types.show',
        'allowed_roles' => ['superadmin']
    ],
    'building-types/edit/:id' => [
        'controller' => 'App\Controllers\BuildingTypeController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'building-types.edit',
        'allowed_roles' => ['superadmin']
    ],
    'building-types/update/:id' => [
        'controller' => 'App\Controllers\BuildingTypeController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'building-types.update',
        'allowed_roles' => ['superadmin']
    ],
    'building-types/delete/:id' => [
        'controller' => 'App\Controllers\BuildingTypeController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'building-types.delete',
        'allowed_roles' => ['superadmin']
    ],
    'apartment-types' => [
        'controller' => 'App\Controllers\ApartmentTypeController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'apartment-types.index',
        'allowed_roles' => ['superadmin']
    ],
    'apartment-types/create' => [
        'controller' => 'App\Controllers\ApartmentTypeController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'apartment-types.create',
        'allowed_roles' => ['superadmin']
    ],
    'apartment-types/store' => [
        'controller' => 'App\Controllers\ApartmentTypeController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'apartment-types.store',
        'allowed_roles' => ['superadmin']
    ],
    'apartment-types/:id' => [
        'controller' => 'App\Controllers\ApartmentTypeController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'apartment-types.show',
        'allowed_roles' => ['superadmin']
    ],
    'apartment-types/edit/:id' => [
        'controller' => 'App\Controllers\ApartmentTypeController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'apartment-types.edit',
        'allowed_roles' => ['superadmin']
    ],
    'apartment-types/update/:id' => [
        'controller' => 'App\Controllers\ApartmentTypeController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'apartment-types.update',
        'allowed_roles' => ['superadmin']
    ],
    'apartment-types/delete/:id' => [
        'controller' => 'App\Controllers\ApartmentTypeController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'apartment-types.delete',
        'allowed_roles' => ['superadmin']
    ],
    'agencies' => [
        'controller' => 'App\Controllers\AgencyController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'agencies.index',
        'allowed_roles' => ['superadmin']
    ],
    'agencies/create' => [
        'controller' => 'App\Controllers\AgencyController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'agencies.create',
        'allowed_roles' => ['superadmin']
    ],
    'agencies/store' => [
        'controller' => 'App\Controllers\AgencyController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'agencies.store',
        'allowed_roles' => ['superadmin']
    ],
    'agencies/:id' => [
        'controller' => 'App\Controllers\AgencyController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'agencies.show',
        'allowed_roles' => ['superadmin']
    ],
    'agencies/edit/:id' => [
        'controller' => 'App\Controllers\AgencyController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'agencies.edit',
        'allowed_roles' => ['superadmin']
    ],
    'agencies/update/:id' => [
        'controller' => 'App\Controllers\AgencyController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'agencies.update',
        'allowed_roles' => ['superadmin']
    ],
    'agencies/delete/:id' => [
        'controller' => 'App\Controllers\AgencyController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'agencies.delete',
        'allowed_roles' => ['superadmin']
    ],
    'users' => [
        'controller' => 'App\Controllers\UserController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'users.index',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'users/create' => [
        'controller' => 'App\Controllers\UserController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'users.create',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'users/store' => [
        'controller' => 'App\Controllers\UserController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'users.store',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'users/:id' => [
        'controller' => 'App\Controllers\UserController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'users.show',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'users/edit/:id' => [
        'controller' => 'App\Controllers\UserController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'users.edit',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'users/update/:id' => [
        'controller' => 'App\Controllers\UserController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'users.update',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'users/delete/:id' => [
        'controller' => 'App\Controllers\UserController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'users.delete',
        'allowed_roles' => ['superadmin', 'admin', 'agent']
    ],
    'roles' => [
        'controller' => 'App\Controllers\RoleController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'roles.index',
        'allowed_roles' => ['superadmin']
    ],
    'roles/create' => [
        'controller' => 'App\Controllers\RoleController',
        'action' => 'create',
        'method' => 'GET',
        'name' => 'roles.create',
        'allowed_roles' => ['superadmin']
    ],
    'roles/store' => [
        'controller' => 'App\Controllers\RoleController',
        'action' => 'store',
        'method' => 'POST',
        'name' => 'roles.store',
        'allowed_roles' => ['superadmin']
    ],
    'roles/:id' => [
        'controller' => 'App\Controllers\RoleController',
        'action' => 'show',
        'method' => 'GET',
        'name' => 'roles.show',
        'allowed_roles' => ['superadmin']
    ],
    'roles/edit/:id' => [
        'controller' => 'App\Controllers\RoleController',
        'action' => 'edit',
        'method' => 'GET',
        'name' => 'roles.edit',
        'allowed_roles' => ['superadmin']
    ],
    'roles/update/:id' => [
        'controller' => 'App\Controllers\RoleController',
        'action' => 'update',
        'method' => 'POST',
        'name' => 'roles.update',
        'allowed_roles' => ['superadmin']
    ],
    'roles/delete/:id' => [
        'controller' => 'App\Controllers\RoleController',
        'action' => 'delete',
        'method' => 'POST',
        'name' => 'roles.delete',
        'allowed_roles' => ['superadmin']
    ],
    'reports' => [
        'controller' => 'App\Controllers\ReportController',
        'action' => 'index',
        'method' => 'GET',
        'name' => 'reports.index',
        'allowed_roles' => ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire', 'acheteur']
    ],
    'export' => [
        'controller' => 'App\Controllers\ExportController',
        'action' => 'export',
        'method' => 'GET',
        'name' => 'export',
        'allowed_roles' => ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire', 'acheteur']
    ],

// Ajout des routes pour les baux
'routes' => [
    // Routes existantes (à conserver)
    // ...

    // Routes pour les baux
    'GET /leases' => ['controller' => 'LeaseController', 'method' => 'index'],
    'GET /leases/create' => ['controller' => 'LeaseController', 'method' => 'create'],
    'POST /leases/store' => ['controller' => 'LeaseController', 'method' => 'store'],
    'GET /leases/show/(\d+)' => ['controller' => 'LeaseController', 'method' => 'show'],
    'GET /leases/edit/(\d+)' => ['controller' => 'LeaseController', 'method' => 'edit'],
    'POST /leases/update/(\d+)' => ['controller' => 'LeaseController', 'method' => 'update'],
    'POST /leases/delete/(\d+)' => ['controller' => 'LeaseController', 'method' => 'delete'],
]


];

return $routes;