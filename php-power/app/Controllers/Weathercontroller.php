<?php

namespace App\Controllers;

class WeatherController
{
    public function index()
    {
        return [
            "model" => "WeatherLog",
            "status" => "ready"
        ];
    }
}