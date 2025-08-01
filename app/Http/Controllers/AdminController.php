<?php

namespace App\Http\Controllers;

use App\Models\Disease;
use App\Models\Quicktest;
use App\Models\Symptom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Psy\Readline\Hoa\Console;

class AdminController extends Controller
{
    //
    public function dashboard()
    {
        return view('back.admin.dashboard');
    }

    public function getUsers()
    {
        $users = User::latest()->get();

        return $users;
    }

    public function changeRole($id, Request $request)
    {
        // Validate the role input
        $validated = $request->validate([
            'role' => 'required|integer|in:1,2,4', // Allow only specific roles
        ]);

        $user = User::findOrFail($id); // Throw 404 if user is not found

        $user->update([
            'role' => $validated['role'],
        ]);

        return response()->json(['success' => true]);
    }

    public function getDisease()
    {
        return Disease::all();
    }

    public function updateDiseases(Request $request)
    {
        // Ensure required data is provided
        $request->validate([
            'disease1' => 'required|string|max:255',
            'disease2' => 'required|string|max:255',
            'disease3' => 'required|string|max:255',
            'no_of_symptoms' => 'required|integer|min:1',
        ]);

        try {
            $disease1 = Disease::findOrFail(1);
            $disease1->disease_name = $request->disease1;
            $disease1->no_of_symptoms = $request->no_of_symptoms;
            $disease1->save();

            $disease1 = Disease::findOrFail(2);
            $disease1->disease_name = $request->disease2;
            $disease1->no_of_symptoms = $request->no_of_symptoms;
            $disease1->save();

            $disease1 = Disease::findOrFail(3);
            $disease1->disease_name = $request->disease3;
            $disease1->no_of_symptoms = $request->no_of_symptoms;
            $disease1->save();

            return response()->json(['message' => 'Disease updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while updating the disease.'], 500);
        }
    }



    // public function upload_symptom($id, Request $request){

    //     if ($request->hasFile('profile_picture')) {

    //         $previousPath = $request->user()->getRawOriginal('avatar');

    //         $link = Storage::put('/photos', $request->file('profile_picture'));

    //         $request->user()->update(['avatar' => $link]);

    //         Storage::delete($previousPath);

    //         return response()->json(['message' => 'Profile picture uploaded successfully!'],200);
    //     }
    // }


    // public function upload_symptom($id, Request $request)
    // {
    //     // Validate incoming data
    //     $validatedData = $request->validate([
    //         'symptoms' => 'required|array',
    //         'symptoms.*.id' => 'required|integer', // Validate 'id' key
    //         'symptoms.*.sym' => 'required|string|max:255',
    //         'symptoms.*.image' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:2048',
    //     ]);

    //     $symptoms = $validatedData['symptoms'];

    //     foreach ($symptoms as $symptomData) {
    //         if ($symptomData['image']->isValid()) {
    //             // Generate a custom file name
    //             $extension = $symptomData['image']->getClientOriginalExtension();
    //             $customFileName = "disease_{$id}_sym_{$symptomData['id']}.{$extension}";
    //             $storagePath = "symptom_images/{$customFileName}";

    //             // Check if a symptom already exists for this disease and ID
    //             $existingSymptom = Symptom::where('disease_id', $id)
    //                 ->where('id', $symptomData['id']) // Match by 'id'
    //                 ->first();

    //             if ($existingSymptom) {
    //                 // Delete the old image if it exists
    //                 if ($existingSymptom->image && Storage::exists($existingSymptom->image)) {
    //                     Storage::delete($existingSymptom->image);
    //                 }

    //                 // Update the existing symptom
    //                 $existingSymptom->update([
    //                     'symptom' => $symptomData['sym'],
    //                     'image' => $storagePath,
    //                 ]);
    //             } else {
    //                 // Create a new symptom record
    //                 Symptom::create([
    //                     'id' => $symptomData['id'],
    //                     'disease_id' => $id,
    //                     'symptom' => $symptomData['sym'],
    //                     'image' => $storagePath,
    //                 ]);
    //             }

    //             // Store the new image in the specified location
    //             $symptomData['image']->storeAs('symptom_images', $customFileName);

    //             Log::info("Symptom stored: {$symptomData['sym']} with image at {$storagePath}");
    //         }
    //     }

    //     return response()->json(['message' => 'Symptoms uploaded successfully'], 200);
    // }

    // public function upload_symptom($id, Request $request)
    // {
    //     // Validate incoming data
    //     $validatedData = $request->validate([
    //         'symptoms' => 'required|array',
    //         'symptoms.*.id' => 'required|integer', // Validate 'id' key
    //         'symptoms.*.sym' => 'required|string|max:255',
    //         'symptoms.*.image' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:2048',
    //     ]);

    //     $symptoms = $validatedData['symptoms'];

    //     foreach ($symptoms as $symptomData) {
    //         if ($symptomData['image']->isValid()) {
    //             // Generate a custom file name
    //             $extension = $symptomData['image']->getClientOriginalExtension();
    //             $customFileName = "disease_{$id}_sym_{$symptomData['id']}.{$extension}";

    //             // Define the target public directory
    //             $publicPath = public_path('images/symptom_images');

    //             // Ensure the directory exists
    //             if (!file_exists($publicPath)) {
    //                 mkdir($publicPath, 0755, true);
    //             }

    //             $storagePath = "images/symptom_images/{$customFileName}";

    //             // Check if a symptom already exists for this disease and ID
    //             $existingSymptom = Symptom::where('disease_id', $id)
    //                 ->where('id', $symptomData['id']) // Match by 'id'
    //                 ->first();

    //             if ($existingSymptom) {
    //                 // Delete the old image if it exists
    //                 $oldImagePath = public_path($existingSymptom->image);
    //                 if ($existingSymptom->image && file_exists($oldImagePath)) {
    //                     unlink($oldImagePath);
    //                 }

    //                 // Update the existing symptom
    //                 $existingSymptom->update([
    //                     'symptom' => $symptomData['sym'],
    //                     'image' => $storagePath,
    //                 ]);
    //             } else {
    //                 // Create a new symptom record
    //                 Symptom::create([
    //                     'id' => $symptomData['id'],
    //                     'disease_id' => $id,
    //                     'symptom' => $symptomData['sym'],
    //                     'image' => $storagePath,
    //                 ]);
    //             }

    //             // Move the uploaded file to the public directory
    //             $symptomData['image']->move($publicPath, $customFileName);

    //             Log::info("Symptom stored: {$symptomData['sym']} with image at {$storagePath}");
    //         }else{

    //         }
    //     }

    //     return response()->json(['message' => 'Symptoms uploaded successfully'], 200);
    // }

    public function upload_symptom($id, Request $request)
    {


        $symptoms = $request['symptoms'];
        foreach ($symptoms as $symptomData) {
            // Check if a symptom already exists for this disease and ID
            $existingSymptom = Symptom::where('disease_id', $id)
                ->where('id', $symptomData['id']) // Match by 'id'
                ->first();

            // If an image is uploaded
            if (file_exists($symptomData['image'])) {
                // Generate a custom file name
                $extension = $symptomData['image']->getClientOriginalExtension();
                $customFileName = "disease_{$id}_sym_{$symptomData['id']}.{$extension}";

                // Define the target public directory
                $publicPath = public_path('images/symptom_images');

                // Ensure the directory exists
                if (!file_exists($publicPath)) {
                    mkdir($publicPath, 0755, true);
                }

                $storagePath = "images/symptom_images/{$customFileName}";

                // If an existing symptom has an image, delete the old image
                if ($existingSymptom && $existingSymptom->image) {
                    $oldImagePath = public_path($existingSymptom->image);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // Move the uploaded file to the public directory
                $symptomData['image']->move($publicPath, $customFileName);

                // Update or create the symptom record with the new image
                if ($existingSymptom) {
                    $existingSymptom->update([
                        'symptom' => $symptomData['sym'],
                        'image' => $storagePath,
                    ]);
                } else {
                    Symptom::create([
                        'id' => $symptomData['id'],
                        'disease_id' => $id,
                        'symptom' => $symptomData['sym'],
                        'image' => $storagePath,
                    ]);
                }
            } else {
                // If no image is uploaded, retain the existing image (if any)
                if ($existingSymptom) {
                    $existingSymptom->update([
                        'symptom' => $symptomData['sym'], // Only update the symptom text
                    ]);
                } else {
                    // Create a new symptom without an image
                    Symptom::create([
                        'id' => $symptomData['id'],
                        'disease_id' => $id,
                        'symptom' => $symptomData['sym'],
                        'image' => null, // Set the image field to null if no file is provided
                    ]);
                }
            }
        }

        return response()->json(['message' => 'Symptoms uploaded successfully'], 200);
    }

    public function getSymptoms($id)
    {
        $symptoms = Symptom::where('disease_id', $id)->get();
        return response()->json($symptoms, 200);
    }

    public function getInfo() {

        $disease_list = Disease::all();

        // Initialize a frequency array
        $frequencies = [
            'disease_1' => 0,
            'disease_2' => 0,
            'disease_3' => 0,
        ];

        // Fetch all rows from the quicktests table
        $quickTests = Quicktest::all();

        foreach ($quickTests as $test) {
            // Determine the disease with the highest percentage
            $maxPercentage = max(
                $test->percentage_disease_1,
                $test->percentage_disease_2,
                $test->percentage_disease_3
            );

            // Increment the frequency for the disease with the highest percentage
            if ($maxPercentage === $test->percentage_disease_1) {
                $frequencies['disease_1']++;
            } elseif ($maxPercentage === $test->percentage_disease_2) {
                $frequencies['disease_2']++;
            } elseif ($maxPercentage === $test->percentage_disease_3) {
                $frequencies['disease_3']++;
            }
        }

        // Return the frequencies
        return response()->json(['message' => 'Successfully retrieved', 'disease_list' => $disease_list, 'frequencies' => $frequencies]);
    }
}
