<?php
/**
 * CarImage model. Handles gallery rows for a car.
 *
 * SECURITY (File Upload Security): the actual file validation (MIME, extension
 * whitelist, size, rename) is performed in CarController::handleUploads() before
 * a row is ever inserted here. Paths stored are relative to /public.
 */
final class CarImage extends BaseModel
{
    protected string $table = 'car_images';

    public function forCar(int $carId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM car_images WHERE car_id = ? ORDER BY is_primary DESC, id ASC'
        );
        $stmt->execute([$carId]);
        return $stmt->fetchAll();
    }

    public function add(int $carId, string $path, bool $primary = false): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO car_images (car_id, image_path, is_primary) VALUES (?, ?, ?)'
        );
        $stmt->execute([$carId, $path, $primary ? 1 : 0]);
        return (int) $this->db->lastInsertId();
    }

    public function setPrimary(int $carId, int $imageId): void
    {
        $this->db->beginTransaction();
        $c = $this->db->prepare('UPDATE car_images SET is_primary = 0 WHERE car_id = ?');
        $c->execute([$carId]);
        $s = $this->db->prepare('UPDATE car_images SET is_primary = 1 WHERE id = ? AND car_id = ?');
        $s->execute([$imageId, $carId]);
        $this->db->commit();
    }

    public function deleteImage(int $imageId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM car_images WHERE id = ?');
        $stmt->execute([$imageId]);
        $row = $stmt->fetch();
        if ($row) {
            $del = $this->db->prepare('DELETE FROM car_images WHERE id = ?');
            $del->execute([$imageId]);
        }
        return $row ?: null;
    }
}
