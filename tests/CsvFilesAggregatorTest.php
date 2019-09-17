<?php

class CsvFilesAggregatorTest extends \PHPUnit\Framework\TestCase
{
    protected $finder;
    protected $filesIterator;
    
    const TESTFILES = ['file1', 'correct.csv'];
    const TESTSTRINGS = ['2013-01-01; 3; 1; 0.5', '2014-02-02; 1; 6; 1.5'];
    
    protected function setUp() : void 
    {
        $this->finder = new Symfony\Component\Finder\Finder;
        $this->filesIterator = $this->testMockFilesIterator();
    }
 
    protected function tearDown() : void 
    {
    }

    public function testMockFilesIterator()
    {
        $stub = $this
            ->getMockBuilder('Console\App\Helper\FilesIterator')
            ->disableOriginalConstructor()
            ->setMethods(['iterate'])
            ->getMock();        
        $this->assertInstanceOf('Console\App\Helper\FilesIterator', $stub);
        $stub->expects($this->any())
               ->method('iterate')
               ->will($this->returnCallback(
                   function () {
                       return self::TESTSTRINGS;
                   }
               ));
        return $stub;
    }


    public function testSetGetPath()
    {
        $csvFilesAggregator = new Console\App\Helper\CsvFilesAggregator($this->finder, $this->filesIterator);
        $this->assertInstanceOf('Console\App\Helper\CsvFilesAggregator', $csvFilesAggregator);

        $this->assertInstanceOf('Console\App\Helper\CsvFilesAggregator', $csvFilesAggregator->setPath(__DIR__));
        $this->assertEquals(__DIR__, $csvFilesAggregator->getPath());

        $this->expectException(Exception::class);
        $csvFilesAggregator->setPath('ololo');
    }

    public function testSetGetFileNameMask()
    {
        $csvFilesAggregator = new Console\App\Helper\CsvFilesAggregator($this->finder, $this->filesIterator);
        $this->assertInstanceOf('Console\App\Helper\CsvFilesAggregator', $csvFilesAggregator);

        $this->assertInstanceOf('Console\App\Helper\CsvFilesAggregator', $csvFilesAggregator->setFileNameMask(App\Config\MainConfig::CSVFILEMASK));
        $this->assertEquals(App\Config\MainConfig::CSVFILEMASK, $csvFilesAggregator->getFileNameMask());
    }

    public function testSetGetCsvFileCaption()
    {
        $csvFilesAggregator = new Console\App\Helper\CsvFilesAggregator($this->finder, $this->filesIterator);
        $this->assertInstanceOf('Console\App\Helper\CsvFilesAggregator', $csvFilesAggregator);

        $this->assertInstanceOf('Console\App\Helper\CsvFilesAggregator', $csvFilesAggregator->setCsvFileCaption(App\Config\MainConfig::CSVFILECAPTION));
        $this->assertEquals(App\Config\MainConfig::CSVFILECAPTION, $csvFilesAggregator->getCsvFileCaption());
    }
    
    public function testSetGetOutDir()
    {
        $csvFilesAggregator = new Console\App\Helper\CsvFilesAggregator($this->finder, $this->filesIterator);
        $this->assertInstanceOf('Console\App\Helper\CsvFilesAggregator', $csvFilesAggregator);

        $this->assertInstanceOf('Console\App\Helper\CsvFilesAggregator', $csvFilesAggregator->setOutDir(__DIR__));
        $this->assertEquals(__DIR__, $csvFilesAggregator->getOutDir());

        $this->expectException(Exception::class);
        $csvFilesAggregator->setPath('ololo');
    }

    public function testAggregateData()
    {
        $csvFilesAggregator = new Console\App\Helper\CsvFilesAggregator($this->finder, $this->filesIterator);
        $this->assertInstanceOf('Console\App\Helper\CsvFilesAggregator', $csvFilesAggregator);

        $this->assertInstanceOf('Console\App\Helper\CsvFilesAggregator', $csvFilesAggregator->setPath(__DIR__));

        //$this->assertInstanceOf('Console\App\Helper\CsvFilesAggregator', $csvFilesAggregator->aggregateDatas());

        $this->expectException(Exception::class);
        $csvFilesAggregator->setPath('ololo');
    }
}
