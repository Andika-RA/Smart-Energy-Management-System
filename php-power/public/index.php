<?php

header('Content-Type: application/json');

echo json_encode([
    "service" => "Power Service",
    "status" => "running",
    "columns" => [
        "hour",
        "day_of_week",
        "temperature",
        "prev_demand",
        "zone",
        "power_demand"
    ]
]);