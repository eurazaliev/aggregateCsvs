<?php
namespace Console\App\Helper;

use Exception;

class FilesIterator
{
    // получаем файл, отдаю простой итератор

    /**
     * @var string path to the file to read data
     */
    protected $file;

    public function setFile($filename, $mode = "r")
    {
        if (!file_exists($filename)) {
            throw new Exception("File not found");
        }
        $this->file = new \SplFileObject($filename, $mode);

        return $this;
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
