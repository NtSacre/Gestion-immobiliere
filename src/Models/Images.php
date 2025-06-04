```php
<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table images
 * ImmoImmoApp (L2GIB, 2024-2025)
 * Représente une image associée à une entité (appartement, bâtiment, utilisateur, etc.)
 */
class Images
{
    private $pdo;
    protected $id;
    protected $entity_type;
    protected $entity_id;
    protected $path;
    protected $alt_text;
    protected $order;
    protected $created_at;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getEntityType() { return $this->entity_type; }
    public function getEntityId() { return $this->entity_id; }
    public function getPath() { return $this->path; }
    public function getAltText() { return $this->alt_text; }
    public function getOrder() { return $this->order; }
    public function getCreatedAt() { return $this->created_at; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setEntityType($entity_type) { $this->entity_type = $entity_type; }
    protected function setEntityId($entity_id) { $this->entity_id = $entity_id; }
    protected function setPath($path) { $this->path = $path; }
    protected function setAltText($alt_text) { $this->alt_text = $alt_text; }
    protected function setOrder($order) { $this->order = $order; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }

    /**
     * Crée un objet Images à partir des données de la base
     * @param array $data
     * @return Images
     */
    protected static function fromData(array $data): Images
    {
        $image = new self();
        $image->setId($data['id']);
        $image->setEntityType($data['entity_type']);
        $image->setEntityId($data['entity_id']);
        $image->setPath($data['path']);
        $image->setAltText($data['alt_text'] ?? null);
        $image->setOrder($data['order']);
        $image->setCreatedAt($data['created_at']);
        return $image;
    }

    /**
     * Trouve une image par ID
     * @param int $id
     * @return Images|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM images WHERE id = ?');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche de l’image : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return Images|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Récupère toutes les images
     * @return array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM images ORDER BY created_at DESC');
            $images = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $images[] = self::fromData($data);
            }
            return $images;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des images : " . $e->getMessage());
        }
    }

    /**
     * Récupère la première image (par date de création)
     * @return Images|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM images ORDER BY created_at ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération de la première image : " . $e->getMessage());
        }
    }

    /**
     * Crée une nouvelle image
     * @param array $data
     * @return Images
     */
    public static function create(array $data)
    {
        try {
            $pdo = Database::getInstance();
            // Validation basique de entity_type
            $validEntityTypes = ['apartment', 'building', 'user']; // À ajuster selon vos besoins
            if (!in_array($data['entity_type'], $validEntityTypes)) {
                throw new PDOException("Type d’entité invalide");
            }
            // Vérifier l’existence de l’entité
            $model = match($data['entity_type']) {
                'apartment' => Apartment::find($data['entity_id']),
                'building' => Building::find($data['entity_id']),
                'user' => User::find($data['entity_id']),
                default => null
            };
            if (!$model) {
                throw new PDOException("ID d’entité invalide pour {$data['entity_type']}");
            }
            $stmt = $pdo->prepare('
                INSERT INTO images (
                    entity_type, entity_id, path, alt_text, `order`, created_at
                ) VALUES (?, ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([
                $data['entity_type'],
                $data['entity_id'],
                $data['path'],
                $data['alt_text'] ?? null,
                $data['order']
            ]);
            $id = $pdo->lastInsertId();
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création de l’image : " . $e->getMessage());
        }
    }

    /**
     * Met à jour une image
     * @param int $id
     * @param array $data
     * @return Images|null
     */
    public static function update($id, array $data)
    {
        try {
            $pdo = Database::getInstance();
            $existing = self::find($id);
            if (!$existing) {
                throw new PDOException("Image introuvable");
            }
            // Validation basique de entity_type si modifié
            if (!empty($data['entity_type'])) {
                $validEntityTypes = ['apartment', 'building', 'user']; // À ajuster
                if (!in_array($data['entity_type'], $validEntityTypes)) {
                    throw new PDOException("Type d’entité invalide");
                }
            }
            // Vérifier l’existence de l’entité si modifiée
            if (!empty($data['entity_type']) && !empty($data['entity_id'])) {
                $model = match($data['entity_type']) {
                    'apartment' => Apartment::find($data['entity_id']),
                    'building' => Building::find($data['entity_id']),
                    'user' => User::find($data['entity_id']),
                    default => null
                };
                if (!$model) {
                    throw new PDOException("ID d’entité invalide pour {$data['entity_type']}");
                }
            }
            $stmt = $pdo->prepare('
                UPDATE images
                SET entity_type = ?, entity_id = ?, path = ?, alt_text = ?, `order` = ?
                WHERE id = ?
            ');
            $stmt->execute([
                $data['entity_type'] ?? $existing->getEntityType(),
                $data['entity_id'] ?? $existing->getEntityId(),
                $data['path'] ?? $existing->getPath(),
                $data['alt_text'] ?? $existing->getAltText(),
                $data['order'] ?? $existing->getOrder(),
                $id
            ]);
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la mise à jour de l’image : " . $e->getMessage());
        }
    }

    /**
     * Supprime une image
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('DELETE FROM images WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression de l’image : " . $e->getMessage());
        }
    }

    /**
     * Trouve les images par entity_type et entity_id
     * @param string $entityType
     * @param int $entityId
     * @return array
     */
    public static function findByEntity($entityType, $entityId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM images WHERE entity_type = ? AND entity_id = ? ORDER BY `order` ASC');
            $stmt->execute([$entityType, $entityId]);
            $images = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $images[] = self::fromData($data);
            }
            return $images;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des images par entité : " . $e->getMessage());
        }
    }
}