<?php
namespace App\Controllers;

use App\Models\Apartment;

class ApartmentController
{
    public function index()
    {
       
        $content_view = 'admin/apartments/index.php';
        require_once dirname(__DIR__) . '/Views/layouts/admin_layout.php';
    }

    public function create()
    {
        $content_view = 'admin/apartments/create.php';
        require_once dirname(__DIR__) . '/Views/layouts/admin_layout.php';
    }

    public function show($id)
    {
        $content_view = 'admin/apartments/show.php';
        require_once dirname(__DIR__) . '/Views/layouts/admin_layout.php';
    }

    public function store()
    {
        // Simuler l'enregistrement (logique à implémenter par l'équipe)
        header('Location: /agent/apartments');
        exit;
    }

        public function edit($id)
    {
        $content_view = 'admin/apartments/edit.php';
        require_once dirname(__DIR__) . '/Views/layouts/admin_layout.php';
    }

    public function update($id)
    {
        // Simuler la mise à jour (logique à implémenter par l'équipe)
        header('Location: /agent/apartments');
        exit;
    }

    public function delete($id)
    {
        // Simuler la suppression (logique à implémenter par l'équipe)
        header('Location: /agent/apartments');
        exit;
    }
}
?>