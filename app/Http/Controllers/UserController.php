<?php

namespace App\Http\Controllers;

use App\Models\Disease;
use App\Models\Prediction;
use App\Models\Quicktest;
use App\Models\Suggestion;
use App\Models\Symptom;
use App\Models\Trackerlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Psy\Readline\Hoa\Console;

class UserController extends Controller
{
    //
    public function dashboard()
    {
        return view('back.user.dashboard');
    }


    public function getQuestions()
    {
        $uniqueSymptoms = Symptom::whereIn('id', function ($query) {
            $query->selectRaw('MIN(id)')
                ->from('symptoms')
                ->groupBy('symptom');
        })
            ->get(['id', 'symptom', 'image', 'disease_id']);

        return response()->json(['message' => 'Symptoms uploaded successfully', 'questions' => $uniqueSymptoms], 200);
    }


    public function testProcessing(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'answers.yes' => 'required|array',
            'answers.yes.*.id' => 'required|integer',
            'answers.no' => 'required|array',
            'answers.no.*.id' => 'required|integer',
        ]);

        // Extract "yes" and "no" answers from the request
        $yesAnswers = $validatedData['answers']['yes'];
        $noAnswers = $validatedData['answers']['no'];

        // Collect the IDs from the "yes" answers
        $ids = array_column($yesAnswers, 'id');

        // Fetch symptoms using the collected IDs
        $symptoms = Symptom::whereIn('id', $ids)->get();

        // Initialize counters for each disease
        $diseaseCounts = [
            1 => 0, // Disease 1 count
            2 => 0, // Disease 2 count
            3 => 0  // Disease 3 count
        ];

        // Loop through the fetched symptoms and search in the database for matches
        foreach ($symptoms as $symptom) {
            $matchedSymptoms = Symptom::where('symptom', $symptom->symptom)->get();

            // Count occurrences for each disease_id based on matched symptoms
            foreach ($matchedSymptoms as $matchedSymptom) {
                if (isset($diseaseCounts[$matchedSymptom->disease_id])) {
                    $diseaseCounts[$matchedSymptom->disease_id]++;
                }
            }
        }

        // Calculate the total number of unique questions (total of yes and no answers)
        $totalUniqueQuestions = count($yesAnswers) + count($noAnswers);

        // Calculate the percentile for each disease
        $diseasePercentiles = array_map(function ($count) use ($totalUniqueQuestions) {
            return $totalUniqueQuestions > 0 ? ($count / $totalUniqueQuestions) * 100 : 0;
        }, $diseaseCounts);


        Quicktest::create([
            'user_id' => Auth::user()->id,
            'percentage_disease_1' => $diseasePercentiles[1],
            'percentage_disease_2' => $diseasePercentiles[2],
            'percentage_disease_3' => $diseasePercentiles[3],
        ]);

        $disease_list = Disease::all();

        return response()->json([
            'message' => 'Successfully intially predicted the disease',
            'disease_percentiles' => $diseasePercentiles,
            'disease_list' => $disease_list,
        ]);
    }

    public function getQuicktest()
    {
        $disease_list = Disease::all();
        $percentage = Quicktest::where('user_id', Auth::user()->id)->latest('updated_at')->get(['id', 'percentage_disease_1', 'percentage_disease_2', 'percentage_disease_3', 'updated_at']);

        return response()->json([
            'message' => 'Successfully Retrieved',
            'disease_percentiles' => $percentage,
            'disease_list' => $disease_list,
        ]);;
    }

    public function upload_retina_image(Request $request)
    {
        // Validate the uploaded image
        $request->validate([
            'retina_image' => 'required|image|mimes:png,jpg,jpeg'
        ]);

        try {
            // Retrieve the uploaded image
            $image = $request->file('retina_image');
            $imageName = $image->getClientOriginalName();
            $imagePath = public_path('images/prediction_images');
            $fullPath = $imagePath . DIRECTORY_SEPARATOR . $imageName;
            $fullPath = str_replace('\\', '/', $fullPath); // Normalize path for compatibility

            // Check if an image with the same name exists and delete it
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }

            // Save the new image
            $image->move($imagePath, $imageName);

            // Generate the public URL for the saved image
            $imageUrl = asset('images/prediction_images/' . $imageName);

            // Send the image path to the Flask API
            $response = Http::asForm()->post('http://127.0.0.1:5000/predict', [
                'image_path' => $fullPath,
            ]);

            // Check if the Flask API returned a valid response
            if ($response->successful()) {
                return response()->json([
                    'message' => 'Prediction Completed',
                    'url' => $imageUrl,
                    'response' => $response->json(),
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Retina uploaded but prediction failed',
                    'url' => $imageUrl,
                    'error' => $response->json(),
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while processing the image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function upload_prediction_data(Request $request)
    {
        $user_id = Auth::user()->id;
        $prediction = $request->prediction["prediction"];
        $prediction_list = $request->prediction["prediction_list"];
        $percentage = 0;
        $predicted_disease = "";
        if ($prediction == 0) {
            $predicted_disease = "No Diabetic Retinopathy";
        } else if ($prediction == 1) {
            $predicted_disease = "Mild Diabetic Retinopathy";
        } else if ($prediction == 2) {
            $predicted_disease = "Moderate Diabetic Retinopathy";
        } else if ($prediction == 3) {
            $predicted_disease = "Severe Diabetic Retinopathy";
        } else if ($prediction == 4) {
            $predicted_disease = "Proliferative Diabetic Retinopathy";
        } else {
            $predicted_disease = "Not identified";
        }
        if (is_array($prediction_list) && !empty($prediction_list)) {
            $max_value = max($prediction_list);
            $percentage = $max_value * 100;
        }
        $prediction_row = Prediction::create([
            'user_id' => $user_id,
            'predicted_disease' => $predicted_disease,
            'percentage' => $percentage,
        ]);

        return response()->json([
            'prediction_id' => $prediction_row->id,
            'message' => 'Successfully uploaded prediction data',
        ]);
    }


    public function getDeeptest()
    {
        $deeptest = Prediction::where('user_id', Auth::user()->id)->latest('updated_at')->get(['id', 'predicted_disease', 'percentage', 'updated_at']);

        return response()->json([
            'message' => 'Detailed predictions',
            'deeptest' => $deeptest,
        ]);
    }

    public function saveQuery(Request $request)
    {

        $validated = $request->validate([
            'prediction_id' => 'required',
            'query' => 'required',
            'response' => 'required',
        ]);

        Suggestion::create($validated);

        return response()->json([
            'message' => 'Query stored',
        ]);
    }


    public function get_prediction_data($prediction_id)
    {
        $prediction_details = Prediction::find($prediction_id);

        $suggestion_list = Suggestion::where('prediction_id', $prediction_id)->get();
        return response()->json([
            'message' => 'Details Retrieved',
            'prediction_details' => $prediction_details,
            'suggestion_list' => $suggestion_list,
        ], 200);
    }

    public function delete_suggestion($suggestion_id)
    {
        // Find the suggestion by ID and delete it
        $suggestion = Suggestion::find($suggestion_id);

        if ($suggestion) {
            $suggestion->delete(); // Delete the row
            return response()->json(['message' => 'Suggestion deleted successfully.'], 200);
        } else {
            return response()->json(['error' => 'Suggestion not found.'], 404);
        }
    }


    public function storeTrackData(Request $request)
    {
        Trackerlist::create([
            'user_id' => Auth::user()->id,
            'class_index' => $request->class_index,
            'heatmap_file' => $request->heatmap_file,
            'optional' => $request->optional,
        ]);
        return response()->json(['success' => true], 200);
    }

    public function getTrackData()
    {
        $data = Trackerlist::where('user_id', Auth::user()->id)->get();
        return response()->json(['success' => true, 'data' => $data], 200);
    }
}
