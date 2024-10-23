<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "user_id",
        "parent_id",
        "name",
        "description",
        "monthly_budget",
        "is_active",
    ];

    protected $casts = [
        "monthly_budget" => "float",
        "is_active" => "boolean",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function parent()
    {
        return $this->belongsTo(TransactionCategory::class, "parent_id");
    }

    public function children()
    {
        return $this->hasMany(TransactionCategory::class, "parent_id");
    }


    public static function getCategoryOptionGroup()
    {
        // Get parent categories (where parent_id is null) with their children
        $categories = self::with('children')
            ->whereNull('parent_id')
            ->where('is_active', true) // Optional: Get only active categories if needed
            ->where("user_id", auth()->user()->id)
            ->get();

        // Initialize an empty array to hold the final structured output
        $categoryOptions = [];

        // Loop through parent categories
        foreach ($categories as $parentCategory) {
            // Initialize an empty array to store child options
            $childrenOptions = [];

            // Loop through child categories and add them to the child options array
            foreach ($parentCategory->children as $childCategory) {
                $childrenOptions[$childCategory->id] = $childCategory->name;
            }

            // Add the parent category and its child options to the output
            $categoryOptions[$parentCategory->name] = $childrenOptions;
        }

        return $categoryOptions;
    }
}
