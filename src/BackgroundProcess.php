<?php

/**
 * This file is part of cocur/background-process.
 *
 * (c) 2013-2015 Florian Eckerstorfer
 */

namespace M1n05\BackgroundProcess;

/**
 * BackgroundProcess.
 *
 * Runs a process in the background.
 *
 * @author    Florian Eckerstorfer <florian@eckerstorfer.co>
 * @copyright 2013-2015 Florian Eckerstorfer
 * @license   http://opensource.org/licenses/MIT The MIT License
 *
 * @link      http://braincrafted.com/php-background-processes/ Running background processes in PHP
 */
class BackgroundProcess
{
    const OS_WINDOWS = 1;
    const OS_LINUX = 2;
    const OS_MAC = 3;
    const OS_OTHER = 4;

    /** @var $WshShell \Com **/
    static protected $WshShell;
    
    /**
     * Runs the command in a background process.
     *
	 * @param string $command Command to execute
     * @param string $outputFile File to write the output of the process to; defaults to /dev/null
     */
    public static function run($command, $outputFile = null)
    {
        	
        $pid = null;
		if(!empty($command)){
			switch (BackgroundProcess::serverOS()) {
	            case BackgroundProcess::OS_WINDOWS :
	                if(is_null($outputFile)){
						$cmd = 'cmd /C %s > NIL';
					} else {
						$cmd = 'cmd /C %s > %s';
					}
	                $WshShell = BackgroundProcess::getShell();
					$oExec = $WshShell->exec(sprintf($cmd, $command, $outputFile));
	                if($oExec){
	                   $pid = $oExec->ProcessID;
	                }
	                break;
	            case BackgroundProcess::OS_LINUX :
	            case BackgroundProcess::OS_MAC :
					if(is_null($outputFile)){
						$outputFile = '/dev/null';
					}
	                $cmd = '%s > %s 2>&1 & echo $!';
	                $pid = shell_exec(sprintf($cmd, $command, $outputFile));
	                break;
	            default:
	                throw new \RuntimeException(sprintf(
	                    'Could not execute command "%s" because operating system "%s" is not supported by Cocur\BackgroundProcess.',
	                    $command,
	                    PHP_OS
	                ));
	        }	
		}
        
		return $pid;
    }

    /**
     * Returns if the process is currently running.
     *
     * @return bool TRUE if the process is running, FALSE if not.
     */
    public static function isRunning($pid)
    {
        
        switch(self::serverOS()){
            case BackgroundProcess::OS_WINDOWS :
                $WshShell = self::getShell();
                $cmd = "TASKLIST /FI \"PID eq %d\"";
                $oExec = $WshShell->exec(sprintf($cmd, $pid));
                $output = $oExec->StdOut->ReadAll();
                if(strpos($output, sprintf("%d",$pid) ) !== false ) {
                    return true;
                }
                break;
            case BackgroundProcess::OS_LINUX :

            case BackgroundProcess::OS_MAC :
                try {
                    $result = shell_exec(sprintf('ps %d', $pid));
                    if (count(preg_split("/\n/", $result)) > 2) {
                        return true;
                    }        
                } catch (\Exception $e) {}
                break;
            default:
                return false;
        }
    
        return false;
    }

    /**
     * Stops the process.
     *
     * @return bool `true` if the processes was stopped, `false` otherwise.
     */
    public static function stop($pid)
    {
        switch(self::serverOS()){
            case BackgroundProcess::OS_WINDOWS :
                $WshShell = self::getShell();
                $cmd = "TASKKILL /PID %d /F";
                $oExec = $WshShell->exec(sprintf($cmd, $pid));
                $output = $oExec->StdOut->readAll();
                return strlen($output) > 0;
                break;
            case BackgroundProcess::OS_LINUX :
            case BackgroundProcess::OS_MAC :
                try {
                    $result = shell_exec(sprintf('kill %d 2>&1', $pid));
                    if (!preg_match('/No such process/', $result)) {
                        return true;
                    }      
                } catch (\Exception $e) {}
                break;
            default:
                return false;
        }

        return false;
    }

    /**
     * @return int 1 Windows, 2 Linux, 3 Mac OS X, 4 unknown
     */
    protected static function serverOS()
    {
        $os = strtoupper(PHP_OS);

        if (substr($os, 0, 3) === 'WIN') {
            $os = BackgroundProcess::OS_WINDOWS;
        } else if ($os == 'LINUX') {
            $os = BackgroundProcess::OS_LINUX;
        } else if ($os == 'DARWIN') { // Mac OS X
            $os = BackgroundProcess::OS_MAC;
        } else {
            $os = BackgroundProcess::OS_OTHER;
        }

        return $os;
    }

    protected static function getShell(){
        if(is_null(self::$WshShell)){
            self::$WshShell = new \COM("WScript.Shell");
        }
        return self::$WshShell;
    }
}
