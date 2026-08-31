#requires -Version 5.1

[CmdletBinding()]
param(
    [switch]$DryRun,
    [switch]$ConfirmProduction,
    [switch]$SkipLint,
    [switch]$KeepArtifacts,
    [string]$SshHost = 'metasiberia-server',
    [string]$PublicUrl = 'https://sreda.metasiberia.com/'
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$repoRoot = (Resolve-Path -LiteralPath (Join-Path $PSScriptRoot '..')).Path
$remoteInbox = '/home/denshipilov/sreda-deploy-inbox'

function Invoke-Checked {
    param(
        [Parameter(Mandatory = $true)][string]$FilePath,
        [Parameter(Mandatory = $false)][string[]]$ArgumentList = @(),
        [Parameter(Mandatory = $true)][string]$Description
    )

    $output = & $FilePath @ArgumentList 2>&1
    $exitCode = $LASTEXITCODE
    if ($exitCode -ne 0) {
        $details = ($output | Out-String).Trim()
        if ([string]::IsNullOrWhiteSpace($details)) {
            $details = 'команда не вернула подробностей'
        }
        throw "$Description завершилась с кодом $exitCode.`n$details"
    }
    return $output
}

function Get-RequiredCommand {
    param([Parameter(Mandatory = $true)][string]$Name)

    $command = Get-Command $Name -ErrorAction SilentlyContinue
    if ($null -eq $command) {
        throw "Не найдена команда '$Name'."
    }
    return $command.Source
}

if (-not [string]::Equals($PublicUrl.TrimEnd('/'), 'https://sreda.metasiberia.com', [StringComparison]::OrdinalIgnoreCase)) {
    throw "Этот workflow ограничен production URL https://sreda.metasiberia.com/. Получено: $PublicUrl"
}

$git = Get-RequiredCommand 'git'
$gitRoot = (Invoke-Checked -FilePath $git -ArgumentList @('-C', $repoRoot, 'rev-parse', '--show-toplevel') -Description 'Определение Git-корня').Trim()
if (-not [string]::Equals((Resolve-Path -LiteralPath $gitRoot).Path, $repoRoot, [StringComparison]::OrdinalIgnoreCase)) {
    throw "Скрипт запущен не из ожидаемого Git-репозитория: $repoRoot"
}

$status = Invoke-Checked -FilePath $git -ArgumentList @('-C', $repoRoot, 'status', '--porcelain=v1') -Description 'Проверка чистоты Git-дерева'
if ($status) {
    throw "Рабочее дерево не чистое. Сначала проверь и закоммить изменения отдельно:`n$($status | Out-String)"
}

Invoke-Checked -FilePath $git -ArgumentList @('-C', $repoRoot, 'diff', '--check') -Description 'Проверка whitespace в рабочем дереве' | Out-Null
Invoke-Checked -FilePath $git -ArgumentList @('-C', $repoRoot, 'diff', '--cached', '--check') -Description 'Проверка whitespace в staged-изменениях' | Out-Null
$parentOutput = & $git '-C' $repoRoot 'rev-parse' '--verify' 'HEAD^' 2>$null
$parentExitCode = $LASTEXITCODE
if ($parentExitCode -eq 0 -and $parentOutput) {
    Invoke-Checked -FilePath $git -ArgumentList @('-C', $repoRoot, 'diff', '--check', 'HEAD^', 'HEAD') -Description 'Проверка whitespace в deploy commit' | Out-Null
}

$commit = (Invoke-Checked -FilePath $git -ArgumentList @('-C', $repoRoot, 'rev-parse', 'HEAD') -Description 'Определение deploy commit').Trim()
if ($commit -notmatch '^[0-9a-f]{40}$') {
    throw "Git вернул некорректный commit: $commit"
}
$shortCommit = (Invoke-Checked -FilePath $git -ArgumentList @('-C', $repoRoot, 'rev-parse', '--short=12', 'HEAD') -Description 'Определение короткого commit').Trim()

$phpCommand = Get-Command php -ErrorAction SilentlyContinue
$changedFiles = @(Invoke-Checked -FilePath $git -ArgumentList @('-C', $repoRoot, 'diff-tree', '--root', '--no-commit-id', '--name-only', '-r', 'HEAD') -Description 'Определение файлов deploy commit' | ForEach-Object { $_.ToString().Trim() } | Where-Object { $_ })
$changedPhpFiles = @($changedFiles | Where-Object { $_ -match '(?i)\.php$' })
if (-not $SkipLint -and $changedPhpFiles.Count -gt 0) {
    if ($null -eq $phpCommand) {
        throw "Для проверки PHP-файлов не найден php в PATH. Установи/добавь PHP или повтори с -SkipLint после осознанной проверки."
    }
    foreach ($relativePath in $changedPhpFiles) {
        $fullPath = Join-Path $repoRoot ($relativePath -replace '/', '\')
        Invoke-Checked -FilePath $phpCommand.Source -ArgumentList @('-l', $fullPath) -Description "PHP lint: $relativePath" | Out-Null
    }
}

$tempRoot = Join-Path ([System.IO.Path]::GetTempPath()) ("sreda-deploy-" + [guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory -Path $tempRoot -Force | Out-Null
$archivePath = Join-Path $tempRoot ("sreda-$shortCommit.tar")
$manifestPath = Join-Path $tempRoot ("sreda-$shortCommit.sha256")
$remoteHelper = Join-Path $repoRoot 'ops\sreda-deploy-remote.sh'

try {
    if (-not (Test-Path -LiteralPath $remoteHelper -PathType Leaf)) {
        throw "Не найден remote helper: $remoteHelper"
    }

    $trackedFiles = @(Invoke-Checked -FilePath $git -ArgumentList @('-C', $repoRoot, 'ls-files') -Description 'Получение списка tracked-файлов' | ForEach-Object { $_.ToString() } | Where-Object { $_ })
    if ($trackedFiles.Count -eq 0) {
        throw 'Git-репозиторий не содержит tracked-файлов.'
    }

    $manifestLines = New-Object System.Collections.Generic.List[string]
    foreach ($relativePath in $trackedFiles) {
        $normalizedPath = $relativePath -replace '\\', '/'
        $fullPath = Join-Path $repoRoot ($normalizedPath -replace '/', '\')
        if (-not (Test-Path -LiteralPath $fullPath -PathType Leaf)) {
            throw "Tracked-файл отсутствует на диске: $normalizedPath"
        }
        $hash = (Get-FileHash -LiteralPath $fullPath -Algorithm SHA256).Hash.ToLowerInvariant()
        [void]$manifestLines.Add("$hash  $normalizedPath")
    }
    $utf8NoBom = New-Object System.Text.UTF8Encoding($false)
    [System.IO.File]::WriteAllLines($manifestPath, [string[]]$manifestLines, $utf8NoBom)

    Invoke-Checked -FilePath $git -ArgumentList @('-C', $repoRoot, 'archive', '--format=tar', '--prefix=release/', "--output=$archivePath", 'HEAD') -Description 'Создание release-архива из HEAD' | Out-Null

    $archiveSize = (Get-Item -LiteralPath $archivePath).Length
    Write-Output "Commit: $commit"
    Write-Output "Tracked files: $($trackedFiles.Count)"
    Write-Output "Archive: $archivePath ($archiveSize bytes)"
    Write-Output "Manifest: $manifestPath"

    if ($DryRun) {
        Write-Output 'DRY-RUN: сервер не подключался, файлы не загружались, production не изменялся.'
        return
    }

    if (-not $ConfirmProduction) {
        throw 'Для реального deployment укажи -ConfirmProduction. Для проверки без сервера используй -DryRun.'
    }

    $ssh = Get-RequiredCommand 'ssh'
    $scp = Get-RequiredCommand 'scp'
    $transferId = "${shortCommit}-" + [guid]::NewGuid().ToString('N')
    $remoteArchive = "$remoteInbox/sreda-$transferId.tar"
    $remoteManifest = "$remoteInbox/sreda-$transferId.sha256"
    $remoteHelperPath = "$remoteInbox/sreda-$transferId.sh"

    Invoke-Checked -FilePath $ssh -ArgumentList @($SshHost, "mkdir -p '$remoteInbox'") -Description 'Подготовка server inbox' | Out-Null
    Invoke-Checked -FilePath $scp -ArgumentList @($archivePath, ("{0}:{1}" -f $SshHost, $remoteArchive)) -Description 'Загрузка release-архива' | Out-Null
    Invoke-Checked -FilePath $scp -ArgumentList @($manifestPath, ("{0}:{1}" -f $SshHost, $remoteManifest)) -Description 'Загрузка SHA256 manifest' | Out-Null
    Invoke-Checked -FilePath $scp -ArgumentList @($remoteHelper, ("{0}:{1}" -f $SshHost, $remoteHelperPath)) -Description 'Загрузка remote helper' | Out-Null

    $remoteCommand = "bash '$remoteHelperPath' --archive '$remoteArchive' --manifest '$remoteManifest' --commit '$commit' --public-url '$PublicUrl'"
    Invoke-Checked -FilePath $ssh -ArgumentList @('-tt', $SshHost, $remoteCommand) -Description 'Выполнение deployment на Ubuntu' | ForEach-Object { Write-Output $_ }
    Write-Output 'DEPLOYMENT=success'
}
finally {
    if (-not $KeepArtifacts -and (Test-Path -LiteralPath $tempRoot)) {
        Remove-Item -LiteralPath $tempRoot -Recurse -Force
    }
}
