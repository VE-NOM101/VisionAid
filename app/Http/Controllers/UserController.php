<?php

namespace App\Http\Controllers;

use App\Models\Disease;
use App\Models\Quicktest;
use App\Models\Symptom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $percentage = Quicktest::where('user_id', Auth::user()->id)->get(['id', 'percentage_disease_1', 'percentage_disease_2', 'percentage_disease_3','updated_at']);

        return response()->json([
            'message' => 'Successfully Retrieved',
            'disease_percentiles' => $percentage,
            'disease_list' => $disease_list,
        ]);;
    }
}
