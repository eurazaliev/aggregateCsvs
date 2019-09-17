<?php

class FileIteratorTest extends \PHPUnit\Framework\TestCase
{
    
    protected function setUp() : void 
    {
    }

    protected function tearDown() : void 
    {
    }

    public function testSetPath()
    {
        $filesIterator = new Console\App\Helper\FilesIterator();
        $this->assertInstanceOf('Console\App\Helper\FilesIterator', $filesIterator);

        $this->expectException(Exception::class);
        $filesIterator->setFile('ololo');

        $testFile = glob(__DIR__ . '/*.*')[array_rand(glob(__DIR__ . '/*.*'))];
        $this->assertInstanceOf('Console\App\Helper\FilesIterator', $filesIterator->setFile($testFile));
        
        $this->assertInstanceOf('\NoRewindIterator', $filesIterator->iterate());
    }

}
