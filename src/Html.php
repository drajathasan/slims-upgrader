<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-22 14:08:53
 * @modify date 2022-11-22 15:02:47
 * @license GPLv3
 * @desc [description]
 */

namespace Drajathasan\SlimsUpgrader;

class Html
{
    private static array $voidElement = [
        'area','base','br','col','command',
        'embed','hr','img','input','keygen','link',
        'meta','param','source','track','wbr'
    ];

    public static function write(string $element, $content = '', array $attributes = [])
    {
        return trim(self::parseAttribute(self::cleanElement($element), $attributes) . 
        trim(is_callable($content) ? $content() : (!in_array($element, self::$voidElement) ? $content : '')) . 
        trim(!in_array($element, self::$voidElement) ? self::cleanElement('/' . $element) : ''));
    }

    public static function js($content = '', array $attribute = ['type' => 'text/javascript'])
    {
        return self::write('script', $content, $attribute);
    }

    public static function cleanElement(string $element)
    {
        return '<' . trim(preg_replace('/[^a-z0-9\/]/i', '', $element)) . '>';
    }

    public static function parseAttribute(string $element, $attributes = [])
    {
        $formatedAttributes = [];
        foreach ($attributes as $attribute => $value) $formatedAttributes[] = xssFree($attribute) . '="' . xssFree($value) . '"';
        return str_replace('>', (count($attributes) ? ' ' . implode(' ', $formatedAttributes) : '') . '>', $element);
    }

    public static function __callStatic($element, $arguments)
    {
        if (!isset($arguments[0])) $arguments[0] = '';
        if (is_array($arguments[0])) $arguments = array_merge([''], $arguments);
        return self::write($element, ...$arguments);
    }
}