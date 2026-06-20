<?php
namespace app\validators;

class citizenValidator {
    public static function validate(array $data): array {
        if (empty($data['nik']) || empty($data['name']) || empty($data['email'])) {
            throw new \Exception("NIK, Nama, dan Email wajib diisi.");
        }
        if (strlen($data['nik']) !== 16) {
            throw new \Exception("Format NIK tidak valid, harus 16 digit angka.");
        }
        return $data;
    }
}