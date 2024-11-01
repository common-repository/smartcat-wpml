<?php

namespace Smartcat\Includes\Services\Tools;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class JsonMagician
{
    private $json;
    private $keys = [];

    public function __construct($json = NULL)
    {
        if (!is_null($json)) {
            $this->setJson($json);
        }
    }

    public function getJson(bool $useDotKeys = false)
    {
        if (isset($this->json)) {
            return $useDotKeys
                ? $this->getDotArray($this->json)
                : $this->json;
        }
        return NULL;
    }

    public function setJson($json)
    {
        if (is_string($json) && $this->isJson($json)) {
            return $this->json = json_decode($json, true);
        } elseif (is_array($json)) {
            return $this->json = $json;
        } else {
            throw new \Exception('Invalid json data');
        }
    }

    public function getKeys(): array
    {
        if (!isset($this->json)) {
            throw new \Exception('Json not initialized');
        }

        if (!$this->isAssoc($this->json)) {
            throw new \Exception('Json not assoc format');
        }

        return $this->getDotKeys($this->json);
    }

    public function insert($key, $value)
    {
        return false;
    }

    public function extract($keys): array
    {
        if (!isset($this->json)) {
            throw new \Exception('JSON is empty');
        }

        $jsonWithDots = $this->getJson(true);
        $data = [];

        if (is_string($keys)) {
            $data = $this->extractValue($jsonWithDots, $keys);
        } else {
            foreach ($keys as $key) {
                $data = array_merge($data, $this->extractValue($jsonWithDots, $key));
            }
        }

        return $data;
    }

    protected function isJson(string $json): bool
    {
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }

    protected function isAssoc(array $array): bool
    {
        return !empty($array) && array_keys($array) !== range(0, count($array) - 1);
    }

    protected function getDotKeys(array $array): array
    {
        $ritit = new RecursiveIteratorIterator(new RecursiveArrayIterator($this->json));
        $result = [];
        foreach ($ritit as $leafValue) {
            $keys = [];
            foreach (range(0, $ritit->getDepth()) as $depth) {
                $keys[] = is_int($ritit->getSubIterator($depth)->key()) ? '[]' : $ritit->getSubIterator($depth)->key();
            }
            $result[] = join('.', $keys);
        }
        $uniqueKeys = array_unique($result, SORT_STRING);
        return array_values(array_map(function ($key) {
            return [
                'key' => $key,
                'displayed' => str_replace(['.[].', '.[]'], ['.', ''], $key)
            ];
        }, $uniqueKeys));
    }

    protected function getDotArray(array $array): array
    {
        $ritit = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
        $result = [];
        foreach ($ritit as $leafValue) {
            $keys = [];
            foreach (range(0, $ritit->getDepth()) as $depth) {
                $keys[] = $ritit->getSubIterator($depth)->key();
            }
            $result[join('.', $keys)] = $leafValue;
        }
        return $result;
    }

    protected function extractValue(array $jsonWithDots, string $key): array
    {
        $data = [];
        if (strpos($key, '[]')) {
            $i = 0;
            while (true) {
                $preparedKey = str_replace('[]', $i, $key);
                if (isset($jsonWithDots[$preparedKey])) {
                    $data[$preparedKey] = $jsonWithDots[$preparedKey];
                } else {
                    break;
                }
                $i++;
            }
        } else {
            if (isset($jsonWithDots[$key])) {
                $data[$key] = $jsonWithDots[$key];
            }
        }
        return $data;
    }

    public function undot(array $dotNotationArray)
    {
        $array = [];
        foreach ($dotNotationArray as $key => $value) {
            $this->set($array, $key, $value);
        }

        return $array;
    }

    protected function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;

        return $array;
    }
}