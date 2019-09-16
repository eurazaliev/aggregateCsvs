<?php
namespace Console\App\Command;
 
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
        
        $fileNameMask = MainConfig::CSVFILEMASK;
        $csvFileCaption = MainConfig::CSVFILECAPTION;
        
        $output->writeln('Ready to parse files in the: ' . $path);

        /** делаем в два этапа. вначале исходные файлы разбираем построчно и из строк
          * формируем предварительные файлы, содержащие данные на определенную дату **/
        try {
            $csvFilesAggregator  = new CsvFilesAggregator($path, new Finder, new FilesIterator);
        }
        catch (Exception $ex) {
                $output->writeln('There was a problem to read source files or 
                write aggregated data. Please check directories or permissions', $ex->getMessage());
        }
        $csvFilesAggregator->setFileNameMask($fileNameMask)
                           ->setCsvFileCaption($csvFileCaption)
                           ->aggregateDatas();


        /** вторым этапом сгенерированные ранее файлы разбираем построчно и аггрегируем 
          * содержащиеся в них данные в выходной файл **/
        $resultProcessor  = new Processor($path, new Finder(), new FilesIterator);
        $resultProcessor->setCsvFileCaption($csvFileCaption)
                        ->processResult();

        $csvFilesAggregator->clearOutputDir();
        $output->writeln('Complete.');
    }
}
