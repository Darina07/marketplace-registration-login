<?php

namespace App\Models;

use PDO;
use \App\Token;

/**
 * Remembered login model
 */
class RememberedLogin extends \Core\Model
{

    /**
     * Find a remembered login model by the token
     *
     * @param string $token The remembered login token
     *
     * @return mixed Remembered login object if found, false otherwise
     */
    public static function findByToken(string $token): mixed
    {
        $token = new Token($token);
        $token_hash = $token->getHash();

        $sql = <<<SQL
SELECT * FROM remembered_logins
WHERE token_hash = :token_hash;
SQL;
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':token_hash', $token_hash);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Get the user model associated with this remembered login
     *
     * @return User The user model
     */
    public function getUser(): User
    {
        return User::findByID($this->user_id);
    }

    /**
     * See if the remember token has expired or not, based on the current system time
     *
     * @return boolean True if the token has expired, false otherwise
     */
    public function hasExpired(): bool
    {
        return strtotime($this->expires_at) < time();
    }

    /**
     * Delete this model
     *
     * @return void
     */
    public function delete(): void
    {
        $sql = <<<SQL
DELETE FROM remembered_logins
WHERE token_hash = :token_hash
SQL;
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':token_hash', $this->token_hash);

        $stmt->execute();
    }
}
