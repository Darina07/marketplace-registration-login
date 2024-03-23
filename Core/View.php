<?php

namespace Core;

use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class View
{

    /**
     * Render a view file
     *
     * @param string $view The view file
     * @param array $args Associative array of data to display in the view (optional)
     *
     * @return void
     * @throws Exception
     */
    public static function render($view, $args = []): void
    {
        extract($args, EXTR_SKIP);

        $file = dirname(__DIR__) . "/App/Views/$view";  // relative to Core directory

        if (is_readable($file)) {
            require $file;
        } else {
            throw new Exception("$file not found");
        }
    }

    /**
     * Render a view template using Twig
     *
     * @param string $template The template file
     * @param array $args Associative array of data to display in the view (optional)
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public static function renderTemplate($template, $args = []): void
    {
        static $twig = null;

        // Check if Twig environment is already initialized
        if ($twig === null) {
            // Specify the directory where Twig templates are located
            $templatesDirectory = dirname(__DIR__) . '/App/Views';

            // Create a Twig loader that loads templates from the filesystem
            $loader = new \Twig\Loader\FilesystemLoader($templatesDirectory);

            // Create a Twig environment
            $twig = new \Twig\Environment($loader);

            // Add global variables
            $twig->addGlobal('current_user', \App\Auth::getUser());
            $twig->addGlobal('flash_messages', \App\Flash::getMessages());
        }

        // Render the template with Twig and output the result
        echo $twig->render($template, $args);
    }

}
