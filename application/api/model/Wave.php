<?php

namespace app\api\model;

use think\Db;
use think\Model;
class Wave extends Model
{
    protected $table = 'wave';
    protected $append = ['pointOffsetJson', 'monsterPointJson'];

    protected function parseBraceJsonField($value)
    {
        if (is_array($value) || is_object($value) || $value === null || $value === '') {
            return $value;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        $normalized = str_replace(['{', '}'], ['[', ']'], trim($value));
        $decoded = json_decode($normalized, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    public function getPointOffsetJsonAttr($value, $data)
    {
        return $this->parseBraceJsonField($data['pointOffset'] ?? null);
    }

    protected function parseMonsterPointJsonField($value)
    {
        if (is_array($value) || is_object($value) || $value === null || $value === '') {
            return $value;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        $normalized = '['.str_replace(['{', '}'], '', trim($value)).']';
        $decoded = json_decode($normalized, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    public function getMonsterPointJsonAttr($value, $data)
    {
        return $this->parseMonsterPointJsonField($data['monsterPoint'] ?? null);
    }

    /**
     * 获取所有波次
     */
    public function getAll()
    {
        return $this->select();
    }
    
}