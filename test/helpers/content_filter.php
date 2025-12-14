<?php

if (!function_exists('filter_content')) {
    /**
     * Filter content for bad words
     * @param string $content
     * @return string
     */
    function filter_content($content) {
        static $bannedWords = null;
        if ($bannedWords === null) {
            $bannedWords = require __DIR__ . '/../config/badwords.php';
        }
        if (isset($bannedWords['filter_text']) && is_callable($bannedWords['filter_text'])) {
            return $bannedWords['filter_text']($content);
        }
        return $content;
    }
}
