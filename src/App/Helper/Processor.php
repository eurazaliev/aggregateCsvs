<?php
namespace Console\App\Helper;

use Symfony\Component\Finder\Finder as Finder;
use App\Config\MainConfig as MainConfig;

class Processor
{
    /** это класс из файлов, где хранятся отсортированные по дате данные
      * формирует выходной файл, как требует задача
      * ниже конструктор, 2 сеттера и основной метод **/

    protected $path;
    protected $finder;
    protected $csvFileCaption;
    protected $fileIterator;

    public function __construct(Finder $finder, \Console\App\Helper\FilesIterator $fileIterator)
    {
        $this->finder = $finder;
        $this->fileIterator = $fileIterator;
    }

    public function setCsvFileCaption (string $csvFileCaption) :self
    {
        $this->csvFileCaption = $csvFileCaption;
        return $this;
    }

    public function setPath (string $path) :self
    {
        $this->path = $path;
        return $this;
    }

    /** а в этой функции просто итеративно парсим
      * аггрегированные по дате файлы, суммируем результат и кладем в выходной файл
      * сколько бы файлов у нас небыло, перебираем итератором - память не кончается  **/

    public function processResult()
    {
        $this->finder = Finder::create()->files()->in($this->path);
        isset($this->csvFileCaption)
            ? file_put_contents(MainConfig::RESULTFILE,
                 $this->csvFileCaption . PHP_EOL, FILE_APPEND | LOCK_EX)
        : null;

        // у меня в кажом итерируемом файле находятся строки данных в соответствии с датой
        foreach ($this->finder as $file) {
            /** файлик может быть большим, поэтому его тоже итерируем построчно,
              * а не читаем целиком в память **/
            $aggregatedString = implode(MainConfig::DIVIDER . ' ', $this->aggregateFileData($file));
            file_put_contents(MainConfig::RESULTFILE, $aggregatedString . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }

    public function clearResultFile() : self
    {
        if (file_exists(MainConfig::RESULTFILE)) {
            unlink(MainConfig::RESULTFILE);
        }

        return $this;
    }

    protected function aggregateFileData($file) : array
    {
            $this->fileIterator->setFile($file);
            $iterator = $this->fileIterator->iterate();

            $dateStr['date'] = $file->getFileName();
            $dateStr['A'] = 0;
            $dateStr['B'] = 0;
            $dateStr['C'] = 0;

            foreach ($iterator as $line) {
                if (preg_match(MainConfig::REGEXP, $line)) { 
                    $pieces = explode(MainConfig::DIVIDER, $line);

                    $dateStr['A'] += floatval($pieces[1]);
                    $dateStr['B'] += floatval($pieces[2]);
                    $dateStr['C'] += floatval($pieces[3]);
                }
            }

            return $dateStr;
    }
    
}