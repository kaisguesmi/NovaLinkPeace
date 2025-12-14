<?php

return [
    'banned_words' => [
        'fuck',
        'stfu',
        'nigga',
        'nigger',
        'putain',
        'merde',
        'couille',
        'son of a bitch'
    ],

    'filter_text' => function($text) {
        $bannedWords = [
            'fuck',
            'stfu',
            'nigga',
            'nigger',
            'putain',
            'merde',
            'couille',
            'son of a bitch'
        ];
        foreach ($bannedWords as $word) {
            $pattern = '/\\b' . preg_quote($word, '/') . '\\b/i';
            $replacement = function($matches) {
                $word = $matches[0];
                $firstChar = mb_substr($word, 0, 1);
                $remaining = str_repeat('*', mb_strlen($word) - 1);
                return $firstChar . $remaining;
            };
            $text = preg_replace_callback($pattern, $replacement, $text);
        }
        return $text;
    }
];
