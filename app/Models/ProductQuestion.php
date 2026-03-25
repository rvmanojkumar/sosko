<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductQuestion extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'product_id',
        'user_id',
        'question',
        'is_answered'
    ];

    protected $casts = [
        'is_answered' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'is_answered' => false,
    ];

    /**
     * Get the product that this question belongs to
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who asked the question
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the answers for this question
     */
    public function answers()
    {
        return $this->hasMany(ProductAnswer::class, 'product_question_id');
    }

    /**
     * Get the latest answer
     */
    public function latestAnswer()
    {
        return $this->hasOne(ProductAnswer::class, 'product_question_id')->latest();
    }

    /**
     * Get the first answer
     */
    public function firstAnswer()
    {
        return $this->hasOne(ProductAnswer::class, 'product_question_id')->oldest();
    }

    /**
     * Check if question has any answers
     */
    public function hasAnswers()
    {
        return $this->answers()->exists();
    }

    /**
     * Mark as answered
     */
    public function markAsAnswered()
    {
        $this->update(['is_answered' => true]);
    }

    /**
     * Mark as unanswered
     */
    public function markAsUnanswered()
    {
        $this->update(['is_answered' => false]);
    }

    /**
     * Get formatted question with user info
     */
    public function getFormattedQuestionAttribute()
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'asked_by' => $this->user ? $this->user->name : 'Anonymous',
            'asked_by_avatar' => $this->user ? $this->user->profile_photo_url : null,
            'asked_at' => $this->created_at->diffForHumans(),
            'is_answered' => $this->is_answered,
            'answers_count' => $this->answers()->count(),
        ];
    }

    /**
     * Scope for unanswered questions
     */
    public function scopeUnanswered($query)
    {
        return $query->where('is_answered', false);
    }

    /**
     * Scope for answered questions
     */
    public function scopeAnswered($query)
    {
        return $query->where('is_answered', true);
    }

    /**
     * Scope for questions of a specific product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope for questions by a specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for recent questions
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}