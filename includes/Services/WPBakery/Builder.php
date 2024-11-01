<?php

namespace Smartcat\Includes\Services\WPBakery;

class Builder
{
    public function decode($content)
    {
        foreach (SC_WP_BAKERY_SINGLE_TAGS as $tag) {
            $content = preg_replace("/\[($tag.*?)]/", '<${1}>' . "</$tag>", $content);
        }

        $decodedContent = preg_replace('/\[(\/?vc_.*?)]/', '<${1}>', $content);

        foreach (SC_WP_BAKERY_BASE64_TAGS as $tagName) {
            $values = $this->getTagContent($decodedContent, $tagName);
            foreach ($values as $value) {
                $decodedContent = str_replace(
                    $value,
                    rawurldecode(
                        base64_decode($value, true)
                    ),
                    $decodedContent
                );
            }
        }

        return $decodedContent;
    }

    public function encode($content)
    {
        foreach (SC_WP_BAKERY_SINGLE_TAGS as $tag) {
            $content = str_replace("</$tag>", '', $content);
        }

        foreach (SC_WP_BAKERY_BASE64_TAGS as $tagName) {
            $values = $this->getTagContent($content, $tagName);
            foreach ($values as $value) {
                $content = str_replace(
                    $value,
                    base64_encode(rawurlencode($value)),
                    $content
                );
            }
        }

        return preg_replace('/<(\/?vc_.*?)>/', '[${1}]', $content);
    }

    public static function isUsedBuilder($postID): bool
    {
        return get_post_meta($postID, '_wpb_vc_js_status', true) === 'true';
    }

    /**
     * @param $content
     * @param $tagName
     * @return array
     */
    private function getTagContent($content, $tagName)
    {
        $pattern = "#<\s*?$tagName\b[^>]*>(.*?)</$tagName\b[^>]*>#s";
        preg_match_all($pattern, $content, $matches);
        return is_array($matches[1]) ? $matches[1] : [];
    }

    private function isBase64($data): bool
    {
        return base64_encode(base64_decode($data, true)) === $data;
    }
}