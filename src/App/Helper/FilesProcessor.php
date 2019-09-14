<?php
namespace Console\App\Helper;

use Symfony\Component\Finder\Finder as Finder;
use App\Config\MainConfig as MainConfig;

class FilesProcessor
{
    protected $path;
    protected $fileNameMask;
    protected $finder;
    protected $csvFileCaption;

    public function __construct(string $path, Finder $finder)
    {
        $this->path = $path;
        $this->finder = $finder;
        
    }

    public function setFileNameMask (string $fileNameMask) :self
    {
        $this->fileNameMask = $fileNameMask;
        $this->finder->files()->name($this->fileNameMask);
        return $this;
    }

    public function setCsvFileCaption (string $csvFileCaption) :self
    {
        $this->csvFileCaption = $csvFileCaption;
        $this->finder->files()->contains($this->csvFileCaption);
        return $this;
    }

    public function process() 
    {
        $this->finder->ignoreVCS(true);
        $this->finder->files()->in($this->path);

        foreach ($this->finder as $file) {
/////////////////
            $csvFile = new FilesIterator($file);
            $iterator = $csvFile->iterate(); 
////////////////
            $counter = 0;
            foreach ($iterator as $line) {
                if ($counter === 0) {$counter++; continue;}
                if (preg_match(MainConfig::REGEXP, $line)) { 
                    $pieces = explode(";", $line);
                    file_put_contents(MainConfig::OUTDIR . $pieces[0], $line , FILE_APPEND | LOCK_EX);
                }
                $counter++;
            }
        }
        
        $this->finder = new Finder;
        
        $this->path = MainConfig::OUTDIR;
        $this->finder->files()->in($this->path);
        
        file_put_contents(MainConfig::RESULTFILE, $this->csvFileCaption . PHP_EOL, FILE_APPEND | LOCK_EX);

        foreach ($this->finder as $file) {
            /////////////////
            $csvFile = new FilesIterator($file);
            $iterator = $csvFile->iterate(); 
////////////////
            $dateStr['date'] = $file->getFileName();
            $dateStr['A'] = 0;
            $dateStr['B'] = 0;
            $dateStr['C'] = 0;

            $counter = 0;
            foreach ($iterator as $line) {
                if (preg_match(MainConfig::REGEXP, $line)) { 
                    $pieces = explode(";", $line);
                    
                    $dateStr['A'] += floatval($pieces[1]);
                    $dateStr['B'] += floatval($pieces[2]);
                    $dateStr['C'] += floatval($pieces[3]);
                }
            }
            $aggregatedString = implode('; ', $dateStr);
            file_put_contents(MainConfig::RESULTFILE, $aggregatedString . PHP_EOL, FILE_APPEND | LOCK_EX);
        }

    }
}
