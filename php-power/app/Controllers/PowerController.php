<?php

namespace App\Controllers;

class PowerController
{
    public function index()
    {
        return [
            "service" => "Power Service",
            "columns" => [
                "hour",
                "day_of_week",
                "temperature",
                "prev_demand",
                "zone",
                "power_demand"
            ]
        ];
    }
}