<?php

/**
 * Class UserFilter
 * Allows ignore and replacement pattern
 */
class UserFilter
{
    private string $ignorePattern;      // pattern marking string to be ignored
    private string $replacePattern;     // pattern marking substring for replacement
    private string $replacement;        // replacement for previous pattern

    /**
     * @param $ignorePattern String pattern
     */
    public function setIgnorePattern(string $ignorePattern)
    {
        $this->ignorePattern = $ignorePattern;
    }

    /**
     * @param string $replacePattern
     * @param string $replacement
     */
    public function setReplacePattern(string $replacePattern, string $replacement)
    {
        $this->replacePattern = $replacePattern;
        $this->replacement = $replacement;
    }

    /**
     * Test provided string against ignore pattern
     * @param string $line to be tested
     * @return bool true if $line should be ignored
     */
    public function testIgnorePattern(string $line): bool
    {
        if (isset($this->ignorePattern)) {
            return preg_match($this->ignorePattern, $line);
        } else {
            return false;
        }
    }

    /**
     * Performs replacement on line with replacePattern for replacement
     * @param string $line for replacement search
     * @return string line with replaced string or unchanged original line if pattern is not found
     */
    public function performReplace(string $line): string
    {
        if (isset($this->replacePattern)) {
            return preg_replace($this->replacePattern, $this->replacement, $line);
        } else {
            return $line;
        }

    }
}