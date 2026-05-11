<?php

use PHPUnit\Framework\TestCase;
use StackWirtz\WordpressPlugin\Handlers\SettingsFormHandler;

class SettingsFormHandlerTest extends TestCase
{
    private $testDir;
    
    protected function setUp(): void
    {
        $this->testDir = __DIR__ . '/fixtures/csv_test_folder';
        if (!is_dir($this->testDir)) {
            mkdir($this->testDir, 0777, true);
        }
        
        // Create test CSV files
        file_put_contents($this->testDir . '/test1.csv', "name,value\ntest,1");
        file_put_contents($this->testDir . '/test2.csv', "name,value\ntest,2");
        
        // Mock get_option
        global $mockOptions;
        $mockOptions = ['wirtz_csv_folder' => $this->testDir];
    }
    
    protected function tearDown(): void
    {
        // Clean up test files
        if (is_dir($this->testDir)) {
            array_map('unlink', glob($this->testDir . '/*'));
            rmdir($this->testDir);
        }
    }
    
    public function testGetCsvFilesDataReturnsFiles()
    {
        $result = SettingsFormHandler::getCsvFilesData();
        
        $this->assertArrayHasKey('files', $result);
        $this->assertCount(2, $result['files']);
        
        $filenames = array_column($result['files'], 'filename');
        $this->assertContains('test1.csv', $filenames);
        $this->assertContains('test2.csv', $filenames);
    }
    
    public function testGetCsvFilesDataWithNoFolder()
    {
        global $mockOptions;
        $mockOptions = ['wirtz_csv_folder' => ''];
        
        $result = SettingsFormHandler::getCsvFilesData();
        
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('No CSV folder configured yet.', $result['error']);
    }
    
    public function testGetCsvFilesDataWithInvalidFolder()
    {
        global $mockOptions;
        $mockOptions = ['wirtz_csv_folder' => '/nonexistent/folder'];
        
        $result = SettingsFormHandler::getCsvFilesData();
        
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('CSV folder path does not exist or is not accessible.', $result['error']);
    }
    
    public function testGetCsvFilesDataWithNoFiles()
    {
        // Remove CSV files
        unlink($this->testDir . '/test1.csv');
        unlink($this->testDir . '/test2.csv');
        
        $result = SettingsFormHandler::getCsvFilesData();
        
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('No CSV files found in the folder.', $result['error']);
    }
}