<?php

use PHPUnit\Framework\TestCase;
use StackWirtz\WordpressPlugin\Models\WirtzData;

class WirtzDataTest extends TestCase
{
    private $testCsvPath;
    
    protected function setUp(): void
    {
        $this->testCsvPath = __DIR__ . '/fixtures/test-data.csv';
        
        // Mock get_option to return test CSV folder
        global $mockOptions;
        $mockOptions = ['wirtz_csv_folder' => dirname($this->testCsvPath)];
    }
    
    public function testLatestFileReturnsCorrectFile()
    {
        // Create a mock WirtzData that uses our test file
        $wirtzData = $this->getMockBuilder(WirtzData::class)
            ->onlyMethods(['latestFile'])
            ->getMock();
            
        $wirtzData->method('latestFile')
            ->willReturn($this->testCsvPath);
            
        $this->assertEquals($this->testCsvPath, $wirtzData->latestFile());
    }
    
    public function testGetUniqueYears()
    {
        $wirtzData = new TestableWirtzData($this->testCsvPath);
        $years = $wirtzData->getUniqueYears();
        
        $this->assertContains('2022', $years);
        $this->assertContains('2023', $years);
        $this->assertCount(2, $years);
    }
    
    public function testGetUniqueProductions()
    {
        $wirtzData = new TestableWirtzData($this->testCsvPath);
        $productions = $wirtzData->getUniqueProductions();
        
        $this->assertContains('Hamlet', $productions);
        $this->assertContains('Romeo and Juliet', $productions);
        $this->assertContains('Macbeth', $productions);
        $this->assertContains('Othello', $productions);
    }
    
    public function testDoSearch()
    {
        $wirtzData = new TestableWirtzData($this->testCsvPath);
        
        // Search by first name
        $results = $wirtzData->doSearch('John', '', '', '', '', '', '');
        $this->assertCount(1, $results);
        $this->assertEquals('John', $results[0]['First']);
        
        // Search by production
        $results = $wirtzData->doSearch('', '', 'Hamlet', '', '', '', '');
        $this->assertCount(1, $results);
        $this->assertEquals('Hamlet', $results[0]['Production']);
        
        // Search by career
        $results = $wirtzData->doSearch('', '', '', '', '', 'UG', '');
        $this->assertCount(2, $results);
    }
    
    public function testGetProductionCountsByYear()
    {
        $wirtzData = new TestableWirtzData($this->testCsvPath);
        $counts = $wirtzData->getProductionCountsByYear();
        
        $this->assertArrayHasKey('2022', $counts);
        $this->assertArrayHasKey('2023', $counts);
        $this->assertArrayHasKey('Hamlet', $counts['2023']);
        $this->assertEquals(1, $counts['2023']['Hamlet']);
    }
}

// Testable version that doesn't rely on WordPress options
class TestableWirtzData extends WirtzData
{
    private $testFile;
    
    public function __construct($testFile)
    {
        $this->testFile = $testFile;
        
        if ($fileHandle = fopen($this->testFile, 'r')) {
            $data = array();
            while (($row = fgetcsv($fileHandle, 0, ',', '"', '\\')) !== false) {
                $data[] = $row;
            }
            fclose($fileHandle);
            
            $this->last_modified = date("Y-m-d H:i:s", filemtime($this->testFile));
            $this->data = $this->convertToArrayWithColumnHeaders($data);
        }
    }
    
    public function latestFile()
    {
        return $this->testFile;
    }
}
