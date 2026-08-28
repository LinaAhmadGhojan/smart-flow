<?php

namespace App\Http\Controllers;

use App\Services\ChatAssistantService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(private ChatAssistantService $assistant)
    {
    }

    /** POST /api/chat — free rule-based assistant (no API key required) */
    public function message(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'locale' => 'nullable|in:ar,en',
        ]);

        return response()->json(
            $this->assistant->reply($validated['message'], $validated['locale'] ?? null)
        );
    }
}
