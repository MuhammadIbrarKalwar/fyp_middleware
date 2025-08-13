<?php

namespace App\Http\Controllers\API;

use App\Models\UserProfile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class JobInsightController extends Controller
{
    public function getInsights(Request $request)
    {
        $country=$request->input('country', 'gb');
        $what = $request->input('what', 'dotnet developer');
        //$location = $request->input('location', 'london');


        $app_id = '49313ed2';
        $app_key = '02b57e84603cadd31d4f8d3b10f6d5cc';


        $url = "http://api.adzuna.com/v1/api/jobs/".$country."/search/1";


        $response = Http::get($url, [
            'app_id' => $app_id,
            'app_key' => $app_key,
            'results_per_page' => 20,
            'what' => $what,
            'where' => '',
            'content-type' => 'application/json',
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        } else {
            return response()->json([
                'error' => 'Unable to fetch job data',
                'status' => $response->status()
            ], $response->status());
        }
    }
}
