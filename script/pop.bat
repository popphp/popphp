@echo off
REM
REM Pop PHP Framework Windows CLI script (http://www.popphp.org/)
REM https://github.com/popphp/popphp2
REM http://www.popphp.org/license    New BSD License
REM
REM Possible arguments
REM
REM pop check             Check the current configuration for required dependencies
REM pop help              Display this help
REM pop build file.php    Build an application based on the build file specified
REM pop show              Show project install instructions
REM pop version           Display version of Pop PHP Framework
REM

SET SCRIPT_DIR=%~dp0
php %SCRIPT_DIR%pop.php %*
