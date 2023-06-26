<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];
    protected $table = 'blogs';
    protected $dates = ['deleted_at'];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function comments()
    {
        return $this->hasMany(BlogComment::class);
    }
    public function category()
    {
        return $this->belongsTo(BlogCategory::class,'blog_category_id');
    }
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
