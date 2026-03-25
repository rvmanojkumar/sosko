<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReviewHelpfulVote extends Model
{
    use HasUuids;

    protected $fillable = [
        'product_review_id',
        'user_id'
    ];

    public $timestamps = true;

    /**
     * Get the review that was voted helpful
     */
    public function review()
    {
        return $this->belongsTo(ProductReview::class, 'product_review_id');
    }

    /**
     * Get the user who voted
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}