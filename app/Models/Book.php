<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $validated)
 * @method static find($id)
 * @method static where(array $array)
 * @method static whereBetween(string $string, array $array)
 */
class Book extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'isbn',
        'authors',
        'country',
        'number_of_pages',
        'publisher',
        'release_date',
    ];
}
