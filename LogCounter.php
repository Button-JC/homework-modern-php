<?php

const LEVEL_PATTERN = '/test\.(\w+)/';

class LogCounter
{
    private string $filename;       // log filename
    private ?SplFileObject $file;   // file handler
    private array $userFilters;     // array of UserFilters
    private array $stats;           // results statistics array
    private array $watchers;        // list of callback functions

    /**
     * @param string $filename filename of the log
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->userFilters = [];
        $this->stats = [];
        $this->watchers = [];
    }

    /**
     * Main cycle for reading file
     * @throws Exception on file not opening or if lines are too long
     */
    public function readFile(): array
    {
        // open file
        $this->openFile();

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
            // run callbacks
            $this->updateWatchers();
        }

        // close the file
        $this->file = null;
        // return final count
        return $this->stats;
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
     * Runs all current callback functions with up-to-date data
     */
    private function updateWatchers()
    {
        foreach ($this->watchers as $watcher) {
            call_user_func($watcher, $this->stats);
        }

    }

    /**
     * @param String $watcher
     */
    public function setWatcher(string $watcher): void
    {
        array_push($this->watchers, $watcher);
    }

    /**
     * @param String $watcher
     */
    public function unsetWatcher(string $watcher): void
    {
        unset($this->watchers[$watcher]);
    }

    /**
     * @param UserFilter $userFilter
     */
    public function addUserFilter(UserFilter $userFilter): void
    {
        array_push($this->userFilters, $userFilter);
    }

}