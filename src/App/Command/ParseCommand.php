<?php
namespace Console\App\Command;
 
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;

use App\Config\MainConfig as MainConfig;
use Console\App\Helper\FilesIterator;
use Console\App\Helper\CsvFilesAggregator;
use Console\App\Helper\Processor;

 
class ParseCommand extends Command
{
    protected $finder;
    protected $aggregator;
    protected $processor;

    public function __construct(CsvFilesAggregator $aggregator, Processor $processor)
    {
        $this->aggregator = $aggregator;
        $this->processor = $processor;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('parse')
            ->setDescription('Recursively parses csv files in the folder.')
            ->setHelp('Pass the --path parameter that set the folder where cvs files will have been searched.')
            ->addArgument(
                    'path', 
                    InputArgument::REQUIRED, 
                    'Please set the path where the files stored'
                );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $output->writeln('Ready to parse files in the: ' . $path);
        /** делаем в два этапа. вначале исходные файлы разбираем построчно и из строк
          * формируем предварительные файлы, содержащие данные на определенную дату **/

        try {
            $this->aggregator->setOutDir(MainConfig::OUTDIR)
                              ->clearOutputDir()
                              ->setPath($path)
                              ->setFileNameMask(MainConfig::CSVFILEMASK)
                              ->setCsvFileCaption(MainConfig::CSVFILECAPTION)
                              ->aggregateDatas();


            /** вторым этапом сгенерированные ранее файлы разбираем построчно и аггрегируем 
              * содержащиеся в них данные в выходной файл **/
            $this->processor->clearResultFile()
                            ->setPath(MainConfig::OUTDIR)
                            ->setCsvFileCaption(MainConfig::CSVFILECAPTION)
                            ->processResult();
            $this->aggregator->clearOutputDir();
        }
        catch (Exception $ex) {
                $output->writeln("There was a problem to read source files or 
                write aggregated data. Please check directories or permissions: {$ex->getMessage()}");
        }
        $output->writeln('Complete.');
    }
}
