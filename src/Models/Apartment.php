<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table apartments
 * ImmoApp (L2GIB, 2024-2025)
 * Représente un appartement dans un bâtiment, appartenant à un propriétaire
 */
class Apartment
{
    private $pdo;
    protected $id;
    protected $building_id;
    protected $owner_id;
    protected $number;
    protected $floor;
    protected $area;
    protected $rooms;
    protected $bedrooms;
    protected $bathrooms;
    protected $toilets;
    protected $living_rooms;
    protected $kitchens;
    protected $has_balcony;
    protected $amenities;
    protected $type_id;
    protected $rent_amount;
    protected $charges_amount;
    protected $status;
    protected $price;
    protected $created_at;
    protected $updated_at;
    protected $deleted_at;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getBuildingId() { return $this->building_id; }
    public function getOwnerId() { return $this->owner_id; }
    public function getNumber() { return $this->number; }
    public function getFloor() { return $this->floor; }
    public function getArea() { return $this->area; }
    public function getRooms() { return $this->rooms; }
    public function getBedrooms() { return $this->bedrooms; }
    public function getBathrooms() { return $this->bathrooms; }
    public function getToilets() { return $this->toilets; }
    public function getLivingRooms() { return $this->living_rooms; }
    public function getKitchens() { return $this->kitchens; }
    public function getHasBalcony() { return $this->has_balcony; }
    public function getAmenities() { return $this->amenities; }
    public function getTypeId() { return $this->type_id; }
    public function getRentAmount() { return $this->rent_amount; }
    public function getChargesAmount() { return $this->charges_amount; }
    public function getStatus() { return $this->status; }
    public function getPrice() { return $this->price; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getDeletedAt() { return $this->deleted_at; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setBuildingId($building_id) { $this->building_id = $building_id; }
    protected function setOwnerId($owner_id) { $this->owner_id = $owner_id; }
    protected function setNumber($number) { $this->number = $number; }
    protected function setFloor($floor) { $this->floor = $floor; }
    protected function setArea($area) { $this->area = $area; }
    protected function setRooms($rooms) { $this->rooms = $rooms; }
    protected function setBedrooms($bedrooms) { $this->bedrooms = $bedrooms; }
    protected function setBathrooms($bathrooms) { $this->bathrooms = $bathrooms; }
    protected function setToilets($toilets) { $this->toilets = $toilets; }
    protected function setLivingRooms($living_rooms) { $this->living_rooms = $living_rooms; }
    protected function setKitchens($kitchens) { $this->kitchens = $kitchens; }
    protected function setHasBalcony($has_balcony) { $this->has_balcony = $has_balcony; }
    protected function setAmenities($amenities) { $this->amenities = $amenities; }
    protected function setTypeId($type_id) { $this->type_id = $type_id; }
    protected function setRentAmount($rent_amount) { $this->rent_amount = $rent_amount; }
    protected function setChargesAmount($charges_amount) { $this->charges_amount = $charges_amount; }
    protected function setStatus($status) { $this->status = $status; }
    protected function setPrice($price) { $this->price = $price; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    protected function setDeletedAt($deleted_at) { $this->deleted_at = $deleted_at; }

    /**
     * Crée un objet Apartment à partir des données de la base
     * @param array $data
     * @return Apartment
     */
    protected static function fromData(array $data): Apartment
    {
        $apartment = new self();
        $apartment->setId($data['id']);
        $apartment->setBuildingId($data['building_id']);
        $apartment->setOwnerId($data['owner_id']);
        $apartment->setNumber($data['number']);
        $apartment->setFloor($data['floor']);
        $apartment->setArea($data['area']);
        $apartment->setRooms($data['rooms']);
        $apartment->setBedrooms($data['bedrooms']);
        $apartment->setBathrooms($data['bathrooms']);
        $apartment->setToilets($data['toilets']);
        $apartment->setLivingRooms($data['living_rooms']);
        $apartment->setKitchens($data['kitchens']);
        $apartment->setHasBalcony($data['has_balcony']);
        $apartment->setAmenities($data['amenities'] ? json_decode($data['amenities'], true) : null);
        $apartment->setTypeId($data['type_id']);
        $apartment->setRentAmount($data['rent_amount'] ?? null);
        $apartment->setChargesAmount($data['charges_amount'] ?? null);
        $apartment->setStatus($data['status']);
        $apartment->setPrice($data['price'] ?? null);
        $apartment->setCreatedAt($data['created_at']);
        $apartment->setUpdatedAt($data['updated_at'] ?? null);
        $apartment->setDeletedAt($data['deleted_at'] ?? null);
        return $apartment;
    }

    /**
     * Trouve un appartement par ID
     * @param int $id
     * @return Apartment|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM apartments WHERE id = ? AND deleted_at IS NULL');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche de l’appartement : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return Apartment|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Récupère tous les appartements
     * @return array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM apartments WHERE deleted_at IS NULL ORDER BY number');
            $apartments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $apartments[] = self::fromData($data);
            }
            return $apartments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des appartements : " . $e->getMessage());
        }
    }

    /**
     * Récupère le premier appartement (par numéro)
     * @return Apartment|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM apartments WHERE deleted_at IS NULL ORDER BY number ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération du premier appartement : " . $e->getMessage());
        }
    }

    /**
     * Crée un nouvel appartement
     * @param array $data
     * @return Apartment
     */
    public static function create(array $data)
    {
        try {
            $pdo = Database::getInstance();
            // Vérifier les dépendances
            if (!Building::find($data['building_id'])) {
                throw new PDOException("Building ID invalide");
            }
            if (!Owner::find($data['owner_id'])) {
                throw new PDOException("Owner ID invalide");
            }
            // Note : apartment_types non implémenté, à vérifier
            $stmt = $pdo->prepare('
                INSERT INTO apartments (
                    building_id, owner_id, number, floor, area, rooms, bedrooms, bathrooms, toilets,
                    living_rooms, kitchens, has_balcony, amenities, type_id, rent_amount, charges_amount,
                    status, price, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $data['building_id'],
                $data['owner_id'],
                $data['number'],
                $data['floor'],
                $data['area'],
                $data['rooms'],
                $data['bedrooms'],
                $data['bathrooms'],
                $data['toilets'],
                $data['living_rooms'],
                $data['kitchens'],
                $data['has_balcony'],
                $data['amenities'] ? json_encode($data['amenities']) : null,
                $data['type_id'],
                $data['rent_amount'] ?? null,
                $data['charges_amount'] ?? null,
                $data['status'],
                $data['price'] ?? null
            ]);
            $id = $pdo->lastInsertId();
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création de l’appartement : " . $e->getMessage());
        }
    }

    /**
     * Met à jour un appartement
     * @param int $id
     * @param array $data
     * @return Apartment|null
     */
    public static function update($id, array $data)
    {
        try {
            $pdo = Database::getInstance();
            // Vérifier les dépendances si modifiées
            if (!empty($data['building_id']) && !Building::find($data['building_id'])) {
                throw new PDOException("Building ID invalide");
            }
            if (!empty($data['owner_id']) && !Owner::find($data['owner_id'])) {
                throw new PDOException("Owner ID invalide");
            }
            $existing = self::find($id);
            if (!$existing) {
                throw new PDOException("Appartement introuvable");
            }
            $stmt = $pdo->prepare('
                UPDATE apartments
                SET building_id = ?, owner_id = ?, number = ?, floor = ?, area = ?, rooms = ?, bedrooms = ?,
                    bathrooms = ?, toilets = ?, living_rooms = ?, kitchens = ?, has_balcony = ?, amenities = ?,
                    type_id = ?, rent_amount = ?, charges_amount = ?, status = ?, price = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $data['building_id'] ?? $existing->getBuildingId(),
                $data['owner_id'] ?? $existing->getOwnerId(),
                $data['number'] ?? $existing->getNumber(),
                $data['floor'] ?? $existing->getFloor(),
                $data['area'] ?? $existing->getArea(),
                $data['rooms'] ?? $existing->getRooms(),
                $data['bedrooms'] ?? $existing->getBedrooms(),
                $data['bathrooms'] ?? $existing->getBathrooms(),
                $data['toilets'] ?? $existing->getToilets(),
                $data['living_rooms'] ?? $existing->getLivingRooms(),
                $data['kitchens'] ?? $existing->getKitchens(),
                $data['has_balcony'] ?? $existing->getHasBalcony(),
                isset($data['amenities']) ? json_encode($data['amenities']) : ($existing->getAmenities() ? json_encode($existing->getAmenities()) : null),
                $data['type_id'] ?? $existing->getTypeId(),
                $data['rent_amount'] ?? $existing->getRentAmount(),
                $data['charges_amount'] ?? $existing->getChargesAmount(),
                $data['status'] ?? $existing->getStatus(),
                $data['price'] ?? $existing->getPrice(),
                $id
            ]);
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la mise à jour de l’appartement : " . $e->getMessage());
        }
    }

    /**
     * Supprime un appartement (soft delete)
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('UPDATE apartments SET deleted_at = NOW() WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression de l’appartement : " . $e->getMessage());
        }
    }

    /**
     * Trouve les appartements par building_id
     * @param int $buildingId
     * @return array
     */
    public static function findByBuildingId($buildingId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM apartments WHERE building_id = ? AND deleted_at IS NULL');
            $stmt->execute([$buildingId]);
            $apartments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $apartments[] = self::fromData($data);
            }
            return $apartments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des appartements par building_id : " . $e->getMessage());
        }
    }

    /**
     * Trouve les appartements par owner_id
     * @param int $ownerId
     * @return array
     */
    public static function findByOwnerId($ownerId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM apartments WHERE owner_id = ? AND deleted_at IS NULL');
            $stmt->execute([$ownerId]);
            $apartments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $apartments[] = self::fromData($data);
            }
            return $apartments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des appartements par owner_id : " . $e->getMessage());
        }
    }

    /**
     * Récupère le bâtiment de l’appartement
     * @return Building|null
     */
    public function building()
    {
        return Building::find($this->building_id);
    }

    /**
     * Récupère le propriétaire de l’appartement
     * @return Owner|null
     */
    public function owner()
    {
        return Owner::find($this->owner_id);
    }
}