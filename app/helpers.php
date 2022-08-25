<?php

if (!function_exists('strip_accents')) {
    /**
     * This function strips all the accents from a string.
     * See: https://stackoverflow.com/a/35177899
     *
     * @param $string
     *
     * @return false|string
     */
    function strip_accents($string): bool|string
    {
        $transliterator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;',
            Transliterator::FORWARD);
        return $transliterator->transliterate($string);
    }
}
