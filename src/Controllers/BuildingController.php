<?php
namespace App\Controllers;

class BuildingController
{
    public function index()
    {
        $content_view = 'admin/buildings/index.php';
        require_once dirname(__DIR__) . '/Views/layouts/admin_layout.php';
    }

    public function create()
    {
        $content_view = 'admin/buildings/create.php';
        require_once dirname(__DIR__) . '/Views/layouts/admin_layout.php';
    }

    public function show($id)
    {
        $content_view = 'admin/buildings/show.php';
        require_once dirname(__DIR__) . '/Views/layouts/admin_layout.php';
    }

    public function store()
    {
        // Simuler l'enregistrement (logique à implémenter par l'équipe)
        header('Location: /agent/buildings');
        exit;
    }

        public function edit($id)
    {
        $content_view = 'admin/buildings/edit.php';
        require_once dirname(__DIR__) . '/Views/layouts/admin_layout.php';
    }

    public function update($id)
    {
        // Simuler la mise à jour (logique à implémenter par l'équipe)
        header('Location: /agent/buildings');
        exit;
    }

    public function delete($id)
    {
        // Simuler la suppression (logique à implémenter par l'équipe)
        header('Location: /agent/buildings');
        exit;
    }
}
?>