# Push SmartFlow to GitHub — run from project root: .\scripts\push-to-github.ps1
# Set: $env:GITHUB_TOKEN = "ghp_..."  (never commit tokens)

$ErrorActionPreference = "Stop"
Set-Location (Split-Path $PSScriptRoot -Parent)

$git = "C:\Program Files\Git\cmd\git.exe"
if (-not (Test-Path $git)) { $git = "git" }

$repo = if ($env:GITHUB_REPO) { $env:GITHUB_REPO } else { "https://github.com/LinaAhmadGhojan/smart-flow.git" }
$branch = if ($env:GITHUB_BRANCH) { $env:GITHUB_BRANCH } else { "main" }

& $git remote set-url origin $repo

Write-Host "Pushing to $repo (branch: $branch)" -ForegroundColor Cyan

if ($env:GITHUB_TOKEN) {
    $b64 = [Convert]::ToBase64String([Text.Encoding]::ASCII.GetBytes("x-access-token:$env:GITHUB_TOKEN"))
    & $git -c "http.extraHeader=Authorization: Basic $b64" push origin "${branch}:${branch}"
} else {
    & $git push origin "${branch}:${branch}"
}

if ($LASTEXITCODE -eq 0) {
    Write-Host "Success!" -ForegroundColor Green
} else {
    Write-Host "Failed. Use GITHUB_TOKEN env var or sign in to GitHub." -ForegroundColor Red
    exit 1
}
