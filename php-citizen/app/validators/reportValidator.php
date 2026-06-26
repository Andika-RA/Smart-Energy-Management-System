<?php
namespace app\validators;

class reportValidator {
    public static function validate(array $data): array {
        if (empty($data['category']) || empty($data['description'])) {
            throw new \Exception("Kategori dan deskripsi laporan wajib diisi.");
        }
        return $data;
    }
}