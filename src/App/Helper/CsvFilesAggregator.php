<?php
namespace Console\App\Helper;

use Exception;
use Symfony\Component\Finder\Finder as Finder;
use App\Config\MainConfig as MainConfig;

class CsvFilesAggregator
{
    /** этот класс делает важную задачу: итеративно проходит по всем файлам 
     * (или если указана маска $this->setMask и(или) заголовок файла $this->setCsvFileCaption по определенным файлам)
     * по указанному пути. а на выходе в указанную директорию складывает файлы, содержащие данные по найденной дате
     * я не проверяю тут, что первое поле содержит только корректную дату и считаю, что так .. если это требуется, можно
     * доделать. 
     */

    /**
     * @var string where the source files stored
     */
    protected $path;

    /**
     * @var string file mask to process those files that match the mask set
     */
    protected $fileNameMask;

    /**
     * @var object Symfony Finder
     */
    protected $finder;

    /**
     * @var string caption that have to be added int the top of output files
     */
    protected $csvFileCaption;

    /**
     * @var object Console\App\Helper\FilesIterator
     */
    protected $fileIterator;

    /**
     * @var string the path where output files should be placed
     */
    protected $outDir;

    /**
     * @param object $finder Symfony component
     * @param object $filesIterator Console\App\Helper\FilesIterator
     */
    public function __construct(Finder $finder, \Console\App\Helper\FilesIterator $fileIterator)
    {
        $this->finder = $finder;
        $this->fileIterator = $fileIterator;
    }

    /**
     * Sets the path to the source files.
     *
     * @param string $path
     *
     * @return self
     *
     * @throws Exception
     */
    public function setPath(string $path) :self
    {
        if (!is_dir($path)) {
            throw new Exception("Path not found or inacceptable");
        }
        $this->path = $path;

        return $this;
    }

    /**
     * @return string|path
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Sets the file mask to match the source files.
     *
     * @param string $fileNameMask
     *
     * @return self
     */
    public function setFileNameMask(string $fileNameMask) :self
    {
        $this->fileNameMask = $fileNameMask;
        $this->finder->files()->name($this->fileNameMask);

        return $this;
    }

    /**
     * @return string|fileNameMask
     */
    public function getFileNameMask(): ?string
    {
        return $this->fileNameMask;
    }

    /**
     * Sets the path to the output files
     *
     * @param string $outDir
     *
     * @return self
     *
     * @throws Exception
     */
    public function setOutDir(string $outDir) :self
    {
        if (!is_dir($outDir)) {
            throw new Exception("Path not found or inacceptable");
        }
        $this->outDir = $outDir;

        return $this;
    }

    /**
     * @return string|outDir
     */
    public function getOutDir(): ?string
    {
        return $this->outDir;
    }

    /**
     * Sets the files caption that have to contain the source files to be processed
     *
     * @param string $csvFileCaption
     *
     * @return self
     */
    public function setCsvFileCaption(string $csvFileCaption) :self
    {
        $this->csvFileCaption = $csvFileCaption;
        $this->finder->files()->contains($this->csvFileCaption);

        return $this;
    }

    /**
     * @return string|csvFileCaption
     */
    public function getCsvFileCaption(): ?string
    {
        return $this->csvFileCaption;
    }

    /** итеративно перебираем все входные файлы.
     * найденные интересующие нас строки раскладываем по разым файлам 
     * в соответствии с датами событий 
     *
     * @return self
     */
    public function aggregateDatas() 
    {
        if (!isset($this->path)) {
            throw new Exception ("Could not process result, path to the data have been not set");
        }
        $this->finder->ignoreVCS(true);
        $this->finder = Finder::create()->files()->in($this->path);

        foreach ($this->finder as $file) {
            try {
                // исходный файл с данными может быть большим, проходим итератором
                $this->fileIterator->setFile($file);
                $iterator = $this->fileIterator->iterate();
                foreach ($iterator as $line) {
                    // берем только строки, удовлетворяющие нашему паттерну YYYY-mm-dd; A; B; C
                    if (preg_match(MainConfig::REGEXP, $line)) { 
                        $pieces = explode(MainConfig::DIVIDER, $line);
                        if (!$fileHandle = fopen($this->outDir . $pieces[0], 'a')){
                            throw new Exception("Could not write to file");
                        }
                        // и найденные данные в зависимости от даты кладем в тот или иной файл
                        fwrite($fileHandle, $line);
                    }
                }

            }
            catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }
        }

        return $this;
    }

    /* рекурсивно очищаем директорию от всего содержимого
     *
     * @return self
     */
    public function clearOutputDir ()
    {
        $di = new \RecursiveDirectoryIterator($this->outDir, \FilesystemIterator::SKIP_DOTS);
        $ri = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);
        try {
            foreach ( $ri as $file ) {
                $file->isDir() ?  rmdir($file) : unlink($file);
            }

            return $this;
        }
        catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
