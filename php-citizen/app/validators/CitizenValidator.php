<?php
// app/validators/CitizenValidator.php
namespace app\validators;

class CitizenValidator {
    public static function validate(array $data): array {
        if (empty($data['nik']) || empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            throw new \Exception("NIK, Nama, Email, dan Password wajib diisi.");
        }
        if (strlen($data['nik']) !== 16) {
            throw new \Exception("Format NIK tidak valid, harus 16 digit angka.");
        }
        if (strlen($data['password']) < 8) {
            throw new \Exception("Password minimal 8 karakter.");
        }
        return $data;
    }
}
