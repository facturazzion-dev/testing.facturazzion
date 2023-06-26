<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;
use CfdiUtils\Utils\Format;

class Tax extends Model
{
    use SoftDeletes,RevisionableTrait;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'taxes';


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeLocal($query)
    {
        return $query->where('is_local', true);
    }

    public function scopeNonLocal($query)
    {
        return $query->where('is_local', false)->orWhereNull('is_local');
    }

    public function isLocal()
    {
        return $this->is_local === 1;
    }

    public function isTraslado()
    {
        return $this->tax_type === 'Traslado';
    }

    public function convertToSatArray(float $amount, int $decimals = 6)
    {
        if ($this->isLocal()) {
            if ($this->isTraslado()) {
                return [
                    'ImpLocTrasladado' => $this->name,
                    'Importe' => Format::number($amount * $this->percentage, 2),
                    'TasadeTraslado' => Format::number(100 * $this->percentage, 2),
                ];
            } else {
                return [
                    'ImpLocRetenido' => $this->name,
                    'Importe' => Format::number($amount * $this->percentage, 2),
                    'TasadeRetencion' => Format::number(100 * $this->percentage, 2),
                ];
            }
        } else {
            return [
                'Base' => Format::number($amount, 6),
                'Impuesto' => $this->tax,
                'TipoFactor' => $this->factor_type,
                'TasaOCuota' => Format::number($this->percentage, $decimals),
                'Importe' => Format::number($amount * $this->percentage, $decimals),
            ];
        }
    }
}
