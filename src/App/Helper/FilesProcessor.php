<?php
namespace Console\App\Helper;

use Symfony\Component\Finder\Finder as Finder;

class FilesProcessor
{
    protected $path;
    protected $fileNameMask;
    protected $finder;
    protected $csvFileCaption;

    public function __construct(string $path, Finder $finder )
    {
        $this->path = $path;
        $this->finder = $finder;
    }
    
    public function setFileNameMask (string $fileNameMask) :self
    {
        $this->finder->files()->name($fileNameMask);
        return $this;
    }
    
    public function setCsvFileCaption (string $csvFileCaption) :self
    {
        $this->finder->files()->contains($csvFileCaption);
        return $this;
    }

    public function process() 
    {
        $this->finder->ignoreVCS(true);
        $this->finder->files()->in($this->path);
        
        foreach ($this->finder as $file) {
            echo $file->getRealPath(), PHP_EOL;
        
            $csvFile = new FilesIterator($file);
 
            $iterator = $csvFile->iterate(); 
            foreach ($iterator as $line) {
                echo $line;
            }
        }
    }
}
