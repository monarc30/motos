<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    public function index(): void
    {
        $this->render('home/index', [
            'titulo' => 'Sistema de Gerenciamento de Vendas de Motocicletas'
        ]);
    }
}

