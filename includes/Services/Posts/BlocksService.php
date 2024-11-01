<?php

namespace Smartcat\Includes\Services\Posts;

use Smartcat\Includes\Services\Tools\JsonMagician;

class BlocksService
{
    public static function parseContentBlocks(string $postContent): array
    {
        $blocks = parse_blocks($postContent);
        $jsonMagician = new JsonMagician($blocks);
        $dotArrayBlocks = $jsonMagician->getJson(true);
        $filteredBlocks = self::filterBlocks($dotArrayBlocks);
        return self::mapBlocks($filteredBlocks);
    }

    public static function applyTranslatedBlocks($postContent, $translatedBlocks): string
    {
        $blocks = parse_blocks($postContent);

        if (empty($blocks) || empty($translatedBlocks)) {
            return '';
        }

        $jsonMagician = new JsonMagician($blocks);
        $dotArrayBlocks = $jsonMagician->getJson(true);

        $mappedTranslatedBlocks = [];

        foreach ($translatedBlocks as $translatedBlock) {
            if (is_array($translatedBlock['content']) && empty($translatedBlock['content'])) {
                continue;
            }
            $mappedTranslatedBlocks[$translatedBlock['key']] = $translatedBlock['content'];
        }
        foreach ($mappedTranslatedBlocks as $key => $value) {
            if (isset($dotArrayBlocks[$key])) {
                $dotArrayBlocks[$key] = self::clearTempComments($value);
            }
        }

        $blocks = $jsonMagician->undot($dotArrayBlocks);
        return serialize_blocks($blocks);
    }

    private static function mapBlocks(array $blocks): array
    {
        $data = [];
        foreach ($blocks as $key => $value) {
            $data[] = [
                'key' => $key,
                'context' => 'gutenberg',
                'content' => $value
            ];
        }
        return $data;
    }

    private static function filterBlocks(array $blocks): array
    {
        $filteredBlocks = array_filter($blocks, function ($k) {
            return strpos($k, '.innerContent.') !== false || strpos($k, '.data.') !== false;
        }, ARRAY_FILTER_USE_KEY);

        $filteredBlocks = array_filter($filteredBlocks, function ($c) {
            return is_string($c);
        });

        $filteredBlocks = array_filter($filteredBlocks, function ($c) {
            return self::checkContentTranslatability($c);
        });

        $filteredBlocks = array_map(function ($c) {
            return smartcat_force_balance_tags($c);
        }, $filteredBlocks);

        return array_filter($filteredBlocks, function ($c) {
            return strpos($c, 'field_') === false && !empty(str_replace('</div>', '', str_replace("\n", '', $c)));
        });
    }

    private static function clearTempComments($html)
    {
        $html = preg_replace('/|(<!--sc-remove-->).*(<!--\/sc-remove-->)|Uis/', '$1$2', $html);
        $html = str_replace('<!--sc-remove-->', '', $html);
        return str_replace('<!--/sc-remove-->', '', $html);
    }

    private static function checkContentTranslatability($content): bool
    {
        preg_match('/^(\s*<\s*(([^>\s]+)[^>]*)>\s*)+$/', $content, $output);
        return empty($output);
    }
}