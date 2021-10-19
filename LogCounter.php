<?php

const LEVEL_PATTERN = '/test\.(\w+)/';

class LogCounter
{
    private string $filename;       // log filename
    private ?SplFileObject $file;   // file handler
    private array $userFilters;     // array of UserFilters
    private array $stats;           // results statistics array

    /**
     * @param string $filename filename of the log
     * @throws Exception
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->userFilters = [];
        $this->stats = [];

        // open file
        $this->openFile();
    }

    /**
     * Performs one step and returns partial results
     * @throws Exception on file not opening or if lines are too long
     */
    public function stepFileReading(): Generator
    {
        // read line
        while (($line = $this->getLine()) !== false) {

            // skip empty lines
            if (strlen($line)<=0) continue;

            // apply filters
            $line = $this->applyFilters($line);

            if ($line !== false) { // line is not marked as ignored
                // add to statistics
                $this->categorize($line);
            }
            yield $this->stats;
        }

        // close the file
        $this->file = null;
    }

    /**
     * Opens file of throws exception if file does not exist or is not readable
     * @throws Exception
     */
    public function openFile()
    {
        if (!$this->filename) {
            throw new Exception("Filename not set.");
        }
        if (!is_readable($this->filename)) {
            throw new Exception("File cannot be read.");
        }
        $this->file = new SplFileObject($this->filename);
    }

    /**
     * @return bool|string false for end of file or next file line
     */
    public function getLine(): bool|string
    {
        if (!$this->file->eof()) {
            return $this->file->fgets();
        } else {
            return false;
        }
    }

    /**
     * @param $line String to be evaluated
     * @return bool|string false if line should be ignored otherwise modified string based on user filters
     */
    public function applyFilters(string $line): bool|string
    {
        /** @var UserFilter $userFilter */
        foreach ($this->userFilters as $userFilter) {
            if ($userFilter->testIgnorePattern($line)) {
                return false;
            } else {
                $line = $userFilter->performReplace($line);
            }
        }
        return $line;
    }

    /**
     * Determines the correct category and increases its counter
     * @param string $line String to be categorized
     */
    private function categorize(string $line)
    {
        if (preg_match(LEVEL_PATTERN, $line, $matches)) {
            $level = strtolower($matches[1]);
        } else {
            $level = "Unknown";
        }

        if (array_key_exists($level, $this->stats)) {
            $this->stats[$level]++;
        } else {
            $this->stats[$level] = 1;
        }

    }

    /**
     * @param UserFilter $userFilter
     */
    public function addUserFilter(UserFilter $userFilter): void
    {
        array_push($this->userFilters, $userFilter);
    }

}