<?php
/**
 * This file contain general helper functions for the application
 */


/**
 * generate slugs
 * @throws Exception
 */

function formatSentenceCase(string $word): string
{
    return ucwords(strtolower($word), " \t\r\n\f\v'");
}

