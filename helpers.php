<?php

use Smartcat\Includes\Plugin\Activator;
use Smartcat\Includes\Plugin\Deactivator;
use Smartcat\Includes\Services\Posts\PostTypeService;
use Smartcat\Includes\SmartcatWpml;

function smartcat_force_balance_tags($text)
{
    $tagstack = array();
    $stacksize = 0;
    $tagqueue = '';
    $newtext = '';
    // Known single-entity/self-closing tags.
    $single_tags = array('area', 'base', 'basefont', 'br', 'col', 'command', 'embed', 'frame', 'hr', 'img', 'input', 'isindex', 'link', 'meta', 'param', 'source', 'track', 'wbr');
    // Tags that can be immediately nested within themselves.
    $nestable_tags = array('article', 'aside', 'blockquote', 'details', 'div', 'figure', 'object', 'q', 'section', 'span');

    // WP bug fix for comments - in case you REALLY meant to type '< !--'.
    $text = str_replace('< !--', '<    !--', $text);
    // WP bug fix for LOVE <3 (and other situations with '<' before a number).
    $text = preg_replace('#<([0-9]{1})#', '&lt;$1', $text);

    /**
     * Matches supported tags.
     *
     * To get the pattern as a string without the comments paste into a PHP
     * REPL like `php -a`.
     *
     * @see https://html.spec.whatwg.org/#elements-2
     * @see https://html.spec.whatwg.org/multipage/custom-elements.html#valid-custom-element-name
     *
     * @example
     * ~# php -a
     * php > $s = [paste copied contents of expression below including parentheses];
     * php > echo $s;
     */
    $tag_pattern = (
        '#<' . // Start with an opening bracket.
        '(/?)' . // Group 1 - If it's a closing tag it'll have a leading slash.
        '(' . // Group 2 - Tag name.
        // Custom element tags have more lenient rules than HTML tag names.
        '(?:[a-z](?:[a-z0-9._]*)-(?:[a-z0-9._-]+)+)' .
        '|' .
        // Traditional tag rules approximate HTML tag names.
        '(?:[\w:]+)' .
        ')' .
        '(?:' .
        // We either immediately close the tag with its '>' and have nothing here.
        '\s*' .
        '(/?)' . // Group 3 - "attributes" for empty tag.
        '|' .
        // Or we must start with space characters to separate the tag name from the attributes (or whitespace).
        '(\s+)' . // Group 4 - Pre-attribute whitespace.
        '([^>]*)' . // Group 5 - Attributes.
        ')' .
        '>#' // End with a closing bracket.
    );

    while (preg_match($tag_pattern, $text, $regex)) {
        $full_match = $regex[0];
        $has_leading_slash = !empty($regex[1]);
        $tag_name = $regex[2];
        $tag = strtolower($tag_name);
        $is_single_tag = in_array($tag, $single_tags, true);
        $pre_attribute_ws = isset($regex[4]) ? $regex[4] : '';
        $attributes = trim(isset($regex[5]) ? $regex[5] : $regex[3]);
        $has_self_closer = '/' === substr($attributes, -1);

        $newtext .= $tagqueue;

        $i = strpos($text, $full_match);
        $l = strlen($full_match);

        // Clear the shifter.
        $tagqueue = '';
        if ($has_leading_slash) { // End tag.
            // If too many closing tags.
            if ($stacksize <= 0) {
                // $tag = "<$tag></$tag>";
                $tag = "<!--sc-remove--><$tag><!--/sc-remove--></$tag>";
                // Or close to be safe $tag = '/' . $tag.

                // If stacktop value = tag close value, then pop.
            } elseif ($tagstack[$stacksize - 1] === $tag) { // Found closing tag.
                $tag = '</' . $tag . '>'; // Close tag.
                array_pop($tagstack);
                $stacksize--;
            } else { // Closing tag not at top, search for it.
                for ($j = $stacksize - 1; $j >= 0; $j--) {
                    if ($tagstack[$j] === $tag) {
                        // Add tag to tagqueue.
                        for ($k = $stacksize - 1; $k >= $j; $k--) {
                            $tagqueue .= '</' . array_pop($tagstack) . '>';
                            $stacksize--;
                        }
                        break;
                    }
                }
                $tag = '';
            }
        } else { // Begin tag.
            if ($has_self_closer) { // If it presents itself as a self-closing tag...
                // ...but it isn't a known single-entity self-closing tag, then don't let it be treated as such
                // and immediately close it with a closing tag (the tag will encapsulate no text as a result).
                if (!$is_single_tag) {
                    $attributes = trim(substr($attributes, 0, -1)) . "></$tag";
                }
            } elseif ($is_single_tag) { // Else if it's a known single-entity tag but it doesn't close itself, do so.
                $pre_attribute_ws = ' ';
                $attributes .= '/';
            } else { // It's not a single-entity tag.
                // If the top of the stack is the same as the tag we want to push, close previous tag.
                if ($stacksize > 0 && !in_array($tag, $nestable_tags, true) && $tagstack[$stacksize - 1] === $tag) {
                    $tagqueue = '</' . array_pop($tagstack) . '>';
                    $stacksize--;
                }
                $stacksize = array_push($tagstack, $tag);
            }

            // Attributes.
            if ($has_self_closer && $is_single_tag) {
                // We need some space - avoid <br/> and prefer <br />.
                $pre_attribute_ws = ' ';
            }

            $tag = '<' . $tag . $pre_attribute_ws . $attributes . '>';
            // If already queuing a close tag, then put this tag on too.
            if (!empty($tagqueue)) {
                $tagqueue .= $tag;
                $tag = '';
            }
        }
        $newtext .= substr($text, 0, $i) . $tag;
        $text = substr($text, $i + $l);
    }

    // Clear tag queue.
    $newtext .= $tagqueue;


    // Add remaining text.
    $newtext .= $text;

    while ($x = array_pop($tagstack)) {
        $newtext .= '<!--sc-remove--></' . $x . '><!--/sc-remove-->'; // Add remaining tags to close.
    }

    // WP fix for the bug with HTML comments.
    $newtext = str_replace('< !--', '<!--', $newtext);
    $newtext = str_replace('<    !--', '< !--', $newtext);

    return $newtext;
}

function smartcatActivatePlugin()
{
    Activator::activate();
}

function smartcatDeactivatePlugin()
{
    Deactivator::deactivate();
}

function smartcatLoadMainIncludesFile()
{
    require plugin_dir_path(__FILE__) . 'includes/SmartcatWpml.php';
}

function run()
{
    smartcatLoadMainIncludesFile();
    (new SmartcatWpml())->run();
}
