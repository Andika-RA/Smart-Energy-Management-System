<?php

namespace App\Controllers;

class ForecastController
{
    public function index()
    {
        return [
            "model" => "Forecast",
            "status" => "ready"
        ];
    }
}