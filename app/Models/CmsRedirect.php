<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsRedirect extends Model
{
    use HasFactory;

    protected $fillable = ['from_path', 'to_path', 'status_code', 'hits'];

    protected $casts = [
        'status_code' => 'integer',
        'hits' => 'integer',
    ];

    public static function findForPath(string $path): ?self
    {
        return self::where('from_path', ltrim($path, '/'))->first();
    }

    public function recordHit(): void
    {
        $this->increment('hits');
    }
}
