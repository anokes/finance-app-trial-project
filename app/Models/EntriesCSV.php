<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class EntriesCSV extends Model
{
    use HasFactory;

    protected $table = 'csv_entries';

    protected $fillable = [
        'user_id',
        'processed',
        'path'
    ];

    //flags whether or not the CSV has been processed
    public function scopeNotProcessed(Builder $query)
    {
             return $this->where('processed', '=', false);
    }

    

}
