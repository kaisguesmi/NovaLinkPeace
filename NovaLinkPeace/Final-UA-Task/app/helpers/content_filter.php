<?php

if (!function_exists('filter_content')) {
    /**
     * Filter content for bad words
     * 
     * @param string $content The content to filter
     * @return string Filtered content with bad words replaced
     */
    function filter_content($content) {
        static $bannedWords = null;
        
        // Load the bad words only once
        if ($bannedWords === null) {
            $bannedWords = require __DIR__ . '/../../config/badwords.php';
        }
        
        // If the filter_text function exists in config, use it
        if (isset($bannedWords['filter_text']) && is_callable($bannedWords['filter_text'])) {
            return $bannedWords['filter_text']($content);
        }
        
        return $content;
    }
}
