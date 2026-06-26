<?php
// app/validators/ReportValidator.php
namespace app\validators;

class ReportValidator {
    public static function validate(array $data): array {
        if (empty($data['category']) || empty($data['description'])) {
            throw new \Exception("Kategori dan deskripsi laporan wajib diisi.");
        }
        return $data;
    }
}
