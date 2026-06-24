<?php

header('Content-Type: application/json');

echo json_encode([
    "message"=>"Power Service Running",

    "models"=>[
        "PowerDemand",
        "WeatherLog",
        "Forecast",
        "ZoneInfrastructure"
    ]
]);