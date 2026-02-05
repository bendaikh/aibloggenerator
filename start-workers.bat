@echo off
REM Start multiple queue workers for parallel article generation
REM Run this script to process articles faster

echo Starting 4 queue workers for parallel processing...
echo Each worker will handle articles concurrently.
echo Press Ctrl+C in any window to stop that worker.
echo.

REM Start 4 workers in separate windows
start "Queue Worker 1" cmd /k "php artisan queue:work --tries=3 --timeout=600 --queue=default"
start "Queue Worker 2" cmd /k "php artisan queue:work --tries=3 --timeout=600 --queue=default"
start "Queue Worker 3" cmd /k "php artisan queue:work --tries=3 --timeout=600 --queue=default"
start "Queue Worker 4" cmd /k "php artisan queue:work --tries=3 --timeout=600 --queue=default"

echo.
echo 4 queue workers started in separate windows.
echo With 4 workers, 16 articles will take approximately 8-10 minutes instead of 30 minutes.
echo.
pause
