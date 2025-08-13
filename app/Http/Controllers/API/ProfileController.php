<?php

namespace App\Http\Controllers\API;

use App\Models\UserProfile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function saveProfile(Request $request)
    {

        //return $request;
        try {

            $validator = Validator::make($request->all(), [
                'name'     => 'required|string|max:255',
                'contact_number' => 'required|min:6',
                'dob'     => 'required',
                'address'     => 'nullable',
                // // New validations
                // 'skills'         => 'array|required',
                // 'skills.*'       => 'string',
                // 'interests'      => 'array|required',
                // 'interests.*'    => 'string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors()
                ], 422);
            }

            $profile = UserProfile::create([
                'user_id'        => $request->user_id,
                'name'           => $request->name,
                'contact_number' => $request->contact_number,
                'address'        => $request->address,
                'dob'            => $request->dob,
            ]);

            // // ✅ Step 3: Save Skills (one-to-many)
            // foreach ($request->skills as $skillName) {
            //     $profile->skills()->create([
            //         'name' => $skillName,
            //         'user_id'        => $request->user_id
            //     ]);
            // }

            // // ✅ Step 4: Save Interests (one-to-many)
            // foreach ($request->interests as $interestName) {
            //     $profile->interests()->create([
            //         'name' => $interestName,
            //         'user_id'        => $request->user_id
            //     ]);
            // }

            return response()->json([
                'message' => 'Success!'
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage(),
            ], 500);
        }
    }
}
