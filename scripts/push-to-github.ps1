# Push SmartFlow to GitHub — https://github.com/eng-lina-ghojan/smartflow
# Run from project root: .\scripts\push-to-github.ps1

$ErrorActionPreference = "Stop"
Set-Location (Split-Path $PSScriptRoot -Parent)

$git = "C:\Program Files\Git\cmd\git.exe"
if (-not (Test-Path $git)) { $git = "git" }

& $git remote set-url origin https://github.com/eng-lina-ghojan/smartflow.git

Write-Host ""
Write-Host "Pushing to: https://github.com/eng-lina-ghojan/smartflow (branch: master)" -ForegroundColor Cyan
Write-Host "Username: eng-lina-ghojan" -ForegroundColor Yellow
Write-Host "Password: GitHub Personal Access Token (https://github.com/settings/tokens)" -ForegroundColor Yellow
Write-Host ""

if ($env:GITHUB_TOKEN) {
    $b64 = [Convert]::ToBase64String([Text.Encoding]::ASCII.GetBytes("x-access-token:$env:GITHUB_TOKEN"))
    & $git -c "http.extraHeader=Authorization: Basic $b64" push origin cursor/migrate-to-laravel-vue:master
} else {
    & $git push origin cursor/migrate-to-laravel-vue:master
}

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "Success! Check: https://github.com/eng-lina-ghojan/smartflow" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "Failed. Create a token with 'repo' scope and retry." -ForegroundColor Red
    exit 1
}
