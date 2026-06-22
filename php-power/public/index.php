<?php

header('Content-Type: application/json');

echo json_encode([
    "service"=>"Power Service",
    "status"=>"running",

    "models"=>[
        "PowerDemand",
        "WeatherLog",
        "Forecast",
        "ZoneInfrastructure"
    ]
]);