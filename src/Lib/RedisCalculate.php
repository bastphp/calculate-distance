<?php


namespace Dashan\calculateDistance\Lib;

use Illuminate\Support\Facades\Redis;

class RedisCalculate implements Calculate
{
    protected $redisKey;

    protected $unit = 'km';

    protected $order = 'asc';

    protected $limit = 3;

    protected $boundaryLng = [-180,180];

    protected $boundaryLat = [ -85.05112878,85.05112878];

    public function __construct()
    {
        $this->redisKey = $this->uniqName('KEY_');
    }

    public function setUnit($unit)
    {
        $this->unit = $unit;
        return $this;
    }

    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function calculateOneCoordinate($base, $need) :float
    {
        $coordinate[] = $base;
        $coordinate[] = $need;
        $this->geoAdd($coordinate);
        return Redis::GEODIST($this->redisKey,$base[2],$need[2],$this->unit);
    }

    public function calculateRadius($base, $need, $dis) :array
    {
        $this->geoAdd($need);
        return Redis::GEORADIUS($this->redisKey,$base[0],$base[1],$dis,$this->unit,'WITHDIST',$this->order,$this->limit);
    }

    public function calculateOrder($base, $need) :array
    {
        $this->geoAdd($need);
        return Redis::GEORADIUS($this->redisKey,$base[0],$base[1],100000,$this->unit,'WITHDIST',$this->order,$this->limit);
    }

    protected function geoAdd($coordinate)
    {
        Redis::pipeline(function ($pipe) use($coordinate){
            foreach ($coordinate as $item) {
                $pipe->GEOADD($this->redisKey,$item[0],$item[1],$item[2]);
            }
        });
    }

    protected function uniqName($start)
    {
        return $start . uniqid();
    }

}