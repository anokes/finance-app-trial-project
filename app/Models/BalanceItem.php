<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BalanceItem extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'balance_items';


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'entry_date',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'label', 'entry_date', 'amount', 'user_id'
    ];

    public static function insertData($data)
    {
        DB::table('balance_items')->insert($data);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
    
}
