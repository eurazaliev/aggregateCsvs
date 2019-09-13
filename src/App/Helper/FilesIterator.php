<?php
namespace Console\App\Helper;

class FilesIterator
{
    protected $file;

    public function __construct($filename, $mode = "r")
    {
        if (!file_exists($filename)) {
            throw new Exception("File not found");
        }
        $this->file = new \SplFileObject($filename, $mode);
    }

    protected function iterateText()
    {
        $count = 0;
        while (!$this->file->eof()) {

            yield $this->file->fgets();

            $count++;
        }
        return $count;
    }

    public function iterate()
    {
            return new \NoRewindIterator($this->iterateText());
    }
}
