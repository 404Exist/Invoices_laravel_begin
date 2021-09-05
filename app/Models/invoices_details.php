<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class invoices_details extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function section()
    {
        return $this->belongsTo('App\Models\sections', 'Section');
    }
}
