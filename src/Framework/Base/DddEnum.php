<?php
namespace Ronghz\LaravelDdd\Framework\Base;

/**
 * 枚举类型基类，实现name value text三者之间的互相转换
 * Class DddEnum
 * @package Ronghz\LaravelDdd\Framework\Base
 */
abstract class DddEnum
{
    protected static $texts = [];

    /**
     * 根据枚举值取枚举名称
     * @param $value
     * @param bool $toLower
     * @return string|null
     */
    public static function getName($value, bool $toLower = false): ?string
    {
        $constArr = array_flip(self::getConstList());
        if (!isset($constArr[$value])) {
            return null;
        }
        return $toLower ? strtolower($constArr[$value]) : $constArr[$value];
    }

    /**
     * 根据枚举值取描述文本
     * @param $value
     * @return string|null
     */
    public static function getText($value): ?string
    {
        if (!isset(static::$texts[$value])) {
            return null;
        }
        return static::$texts[$value];
    }

    /**
     * 根据枚举名取枚举值
     * @param string $constName
     * @return int|null
     */
    public static function getByName(string $constName): ?int
    {
        $constName = strtoupper($constName);
        $constArr = self::getConstList();
        if (!isset($constArr[$constName])) {
            return null;
        }
        return $constArr[$constName];
    }

    /**
     * 根据描述文本取枚举值
     * @param string $text
     * @return int|null
     */
    public static function getByText(string $text): ?int
    {
        $textMapValue = array_flip(static::$texts);
        if (!isset($textMapValue[$text])) {
            return null;
        }
        return $textMapValue[$text];
    }

    /**
     * 根据变量名取描述文本
     * @param $name
     * @return string|null
     */
    public static function getTextByName($name): ?string
    {
        if (!isset(static::$texts[$name])) {
            return null;
        }
        return static::$texts[$name];
    }

    /**
     * 取全部枚举变量值列表
     * @param string $valueType
     * @return array
     * @throws \ReflectionException
     */
    public static function getConstList(string $valueType = ''): array
    {
        $reflectionClass = new \ReflectionClass(static::class);
        $constArr = $reflectionClass->getConstants();

        if ($valueType) {
            $constArr = array_map(function ($value) use ($valueType) {
                settype($value, $valueType);
                return $value;
            }, $constArr);
        }

        return $constArr;
    }

    /**
     * 取全部枚举变量名列表
     * @return array
     * @throws \ReflectionException
     */
    public static function getNameList(): array
    {
        $constNames = array_keys(static::getConstList());

        return array_map(function ($constName) {
            return mb_strtolower($constName);
        }, $constNames);
    }

    /**
     * 取全部的描述文本列表
     * @return array
     */
    public static function getTextList(): array
    {
        return static::$texts;
    }
}
