# Business Logic Verification Script
# This script compares current business logic files against the baseline to detect changes

$ErrorActionPreference = "Continue"
$baseDir = Get-Location

Write-Host "Loading baseline snapshot..." -ForegroundColor Cyan
$baselinePath = ".kiro/specs/erp-rebranding-tewos-to-mdcode/business-logic-baseline.json"

if (-not (Test-Path $baselinePath)) {
    Write-Host "ERROR: Baseline file not found at $baselinePath" -ForegroundColor Red
    exit 1
}

$baseline = Get-Content $baselinePath -Raw | ConvertFrom-Json
Write-Host "Baseline loaded: $($baseline.summary.totalFiles) files" -ForegroundColor Green
Write-Host "Baseline date: $($baseline.analysisDate)" -ForegroundColor White
Write-Host ""

# Verification results
$results = @{
    timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    totalChecked = 0
    filesChanged = @()
    filesUnchanged = 0
    filesMissing = @()
    checksumMatches = 0
    checksumMismatches = 0
    signatureChanges = @()
    passed = $true
}

function Get-FileChecksum {
    param([string]$filePath)
    
    $hash = Get-FileHash -Path $filePath -Algorithm SHA256 -ErrorAction SilentlyContinue
    if ($hash) {
        return $hash.Hash
    }
    return ""
}

function Compare-Signatures {
    param(
        [object]$baseline,
        [object]$current
    )
    
    $changes = @()
    
    # Compare class count
    if ($baseline.classes.Count -ne $current.classes.Count) {
        $changes += "Class count changed: $($baseline.classes.Count) -> $($current.classes.Count)"
    }
    
    # Compare method count
    if ($baseline.methods.Count -ne $current.methods.Count) {
        $changes += "Method count changed: $($baseline.methods.Count) -> $($current.methods.Count)"
    }
    
    # Compare function count
    if ($baseline.functions.Count -ne $current.functions.Count) {
        $changes += "Function count changed: $($baseline.functions.Count) -> $($current.functions.Count)"
    }
    
    return $changes
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
    
    # Extract method signatures
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

Write-Host "Verifying business logic preservation..." -ForegroundColor Yellow
Write-Host ""

$progressCounter = 0
foreach ($baselineFile in $baseline.files) {
    $progressCounter++
    if ($progressCounter % 50 -eq 0) {
        Write-Host "  Checked $progressCounter / $($baseline.files.Count) files..." -ForegroundColor Gray
    }
    
    $results.totalChecked++
    $filePath = $baselineFile.path
    
    # Check if file still exists
    if (-not (Test-Path $filePath)) {
        $results.filesMissing += $filePath
        $results.passed = $false
        continue
    }
    
    # Get current checksum
    $currentChecksum = Get-FileChecksum -filePath $filePath
    
    # Compare checksums
    if ($currentChecksum -eq $baselineFile.checksum) {
        $results.checksumMatches++
        $results.filesUnchanged++
    } else {
        $results.checksumMismatches++
        
        # File changed - analyze signatures to see if business logic changed
        $currentSignatures = Get-PHPSignatures -filePath $filePath
        $signatureChanges = Compare-Signatures -baseline $baselineFile.signatures -current $currentSignatures
        
        if ($signatureChanges.Count -gt 0) {
            $results.signatureChanges += @{
                file = $filePath
                changes = $signatureChanges
            }
            $results.passed = $false
        }
        
        $results.filesChanged += @{
            file = $filePath
            baselineChecksum = $baselineFile.checksum
            currentChecksum = $currentChecksum
            signatureChanges = $signatureChanges
        }
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  BUSINESS LOGIC VERIFICATION REPORT" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Verification Time: $($results.timestamp)" -ForegroundColor White
Write-Host "Files Checked: $($results.totalChecked)" -ForegroundColor White
Write-Host ""
Write-Host "Results:" -ForegroundColor Yellow
Write-Host "  Files Unchanged: $($results.filesUnchanged)" -ForegroundColor Green
Write-Host "  Files Changed (checksum): $($results.checksumMismatches)" -ForegroundColor $(if ($results.checksumMismatches -gt 0) { "Yellow" } else { "Green" })
Write-Host "  Files with Signature Changes: $($results.signatureChanges.Count)" -ForegroundColor $(if ($results.signatureChanges.Count -gt 0) { "Red" } else { "Green" })
Write-Host "  Files Missing: $($results.filesMissing.Count)" -ForegroundColor $(if ($results.filesMissing.Count -gt 0) { "Red" } else { "Green" })
Write-Host ""

if ($results.passed) {
    Write-Host "✓ VERIFICATION PASSED" -ForegroundColor Green
    Write-Host "Business logic structure has been preserved." -ForegroundColor Green
} else {
    Write-Host "✗ VERIFICATION FAILED" -ForegroundColor Red
    Write-Host "Business logic has been modified!" -ForegroundColor Red
    Write-Host ""
    
    if ($results.filesMissing.Count -gt 0) {
        Write-Host "Missing Files:" -ForegroundColor Red
        foreach ($file in $results.filesMissing) {
            Write-Host "  - $file" -ForegroundColor Red
        }
        Write-Host ""
    }
    
    if ($results.signatureChanges.Count -gt 0) {
        Write-Host "Files with Signature Changes:" -ForegroundColor Red
        foreach ($change in $results.signatureChanges) {
            Write-Host "  File: $($change.file)" -ForegroundColor Yellow
            foreach ($detail in $change.changes) {
                Write-Host "    - $detail" -ForegroundColor Red
            }
        }
    }
}

Write-Host ""
Write-Host "Files changed (text-only changes expected during rebranding):" -ForegroundColor Cyan
if ($results.filesChanged.Count -eq 0) {
    Write-Host "  None" -ForegroundColor Green
} else {
    $changesWithoutSignatureModifications = $results.filesChanged | Where-Object { $_.signatureChanges.Count -eq 0 }
    Write-Host "  $($changesWithoutSignatureModifications.Count) files changed (acceptable text changes)" -ForegroundColor Yellow
    
    if ($changesWithoutSignatureModifications.Count -le 10) {
        foreach ($file in $changesWithoutSignatureModifications) {
            Write-Host "    - $($file.file)" -ForegroundColor Gray
        }
    } else {
        Write-Host "    (List truncated - see full report for details)" -ForegroundColor Gray
    }
}

# Save detailed report
$reportPath = ".kiro/specs/erp-rebranding-tewos-to-mdcode/business-logic-verification-report.json"
$results | ConvertTo-Json -Depth 10 | Out-File -FilePath $reportPath -Encoding UTF8

Write-Host ""
Write-Host "Detailed report saved to: $reportPath" -ForegroundColor Cyan

if (-not $results.passed) {
    exit 1
}

exit 0
