<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AddressController extends Controller
{
    protected $data;

    public function __construct()
    {
        $path = storage_path('app/philippines_addresses.json');
        if (!file_exists($path)) {
            $this->data = ['regions' => []];
        } else {
            $json = file_get_contents($path);
            $this->data = json_decode($json, true) ?: ['regions' => []];
        }
    }

    /**
     * Get all regions.
     */
    public function regions()
    {
        $regions = collect($this->data['regions'] ?? [])->map(function ($r) {
            return [
                'code' => $r['code'],
                'name' => $r['name'],
            ];
        });

        return response()->json($regions);
    }

    /**
     * Get provinces by region code.
     */
    public function provinces($regionCode)
    {
        $region = collect($this->data['regions'] ?? [])->firstWhere('code', $regionCode);
        if (!$region) {
            return response()->json([]);
        }

        $provinces = collect($region['provinces'] ?? [])->map(function ($p) {
            return [
                'code' => $p['code'],
                'name' => $p['name'],
            ];
        });

        return response()->json($provinces);
    }

    /**
     * Get cities/municipalities by region code and province code.
     */
    public function cities($regionCode, $provinceCode)
    {
        $region = collect($this->data['regions'] ?? [])->firstWhere('code', $regionCode);
        if (!$region) {
            return response()->json([]);
        }

        $province = collect($region['provinces'] ?? [])->firstWhere('code', $provinceCode);
        if (!$province) {
            return response()->json([]);
        }

        $cities = collect($province['cities'] ?? [])->map(function ($c) {
            return ['name' => $c['name']];
        });

        return response()->json($cities);
    }

    /**
     * Get barangays by region, province, and city name.
     */
    public function barangays($regionCode, $provinceCode, $cityName)
    {
        $region = collect($this->data['regions'] ?? [])->firstWhere('code', $regionCode);
        if (!$region) {
            return response()->json([]);
        }

        $province = collect($region['provinces'] ?? [])->firstWhere('code', $provinceCode);
        if (!$province) {
            return response()->json([]);
        }

        $cityDecoded = urldecode($cityName);
        $city = collect($province['cities'] ?? [])->firstWhere('name', $cityDecoded);
        if (!$city) {
            return response()->json([]);
        }

        $barangays = collect($city['barangays'] ?? [])->map(function ($b) {
            return ['name' => $b];
        });

        return response()->json($barangays);
    }
}
