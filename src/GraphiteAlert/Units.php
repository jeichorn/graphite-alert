<?php
namespace GraphiteAlert;

class Units
{
    protected $toRaw = [
        'M' => 'megabytesToBytes',
        ];
    protected $toUnit = [
        'BtoM' => 'bytesToMegabytes'
        ];

    public function toRaw($value)
    {
        if (preg_match('/([0-9]+)(.+)/', $value, $match))
        {

            if (isset($this->toRaw[$match[2]]))
            {
                $method = $this->toRaw[$match[2]];
                return $this->$method($value, $match[1]);
            }

        }
        return $value;
    }

    public function toUnit($unit, $value)
    {
        if (isset($this->toUnit[$unit]))
        {
            $method = $this->toUnit[$unit];
            return $this->$method($value);
        }

        return $value;
    }

    public function megabytesToBytes($value)
    {
        return $value * 1024 * 1024;
    }

    public function bytesToMegabytes($value)
    {
        return number_format(round($value / 1024 / 1024, 2))."M";
    }
}
