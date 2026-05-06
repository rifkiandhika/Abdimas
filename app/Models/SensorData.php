<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    protected $table = 'sensor_data';
 
    protected $fillable = [
        'device_id',
        'flame_raw',
        'fire',
        'voltage',
        'current',
        'power',
        'energy',
        'deviasi_pct',
        'voltage_ok',
    ];
 
    protected $casts = [
        'fire'        => 'boolean',
        'voltage'     => 'float',
        'current'     => 'float',
        'power'       => 'float',
        'energy'      => 'float',
        'deviasi_pct' => 'float',
        'voltage_ok'  => 'boolean',
    ];
 
    public function scopeDevice($query, string $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }
 
    public function scopeFireOnly($query)
    {
        return $query->where('fire', true);
    }
}
