<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $table="books";
    protected $fillable = [
        'user_id',
        'Book_name',
        'Book_Description',
        'Book_Author',
        'Book_Image',
        'Price',
        'Quantity'
    ];


    public function user() {
        return $this->belongsTo(User::class);
    }

}
