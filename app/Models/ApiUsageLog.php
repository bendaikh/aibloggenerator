<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'model',
        'operation',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'estimated_cost',
        'generation_mode',
        'article_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'estimated_cost' => 'decimal:6',
    ];

    /**
     * Relationship: ApiUsageLog belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: ApiUsageLog belongs to Article (optional)
     */
    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Calculate estimated cost based on token usage and model
     * 
     * Pricing as of Feb 2026 (per 1M tokens):
     * - GPT-4o: $2.50 input, $10.00 output
     * - GPT-4-turbo: $10.00 input, $30.00 output
     * - GPT-3.5-turbo: $0.50 input, $1.50 output
     */
    public static function calculateCost(string $model, int $promptTokens, int $completionTokens): float
    {
        $pricing = [
            'gpt-4o' => ['input' => 2.50, 'output' => 10.00],
            'gpt-4-turbo' => ['input' => 10.00, 'output' => 30.00],
            'gpt-3.5-turbo' => ['input' => 0.50, 'output' => 1.50],
        ];

        $modelPricing = $pricing[$model] ?? $pricing['gpt-4o'];

        $inputCost = ($promptTokens / 1_000_000) * $modelPricing['input'];
        $outputCost = ($completionTokens / 1_000_000) * $modelPricing['output'];

        return round($inputCost + $outputCost, 6);
    }

    /**
     * Log API usage
     */
    public static function logUsage(
        int $userId,
        string $provider,
        string $model,
        string $operation,
        int $promptTokens,
        int $completionTokens,
        string $generationMode = 'full_ai',
        ?int $articleId = null,
        array $metadata = []
    ): self {
        $totalTokens = $promptTokens + $completionTokens;
        $estimatedCost = self::calculateCost($model, $promptTokens, $completionTokens);

        return self::create([
            'user_id' => $userId,
            'provider' => $provider,
            'model' => $model,
            'operation' => $operation,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $totalTokens,
            'estimated_cost' => $estimatedCost,
            'generation_mode' => $generationMode,
            'article_id' => $articleId,
            'metadata' => $metadata,
        ]);
    }
}
