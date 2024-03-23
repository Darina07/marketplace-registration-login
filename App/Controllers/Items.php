<?php

namespace App\Controllers;

use App\Auth;
use App\Flash;
use App\Models\Item;
use \Core\View;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Items controller
 */
class Items extends Authenticated
{


    /**
     * Require the user to be authenticated before giving access to all methods in the controller
     *
     * @return void
     */

    protected function before(): void
    {
        $this->requireLogin();
    }


    //construct
    public function __construct($params)
    {
        $this->params = $params;
        $this->model = new Item();
    }

    /**
     * Show the My Items Index page
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function index(): void
    {
        $items = $this->model->getAllByUserId(Auth::getUser()->id);
        View::renderTemplate('Items/index.html', ['items' => $items]);
    }

    /**
     * Show the form for creating a new item
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function createAction(): void
    {
        $categories = $this->model->getCategories();
        View::renderTemplate('Items/create.html', ['categories' => $categories]);
    }

    /**
     * Store a new item in the database
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function storeAction(): void
    {
        // Process form submission and store the item
        $item = new Item($_POST);

        if ($item->save()) {
            Flash::addMessage('Item created successfully', Flash::SUCCESS);
            $this->redirect('/items/index');
        } else {
            Flash::addMessage('Item creation failed', Flash::WARNING);
            $categories = $this->model->getCategories();
            View::renderTemplate('Items/create.html', [
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'category' => $categories,
                'item' => $item
            ]);
        }
    }

    /**
     * Show the form for editing an item
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function editAction(): void
    {
        $id = $this->params['id'];
        $item = $this->model->getById($id);
        if ($item && $item->users_id === Auth::getUser()->id) {
            View::renderTemplate('Items/edit.html', ['item' => $item, 'categories' => $this->model->getCategories()]);
        } else {
            // Handle case where item does not exist or does not belong to the user
            Flash::addMessage('Permission denied', Flash::WARNING);
            $this->redirect("/items/index");
        }

    }

    /**
     * Update an item in the database
     */
    public function updateAction()
    {
        $id = $this->params['id'];
        $data = [
            "title" => $_POST["title"],
            "description" => empty($_POST["description"]) ? null : $_POST["description"],
            "categories_id" => $_POST["categories_id"],
            "id" => $id
        ];

        if ($this->model->update($data)) {
           return $this->redirect("/items/index");
       } else {

            return $this->view("Products/new.mvc.php", [
                "errors" => $this->model->getErrors(),
                "product" => $data
            ]);

        }
    }

    /**
     * Delete an item from the database
     *
     * @return void
     */
    public function deleteAction(): void
    {
        $id = $_POST['delete'];
        $item = $this->model->getById($id);
        if ($item->users_id === Auth::getUser()->id) {
            $this->model->delete($id);
            Flash::addMessage('Item deleted successfully', Flash::SUCCESS);
            $this->redirect('/items/index');
        } else {
            // Handle case where item does not belong to the user
            Flash::addMessage('Permission denied', Flash::WARNING);
            $this->redirect('/items/index');
        }
    }

}
