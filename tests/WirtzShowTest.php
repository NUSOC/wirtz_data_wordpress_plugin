<?php

use PHPUnit\Framework\TestCase;
use StackWirtz\WordpressPlugin\WirtzShow;

class WirtzShowTest extends TestCase
{
    private $testCsvPath;
    
    protected function setUp(): void
    {
        $this->testCsvPath = __DIR__ . '/fixtures/test-data.csv';
        
        // Mock WordPress functions
        global $mockOptions, $mockUserLoggedIn;
        $mockOptions = ['wirtz_csv_folder' => dirname($this->testCsvPath)];
        $mockUserLoggedIn = true;
    }
    
    public function testRenderDashboardWithSkipChecks()
    {
        $wirtzShow = new TestableWirtzShow($this->testCsvPath);
        $result = $wirtzShow->renderDashboard(true);
        
        $this->assertIsString($result);
        $this->assertStringContainsString('dashboard', $result);
    }
    
    public function testRenderDashboardWithoutLogin()
    {
        global $mockUserLoggedIn;
        $mockUserLoggedIn = false;
        
        $wirtzShow = new TestableWirtzShow($this->testCsvPath);
        $result = $wirtzShow->renderDashboard(true);
        
        $this->assertStringContainsString('login', $result);
    }
    
    public function testRenderProductionByYearChart()
    {
        $wirtzShow = new TestableWirtzShow($this->testCsvPath);
        $result = $wirtzShow->renderProductionByYearChart(true);
        
        $this->assertIsString($result);
    }
}

// Testable version of WirtzShow
class TestableWirtzShow extends WirtzShow
{
    public function __construct($testFile)
    {
        // Override constructor to use test data
        $this->wirtz_data = new TestableWirtzData($testFile);
        
        // Mock Twig
        $this->twig = new MockTwig();
    }
}

// Mock Twig environment
class MockTwig
{
    public function render($template, $data = [])
    {
        return "Rendered template: $template with data: " . json_encode($data);
    }
}

// Mock WordPress functions
function is_user_logged_in() {
    global $mockUserLoggedIn;
    return $mockUserLoggedIn ?? false;
}

function wp_login_url() {
    return 'http://example.com/wp-login.php';
}