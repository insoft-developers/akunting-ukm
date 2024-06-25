<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Account extends Model
{
    use HasFactory;
    protected $table = 'ml_accounts';
    protected $fillable = [
        "uuid",
        "email",
        "username",
        "fullname",
        "password",
        "roles",
        "role_code",
        "status",
        "is_upgraded",
        "is_soft_delete",
        "recovery_code",
        "recovery_code_duration",
        "token",
        "created",
        "referal_source",
        "referal_code",
        "is_active"
        
    ];

    public $timestamps = false;

    
}
