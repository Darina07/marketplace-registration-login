<?php

namespace App;

/**
 * Application configuration
 */
class Config
{

    /**
     * Database host
     * @var string
     */
    const DB_HOST = 'localhost';

    /**
     * Database name
     * @var string
     */
    const DB_NAME = 'task';

    /**
     * Database user
     * @var string
     */
    const DB_USER = 'erc_dev';

    /**
     * Database password
     * @var string
     */
    const DB_PASSWORD = 'b1sY0mQ5EnkEqPQG';

    /**
     * Show or hide error messages on screen
     * @var boolean
     */
    const SHOW_ERRORS = true;

    /**
     * Secret key for hashing
     * @var boolean
     */
    const SECRET_KEY = 'secret';
}
