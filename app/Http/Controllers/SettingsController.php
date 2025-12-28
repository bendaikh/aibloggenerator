<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index(): Response
    {
        $user = auth()->user();
        
        return Inertia::render('SuperAdmin/Settings', [
            'settings' => [
                'openai_api_key_set' => !empty($user->openai_api_key),
                'openai_api_key_masked' => $this->maskApiKey($user->openai_api_key),
                'ai_model' => $user->ai_model ?? 'gpt-4o',
                'ai_default_tone' => $user->ai_default_tone ?? 'conversational',
            ]
        ]);
    }

    /**
     * Update AI settings.
     */
    public function updateAiSettings(Request $request)
    {
        $validated = $request->validate([
            'openai_api_key' => 'nullable|string',
            'ai_model' => 'required|string|in:gpt-4o,gpt-4-turbo,gpt-3.5-turbo',
            'ai_default_tone' => 'required|string|in:professional,casual,friendly,formal,conversational',
        ]);

        $user = auth()->user();

        // Only update API key if a new one is provided
        if (!empty($validated['openai_api_key'])) {
            // The User model's 'encrypted' cast handles encryption automatically
            $user->openai_api_key = $validated['openai_api_key'];
        }

        $user->ai_model = $validated['ai_model'];
        $user->ai_default_tone = $validated['ai_default_tone'];
        $user->save();

        return redirect()->route('superadmin.settings')
            ->with('success', 'AI settings saved successfully!');
    }

    /**
     * Test OpenAI connection.
     */
    public function testAiConnection()
    {
        $user = auth()->user();

        if (empty($user->openai_api_key)) {
            return response()->json([
                'success' => false,
                'message' => 'No OpenAI API key configured. Please save your API key first.'
            ]);
        }

        try {
            // The User model's 'encrypted' cast handles decryption automatically
            $apiKey = $user->openai_api_key;
            
            $client = \OpenAI::client($apiKey);
            
            $result = $client->chat()->create([
                'model' => $user->ai_model ?? 'gpt-4o',
                'messages' => [
                    ['role' => 'user', 'content' => 'Say "Connection successful!" in exactly those words.'],
                ],
                'max_tokens' => 20,
            ]);

            $response = $result->choices[0]->message->content ?? '';

            return response()->json([
                'success' => true,
                'message' => 'Connection successful! Model: ' . ($user->ai_model ?? 'gpt-4o'),
                'response' => $response
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mask API key for display.
     * Note: The key passed here is already decrypted by the model's 'encrypted' cast
     */
    private function maskApiKey(?string $key): string
    {
        if (empty($key)) {
            return '';
        }

        if (strlen($key) > 10) {
            return substr($key, 0, 7) . '...' . substr($key, -4);
        }
        return '***';
    }
}

