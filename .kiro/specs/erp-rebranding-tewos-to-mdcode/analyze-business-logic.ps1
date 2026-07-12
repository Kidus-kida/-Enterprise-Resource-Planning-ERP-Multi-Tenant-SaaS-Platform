# Business Logic Analysis Script
# This script creates a baseline snapshot of all business logic files

$ErrorActionPreference = "Continue"
$baseDir = Get-Location

$businessLogicBaseline = @{
    analysisDate = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    files = @()
    summary = @{
        totalFiles = 0
        controllers = 0
        models = 0
        services = 0
        jobs = 0
        utils = 0
    }
}

function Get-PHPSignatures {
    param([string]$filePath)
    
    $content = Get-Content $filePath -Raw -ErrorAction SilentlyContinue
    if (-not $content) {
        return @{
            classes = @()
            methods = @()
            functions = @()
        }
    }
    
    $signatures = @{
        classes = @()
        methods = @()
        functions = @()
    }
    
    # Extract class names
    $classMatches = [regex]::Matches($content, 'class\s+(\w+)(?:\s+extends\s+(\w+))?(?:\s+implements\s+([\w\s,]+))?')
    foreach ($match in $classMatches) {
        $className = $match.Groups[1].Value
        $extends = if ($match.Groups[2].Success) { $match.Groups[2].Value } else { "" }
        $implements = if ($match.Groups[3].Success) { $match.Groups[3].Value } else { "" }
        
        $signatures.classes += @{
            name = $className
            extends = $extends
            implements = $implements
        }
    }
    
    # Extract method signatures (public, protected, private)
    $methodMatches = [regex]::Matches($content, '(?:public|protected|private)\s+(?:static\s+)?function\s+(\w+)\s*\([^)]*\)')
    foreach ($match in $methodMatches) {
        $signatures.methods += $match.Groups[0].Value
    }
    
    # Extract standalone function signatures
    $functionMatches = [regex]::Matches($content, '^function\s+(\w+)\s*\([^)]*\)', [System.Text.RegularExpressions.RegexOptions]::Multiline)
    foreach ($match in $functionMatches) {
        $signatures.functions += $match.Groups[0].Value
    }
    
    return $signatures
}

function Get-FileChecksum {
    param([string]$filePath)
    
    $hash = Get-FileHash -Path $filePath -Algorithm SHA256 -ErrorAction SilentlyContinue
    if ($hash) {
        return $hash.Hash
    }
    return ""
}

Write-Host "Analyzing business logic files..." -ForegroundColor Cyan

# Process app/ directory
$appDirs = @(
    "app/Http/Controllers",
    "app/Models",
    "app/Services",
    "app/Jobs",
    "app/Utils"
)

foreach ($dir in $appDirs) {
    if (Test-Path $dir) {
        Write-Host "Processing $dir..." -ForegroundColor Yellow
        
        Get-ChildItem -Path $dir -Recurse -Filter "*.php" | ForEach-Object {
            $relativePath = $_.FullName.Replace($baseDir.Path + "\", "").Replace("\", "/")
            $signatures = Get-PHPSignatures -filePath $_.FullName
            $checksum = Get-FileChecksum -filePath $_.FullName
            
            $fileInfo = @{
                path = $relativePath
                type = if ($relativePath -like "*Controller*") { "controller" }
                       elseif ($relativePath -like "*Models*") { "model" }
                       elseif ($relativePath -like "*Services*") { "service" }
                       elseif ($relativePath -like "*Jobs*") { "job" }
                       elseif ($relativePath -like "*Utils*") { "util" }
                       else { "other" }
                size = $_.Length
                lastModified = $_.LastWriteTime.ToString("yyyy-MM-dd HH:mm:ss")
                checksum = $checksum
                signatures = $signatures
            }
            
            $businessLogicBaseline.files += $fileInfo
            $businessLogicBaseline.summary.totalFiles++
            
            switch ($fileInfo.type) {
                "controller" { $businessLogicBaseline.summary.controllers++ }
                "model" { $businessLogicBaseline.summary.models++ }
                "service" { $businessLogicBaseline.summary.services++ }
                "job" { $businessLogicBaseline.summary.jobs++ }
                "util" { $businessLogicBaseline.summary.utils++ }
            }
        }
    }
}

# Process Modules/ directory
$modules = Get-ChildItem -Path "Modules" -Directory -ErrorAction SilentlyContinue
foreach ($module in $modules) {
    Write-Host "Processing Module: $($module.Name)..." -ForegroundColor Yellow
    
    $moduleDirs = @(
        "$($module.FullName)/app/Http/Controllers",
        "$($module.FullName)/app/Models",
        "$($module.FullName)/app/Services",
        "$($module.FullName)/app/Jobs"
    )
    
    foreach ($dir in $moduleDirs) {
        if (Test-Path $dir) {
            Get-ChildItem -Path $dir -Recurse -Filter "*.php" | ForEach-Object {
                $relativePath = $_.FullName.Replace($baseDir.Path + "\", "").Replace("\", "/")
                $signatures = Get-PHPSignatures -filePath $_.FullName
                $checksum = Get-FileChecksum -filePath $_.FullName
                
                $fileInfo = @{
                    path = $relativePath
                    type = if ($relativePath -like "*Controller*") { "controller" }
                           elseif ($relativePath -like "*Models*") { "model" }
                           elseif ($relativePath -like "*Services*") { "service" }
                           elseif ($relativePath -like "*Jobs*") { "job" }
                           else { "other" }
                    module = $module.Name
                    size = $_.Length
                    lastModified = $_.LastWriteTime.ToString("yyyy-MM-dd HH:mm:ss")
                    checksum = $checksum
                    signatures = $signatures
                }
                
                $businessLogicBaseline.files += $fileInfo
                $businessLogicBaseline.summary.totalFiles++
                
                switch ($fileInfo.type) {
                    "controller" { $businessLogicBaseline.summary.controllers++ }
                    "model" { $businessLogicBaseline.summary.models++ }
                    "service" { $businessLogicBaseline.summary.services++ }
                    "job" { $businessLogicBaseline.summary.jobs++ }
                }
            }
        }
    }
}

# Convert to JSON and save
$jsonOutput = $businessLogicBaseline | ConvertTo-Json -Depth 10
$outputPath = ".kiro/specs/erp-rebranding-tewos-to-mdcode/business-logic-baseline.json"
$jsonOutput | Out-File -FilePath $outputPath -Encoding UTF8

Write-Host "`nAnalysis Complete!" -ForegroundColor Green
Write-Host "Total Files Analyzed: $($businessLogicBaseline.summary.totalFiles)" -ForegroundColor Cyan
Write-Host "  - Controllers: $($businessLogicBaseline.summary.controllers)" -ForegroundColor White
Write-Host "  - Models: $($businessLogicBaseline.summary.models)" -ForegroundColor White
Write-Host "  - Services: $($businessLogicBaseline.summary.services)" -ForegroundColor White
Write-Host "  - Jobs: $($businessLogicBaseline.summary.jobs)" -ForegroundColor White
Write-Host "  - Utils: $($businessLogicBaseline.summary.utils)" -ForegroundColor White
Write-Host "`nBaseline saved to: $outputPath" -ForegroundColor Green
