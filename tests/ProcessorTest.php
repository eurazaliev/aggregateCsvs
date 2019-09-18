<?php

class ProcessorTest extends \PHPUnit\Framework\TestCase
{
    protected $finder;
    protected $filesIterator;

    const TESTSTRINGS = ['2013-01-01; 3; 1; 0.5', '2014-02-02; 1; 6; -1.5'];

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
        $processor = new Console\App\Helper\Processor($this->finder, $this->filesIterator);
        $this->assertInstanceOf('Console\App\Helper\Processor', $processor);

        $this->assertInstanceOf('Console\App\Helper\Processor', $processor->setPath(__DIR__));
        $this->assertEquals(__DIR__, $processor->getPath());

        $this->expectException(Exception::class);
        $processor->setPath('ololo');
    }

    public function testSetGetCsvFileCaption()
    {
        $processor = new Console\App\Helper\Processor($this->finder, $this->filesIterator);
        $this->assertInstanceOf('Console\App\Helper\Processor', $processor);

        $this->assertInstanceOf('Console\App\Helper\Processor', $processor->setCsvFileCaption(App\Config\MainConfig::CSVFILECAPTION));
        $this->assertEquals(App\Config\MainConfig::CSVFILECAPTION, $processor->getCsvFileCaption());
    }
    
    public function testAggregateFileData(){
        // тут проверяю protecred метод, поэтому через рефлекшн
        $class = new \ReflectionClass('Console\App\Helper\Processor');
        $aggregateFileData = $class->getMethod('AggregateFileData');
        $aggregateFileData->setAccessible(true);
        
        $processor = new Console\App\Helper\Processor($this->finder, $this->filesIterator);
        
        $iterator = $this->finder->files()->in(__DIR__)->getIterator();
        $iterator->rewind();
        $testFile = $iterator->current();

        $result = $aggregateFileData->invokeArgs($processor, [$testFile]);
        $this->assertEquals(4, $result["A"]);
        $this->assertEquals(7, $result["B"]);
        $this->assertEquals(-1, $result["C"]);
    }


    public function testProcessResult()
    {
        $processor = new Console\App\Helper\Processor($this->finder, $this->filesIterator);
        $this->assertInstanceOf('Console\App\Helper\Processor', $processor->clearResultFile());
        
        $this->expectException(Exception::class);
        $this->assertInstanceOf('Console\App\Helper\Processor', $processor->processResult());

        $this->assertInstanceOf('Console\App\Helper\Processor', $processor->setPath(__DIR__));
        $this->assertInstanceOf('Console\App\Helper\Processor', $processor->processResult());
        $this->expectException(Exception::class);
        $processor->setPath('ololo');
    }
}
