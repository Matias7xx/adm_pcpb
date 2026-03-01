<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticiaView extends Model
{
  public $timestamps = false;

  protected $table = 'noticia_views';

  protected $fillable = ['noticia_id', 'session_id', 'viewed_at'];

  protected $casts = [
    'viewed_at' => 'datetime',
  ];

  public function noticia()
  {
    return $this->belongsTo(Noticia::class);
  }
}
