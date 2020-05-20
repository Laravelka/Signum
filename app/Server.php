<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
	protected $fillable = ['ip', 'name', 'super_email', 'super_password'];
}
