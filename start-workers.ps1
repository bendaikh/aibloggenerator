# PowerShell script to start multiple queue workers
# Run: .\start-workers.ps1 -Workers 4

param(
    [int]$Workers = 4
)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host " Starting $Workers Queue Workers" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "This will process articles in parallel." -ForegroundColor Yellow
Write-Host "With $Workers workers, 16 articles will take ~$([math]::Ceiling(16/$Workers) * 2) minutes instead of 30 minutes." -ForegroundColor Yellow
Write-Host ""

# Start workers in background jobs
$jobs = @()
for ($i = 1; $i -le $Workers; $i++) {
    Write-Host "Starting Worker $i..." -ForegroundColor Green
    $job = Start-Process -FilePath "php" -ArgumentList "artisan queue:work --tries=3 --timeout=600 --queue=default" -PassThru -WindowStyle Normal
    $jobs += $job
}

Write-Host ""
Write-Host "All $Workers workers started!" -ForegroundColor Green
Write-Host ""
Write-Host "Worker Process IDs:" -ForegroundColor Cyan
foreach ($job in $jobs) {
    Write-Host "  PID: $($job.Id)" -ForegroundColor White
}

Write-Host ""
Write-Host "To stop all workers, run:" -ForegroundColor Yellow
Write-Host "  Get-Process php | Stop-Process" -ForegroundColor White
Write-Host ""
Write-Host "Or close the worker windows manually." -ForegroundColor Yellow
