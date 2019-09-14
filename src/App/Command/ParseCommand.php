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
use Console\App\Helper\FilesProcessor;
 
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
                    'Please set the path where files stored'
                );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        
        $fileNameMask = MainConfig::CSVFILEMASK;
        $csvFileCaption = MainConfig::CSVFILECAPTION;
        
        $output->writeln('Ready to parse files in the: ' . $path);

        $finder = new Finder();
        $filesProcessor  = new FilesProcessor($path, $finder);
        $filesProcessor->setFileNameMask($fileNameMask);
        $filesProcessor->setCsvFileCaption($csvFileCaption);
        
        $result = $filesProcessor->process(); //->getResult();
        
        $output->writeln('Complete.');
    }
}
