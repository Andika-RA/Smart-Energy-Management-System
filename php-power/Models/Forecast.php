<?php

namespace App\Models;

class Forecast
{
    public $zone_id;

    public $predicted_demand_kw;

    public $status_level;

    public $forecast_for_time;

    public $model_version;

    public $generated_from;
}