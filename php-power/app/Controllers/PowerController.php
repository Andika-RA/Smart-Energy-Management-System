<?php

namespace App\Controllers;

class PowerController
{
    public function index()
    {
        return [
            "service"=>"Power Service",
            "status"=>"running"
        ];
    }
}