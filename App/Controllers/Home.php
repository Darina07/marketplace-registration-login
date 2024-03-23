<?php

namespace App\Controllers;

use App\Models\Item;
use \Core\View;
use \App\Auth;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Home controller
 */
class Home extends \Core\Controller
{

    /**
     * Show the index page
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function indexAction(): void
    {
        $model = new Item();
        $allItems = $model->getAllItems();
        View::renderTemplate('Home/index.html', [
            'items' => $allItems
        ]);
    }
}
