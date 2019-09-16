<?php
namespace Console\App\Helper;

use Symfony\Component\Finder\Finder as Finder;
use App\Config\MainConfig as MainConfig;

class CsvFilesAggregator
{
    /** этот класс делает важную задачу: итеративно проходит по всем файлам 
      * (или если указана маска $this->setMask и(или) заголовок файла $this->setCsvFileCaption по определенным файлам)
      * по указанному пути. а на выходе в указанную директорию складывает файлы, содержащие данные по найденной дате
      * я не проверяю тут, что первое поле содержит только корректную дату и считаю, что так .. если это требуется, можно
      * доделать. **/
    
    protected $path;
    protected $fileNameMask;
    protected $finder;
    protected $csvFileCaption;
    protected $fileIterator;

    public function __construct(string $path, Finder $finder, \Console\App\Helper\FilesIterator $fileIterator)
    {
        $this->path = $path;
        $this->finder = $finder;
        $this->fileIterator = $fileIterator;
        // выходную папку очищаем полностью от всего
        $this->clearOutputDir();
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

    /** итеративно перебираем все входные файлы.
      * найденные интересующие нас строки раскладываем по разым файлам 
      * в соответствии с датами событий **/
    public function aggregateDatas() 
    {
        $this->finder->ignoreVCS(true);
        $this->finder->files()->in($this->path);

        foreach ($this->finder as $file) {
            try {
                // исходный файл с данными может быть большим, проходим итератором
                $this->fileIterator->setFile($file);
                $iterator = $this->fileIterator->iterate();
                $counter = 0;
                foreach ($iterator as $line) {
                    // берем только строки, удовлетворяющие нашему паттерну YYYY-mm-dd; A; B; C
                    if (preg_match(MainConfig::REGEXP, $line)) { 
                        $pieces = explode(MainConfig::DIVIDER, $line);
                        // и найденные данные в зависимости от даты кладем в тот или иной файл
                        file_put_contents(MainConfig::OUTDIR . $pieces[0], $line , FILE_APPEND | LOCK_EX);
                    }
                }
            }
            catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }
        }
    }

    // рекурсивно очищаем директорию от всего содержимого
    public function clearOutputDir ()
    {
        $di = new \RecursiveDirectoryIterator(MainConfig::OUTDIR, \FilesystemIterator::SKIP_DOTS);
        $ri = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);
        try {
            foreach ( $ri as $file ) {
                $file->isDir() ?  rmdir($file) : unlink($file);
            }
        }
        catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
