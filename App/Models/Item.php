<?php

namespace App\Models;

use App\Auth;
use PDO;

/**
 * Item model
 */
class Item extends \Core\Model
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
        }
    }

    /**
     * Save the item model with the current property values
     *
     * @return boolean  True if the item was saved, false otherwise
     */
    public function save(): bool
    {

        $this->validate();

        if (empty($this->errors)) {
            $sql = <<<SQL
INSERT INTO items (users_id, categories_id, title, description)
VALUES (:users_id, :categories_id, :title, :description);
SQL;
            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':users_id', Auth::getUser()->id, PDO::PARAM_INT);
            $stmt->bindValue(':categories_id', (int)$this->category, PDO::PARAM_INT);
            $stmt->bindValue(':title', $this->title);
            $stmt->bindValue(':description', $this->description);


            return $stmt->execute();
        }

        return false;
    }

    /**
     * Validate current property values, adding validation error messages to the errors array property
     *
     * @return void
     */
    public function validate(): void
    {
        if ($this->title == '') {
            $this->errors[] = 'Title is required';
        }

        if ($this->description == '') {
            $this->errors[] = 'Description is required';
        }
        if (!isset($this->category) && !in_array($this->category, $this->getCategories())) {
            $this->errors[] = 'Category is required';
        }
    }

    /**
     * Retrieve all items belonging to a specific user
     *
     * @param int $userId User ID
     * @return array Array of items
     */
    public static function getAllByUserId(int $userId): array
    {
        $sql = <<<EOF
SELECT i.id, c.name as category_title, i.title, i.description, i.created_at, i.image_path
FROM items i
LEFT JOIN categories c on c.id = i.categories_id
WHERE users_id = :users_id;
EOF;
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':users_id', $userId, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Retrieve an item by its ID
     *
     * @param int $itemId Item ID
     * @return mixed Item object if found, false otherwise
     */
    public static function getById(int $itemId): mixed
    {
        $sql = <<<EOF
SELECT * FROM items WHERE id = :id;
EOF;
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $itemId, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        return $stmt->fetch();

    }

    /**
     * Update an existing item
     *
     * @param array $data Data to update the item
     * @return boolean True if the item was updated successfully, false otherwise
     */
    public function update(array $data): bool
    {
        $this->title = $data['title'];
        $this->description = $data['description'];
        $this->category = $data['categories_id'];
        $this->id = $data['id'];
        $this->validate();

        if (empty($this->errors)) {
            $sql = <<<EOF
UPDATE items
SET title = :title, description = :description, categories_id = :categories_id
WHERE id = :id;
EOF;
            $db = static::getDB();
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':categories_id', $this->category, PDO::PARAM_INT);
            $stmt->bindValue(':title', $this->title);
            $stmt->bindValue(':description', $this->description);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

            return $stmt->execute();
        }

        return false;

    }

    /**
     * Delete an existing item
     *
     * @param int $itemId Item ID
     * @return boolean True if the item was deleted successfully, false otherwise
     */
    public function delete(int $itemId): bool
    {
        $sql = <<<EOF
DELETE FROM items WHERE id = :id;
EOF;
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $itemId, PDO::PARAM_INT);

        return $stmt->execute();

    }

    /**
     * Get All Categories
     *
     * @return array Array of categories
     */
    public function getCategories(): array
    {
        $sql = <<<EOF
SELECT * FROM categories;
EOF;
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get All Items (It is for the home page)
     *
     * @return array Array of items
     */
    public function getAllItems(): array
    {
        $sql = <<<EOF
SELECT i.id, u.name, u.email, u.phone, c.name as category_title, i.title, i.description, i.created_at, i.image_path
FROM items i
LEFT JOIN categories c on c.id = i.categories_id
LEFT JOIN users u on i.users_id = u.id;
EOF;
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        return $stmt->fetchAll();
    }
}