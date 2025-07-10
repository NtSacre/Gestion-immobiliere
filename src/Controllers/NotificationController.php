<?php
namespace App\Controllers;

use App\Services\NotificationService;
use App\Utils\Helpers;

class NotificationController 
{
    protected $notificationService;
     protected $helpers;

    public function __construct()
    {
  
        $this->notificationService = new NotificationService();
        $this->helpers = new Helpers();
    }

    public function markAsRead($id)
    {
        $this->notificationService->markAsRead($id);

        // Redirection vers la page liée à la notif (si elle existe)
        $link = $_GET['redirect'] ?? '/dashboard';
        $this->helpers->redirect($link);
    }
}
