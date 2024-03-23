<?php

namespace App\Models;

use PDO;
use \App\Token;

/**
 * User model
 */
class User extends \Core\Model
{

    /**
     * Error messages
     *
     * @var array
     */
    public array $errors = [];

    /**
     * Class constructor
     *
     * @param array $data  Initial property values (optional)
     *
     * @return void
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }

    /**
     * Save the user model with the current property values
     *
     * @return boolean  True if the user was saved, false otherwise
     */
    public function save(): bool
    {
        $this->validate();

        if (empty($this->errors)) {

            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $sql = <<<SQL
INSERT INTO users (name, surname, phone, city, email, password_hash)
VALUES (:name, :surname, :phone, :city, :email, :password_hash);
SQL;
            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $this->name);
            $stmt->bindValue(':surname', $this->surname);
            $stmt->bindValue(':phone', $this->phone);
            $stmt->bindValue(':city', $this->city);
            $stmt->bindValue(':email', $this->email);
            $stmt->bindValue(':password_hash', $password_hash);

            return $stmt->execute();
        }

        return false;
    }

    /**
     * Validate current property values, adding valiation error messages to the errors array property
     *
     * @return void
     */
    public function validate(): void
    {
        // Name
        if ($this->name == '') {
            $this->errors[] = 'Name is required';
        }

        if ($this->surname == '') {
            $this->errors[] = 'Surname is required';
        }

        if ($this->phone == '') {
            $this->errors[] = 'Phone is required';
        }

        if ($this->city == '') {
            $this->errors[] = 'City is required';
        }

        // email address
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Invalid email';
        }
        if (static::emailExists($this->email, $this->id ?? null)) {
            $this->errors[] = 'email already taken';
        }

        // Password
        if (isset($this->password)) {

            if (strlen($this->password) < 6) {
                $this->errors[] = 'Please enter at least 6 characters for the password';
            }

            if (preg_match('/.*[a-z]+.*/i', $this->password) == 0) {
                $this->errors[] = 'Password needs at least one letter';
            }

            if (preg_match('/.*\d+.*/i', $this->password) == 0) {
                $this->errors[] = 'Password needs at least one number';
            }

        }
    }

        /**
     * See if a user record already exists with the specified email
     *
     * @param string $email email address to search for
     * @param string|null $ignore_id Return false anyway if the record found has this ID
     *
     * @return boolean|false  True if a record already exists with the specified email, false otherwise
     */
    public static function emailExists(string $email, string $ignore_id = null): bool
    {
        $user = static::findByEmail($email);

        if ($user) {
            if ($user->id != $ignore_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find a user model by email address
     *
     * @param string $email email address to search for
     *
     * @return mixed User object if found, false otherwise
     */
    public static function findByEmail(string $email): mixed
    {
        $sql = <<<SQL
SELECT * FROM users WHERE email = :email
SQL;
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':email', $email);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Authenticate a user by email and password.
     *
     * @param string $email email address
     * @param string $password password
     *
     * @return User|false The user object or false if authentication fails
     */
    public static function authenticate(string $email, string $password): User|false
    {
        $user = static::findByEmail($email);

        if ($user) {
            if (password_verify($password, $user->password_hash)) {
                return $user;
            }
        }

        return false;
    }

    /**
     * Find a user model by ID
     *
     * @param int $id The user ID
     *
     * @return mixed User object if found, false otherwise
     */
    public static function findByID(int $id)
    {
        $sql = <<<SQL
SELECT * FROM users WHERE id = :id;
SQL;
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Remember the login by inserting a new unique token into the remembered_logins table
     * for this user record
     *
     * @return boolean  True if the login was remembered successfully, false otherwise
     */
    public function rememberLogin(): bool
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->remember_token = $token->getValue();
        $this->expiry_timestamp = time() + 60 * 60 * 24 * 30;  // 30 days from now

        $sql = <<<SQL
INSERT INTO remembered_logins (token_hash, user_id, expires_at)
VALUES (:token_hash, :user_id, :expires_at);
SQL;
        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
        //$stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiry_timestamp), PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Update the user's profile
     *
     * @param array $data Data from the edit profile form
     *
     * @return boolean  True if the data was updated, false otherwise
     */
    public function updateProfile(array $data): bool
    {
        $this->name = $data['name'];
        $this->surname = $data['surname'];
        $this->phone = $data['phone'];
        $this->city = $data['city'];
        $this->email = $data['email'];
        $this->id = (int)$data['userID'];

        // Only validate and update the password if a value provided
        if ($data['password'] != '') {
            $this->password = $data['password'];
        }

        $this->validate();

        if (empty($this->errors)) {

            $sql = <<<SQL
UPDATE users
SET name = :name,
    surname = :surname,
    phone = :phone,
    city = :city,
    email = :email  
SQL;

            // Add password if it's set
            if (isset($this->password)) {
                $sql .= ', password_hash = :password_hash';
            }

            $sql .= "\nWHERE id = :id";


            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $this->name);
            $stmt->bindValue(':surname', $this->surname);
            $stmt->bindValue(':phone', $this->phone);
            $stmt->bindValue(':city', $this->city);
            $stmt->bindValue(':email', $this->email);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

            // Add password if it's set
            if (isset($this->password)) {

                $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
                $stmt->bindValue(':password_hash', $password_hash);

            }

            return $stmt->execute();
        }

        return false;
    }
}
