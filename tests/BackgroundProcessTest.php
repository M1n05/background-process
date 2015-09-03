<?php
/**
 * This file is part of cocur/background-process.
 *
 * (c) 2013-2014 Florian Eckerstorfer
 */

namespace M1n05\BackgroundProcess;

/**
 * BackgroundProcessTest
 *
 * @category  test
 * @package   cocur/background-process
 * @author    Florian Eckerstorfer <florian@eckerstorfer.co>
 * @copyright 2013-2104 Florian Eckerstorfer
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @group     functional
 */
class BackgroundProcessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests running a background process.
     * 
     * @covers M1n05\BackgroundProcess\BackgroundProcess::run()
     * @covers M1n05\BackgroundProcess\BackgroundProcess::isRunning()
     * @covers M1n05\BackgroundProcess\BackgroundProcess::getPid()
     * @covers M1n05\BackgroundProcess\BackgroundProcess::stop()
     */
    public function testRun()
    {
		$pid = BackgroundProcess::run("php " . __DIR__ . DIRECTORY_SEPARATOR. "test2.php");
		$this->assertInternalType('int', $pid, 'process should return pid');
		$this->assertTrue(BackgroundProcess::isRunning($pid), 'process should run');
        $this->assertTrue(BackgroundProcess::stop($pid), 'stop process');
        $this->assertFalse(BackgroundProcess::isRunning($pid), 'processes should not run anymore');
        $this->assertFalse(BackgroundProcess::stop($pid), 'cannot stop process that is not running');
    }
	
	public function testRunEmptyCommand()
	{
		$pid = BackgroundProcess::run("");
		$this->assertNull($pid, 'process should not be started');
	}
	
	public function testRunWithFile()
	{
		$outputfile = tempnam(__DIR__,'tst');
				
		$pid = BackgroundProcess::run("php " . __DIR__ . DIRECTORY_SEPARATOR. "test2.php", $outputfile);
		echo $pid;
		$this->assertFileExists($outputfile);
		$this->assertTrue(BackgroundProcess::stop($pid), 'stop process');
		sleep(5);
		unlink($outputfile);
		
		
		
		 
	}
}
