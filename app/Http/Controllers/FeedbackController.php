<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    /**
     * Store a newly created feedback in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'username' => 'required|string',
            'page' => 'required|string',
            'message' => 'required|string',
        ]);

        // Create a new Feedback model instance with the validated data
        $feedback = Feedback::create($validatedData);

        // Optionally, you can return a response indicating success
        return response()->json(['message' => 'Din tilbakemelding er mottatt', 'data' => $feedback], 201);
    }
}
