<?php
namespace App\Controllers;

class TenantController
{
    public function index()
    {
        $content_view = 'admin/tenants/index.php';
        require_once dirname(__DIR__) . '/Views/layouts/admin_layout.php';
    }

    public function create()
    {
        $content_view = 'admin/tenants/create.php';
        require_once dirname(__DIR__) . '/Views/layouts/admin_layout.php';
    }

    public function show($id)
    {
        $content_view = 'admin/tenants/show.php';
        require_once dirname(__DIR__) . '/Views/layouts/admin_layout.php';
    }

    public function store()
    {
        // Simuler l'enregistrement (logique à implémenter par l'équipe)
        header('Location: /agent/tenants');
        exit;
    }

    public function edit($id)
    {
        $content_view = 'admin/tenants/edit.php';
        require_once dirname(__DIR__) . '/Views/layouts/admin_layout.php';
    }

    public function update($id)
    {
        // Simuler la mise à jour (logique à implémenter par l'équipe)
        header('Location: /agent/tenants');
        exit;
    }

    public function delete($id)
    {
        // Simuler la suppression (logique à implémenter par l'équipe)
        header('Location: /agent/tenants');
        exit;
    }
}
?>