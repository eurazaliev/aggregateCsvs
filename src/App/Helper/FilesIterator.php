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

    /**
     * Sets the path of the file to be iterated
     *
     * @param string $outDir
     *
     * @return self
     *
     * @throws Exception
     */
    public function setFile($filename, $mode = "r")
    {
        if (!file_exists($filename)) {
            throw new Exception("File not found");
        }
        $this->file = new \SplFileObject($filename, $mode);

        return $this;
    }

    /**
     * Iterates text with generator
     *
     * @return int|count
     */
    protected function iterateText()
    {
        $count = 0;
        while (!$this->file->eof()) {
            yield $this->file->fgets();
            $count++;
        }

        return $count;
    }

    /**
     * Creates new traversable object to iterate the source file
     *
     * @return object \NorewindIterator
     */
    public function iterate()
    {
        return new \NoRewindIterator($this->iterateText());
    }
}
