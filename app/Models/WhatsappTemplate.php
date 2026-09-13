<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    protected $table = 'whatsapp_templates';
    protected $fillable = ['name','code','content','variables','is_active','created_by'];
    protected $casts = ['variables' => 'array', 'is_active' => 'boolean'];

    public function render(array $vars): string
    {
        $out = $this->content;
        foreach ($vars as $k => $v) {
            $out = str_replace('{'.$k.'}', $v, $out);
            $out = str_replace('{{'.$k.'}}', $v, $out);
        }
        return $out;
    }
}
