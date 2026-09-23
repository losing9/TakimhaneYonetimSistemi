<?php

namespace App\Http\Controllers;

use App\Models\Station;

class StationController extends Controller
{
    public function qrLabel(Station $station)
    {
        return view('stations.qr-label', ['station' => $station]);
    }
}
