<?php

class UserFilter
{
    private string $ignorePattern;
    private string $replacePattern;
    private string $replacement;

    public function setIgnorePattern($ignorePattern)
    {
        $this->ignorePattern = $ignorePattern;
    }

    public function setReplacePattern($replacePattern, $replacement)
    {
        $this->replacePattern = $replacePattern;
        $this->replacement = $replacement;
    }

    public function testIgnorePattern($line): bool
    {
        if (isset($this->ignorePattern)){
            //echo($line. " ". $this->ignorePattern." ".preg_match($this->ignorePattern, $line));
            return preg_match($this->ignorePattern, $line);
        } else {
            return false;
        }
    }

    public function performReplace($line)
    {
        if (isset($this->replacePattern)) {
            return preg_replace($this->replacePattern, $this->replacement, $line);
            //if(preg_match($this->replacePattern, $line)){
            //    return $this->replacement;
            //}
        } else {
            return $line;
        }

    }
}