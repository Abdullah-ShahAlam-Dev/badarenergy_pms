Add-Type -AssemblyName System.IO.Compression.FileSystem

$docxPath = "c:\laragon\www\badarenergy_pms\Badar energy v1.1.docx"
$outputPath = "c:\laragon\www\badarenergy_pms\Badar energy v1.1.md"

$zip = [System.IO.Compression.ZipFile]::OpenRead($docxPath)
$entry = $zip.Entries | Where-Object { $_.FullName -eq 'word/document.xml' }
$stream = $entry.Open()
$reader = New-Object System.IO.StreamReader($stream)
$xmlText = $reader.ReadToEnd()
$reader.Close()
$stream.Close()
$zip.Dispose()

[xml]$xml = $xmlText
$nsManager = New-Object System.Xml.XmlNamespaceManager($xml.NameTable)
$nsManager.AddNamespace("w", "http://schemas.openxmlformats.org/wordprocessingml/2006/main")

$lines = @()
$bodyNodes = $xml.SelectNodes("//w:body/*", $nsManager)

foreach ($node in $bodyNodes) {
    if ($node.LocalName -eq "p") {
        $pStyle = $node.SelectSingleNode(".//w:pStyle/@w:val", $nsManager)
        $styleVal = if ($pStyle) { $pStyle.Value } else { "" }
        
        $textRuns = $node.SelectNodes(".//w:t", $nsManager)
        $pText = ""
        foreach ($t in $textRuns) {
            $pText += $t.InnerText
        }
        $pText = $pText.Trim()
        
        if ($pText.Length -gt 0) {
            if ($styleVal -match "Title") {
                $lines += "# $pText`n"
            } elseif ($styleVal -match "Heading1") {
                $lines += "# $pText`n"
            } elseif ($styleVal -match "Heading2") {
                $lines += "## $pText`n"
            } elseif ($styleVal -match "Heading3") {
                $lines += "### $pText`n"
            } elseif ($styleVal -match "Heading4") {
                $lines += "#### $pText`n"
            } elseif ($node.SelectSingleNode(".//w:numPr", $nsManager)) {
                $lines += "- $pText"
            } elseif ($pText -match "^[☐☑❌\-\*]\s*") {
                $lines += "- " + ($pText -replace "^[☐☑\-\*]\s*", "")
            } else {
                $lines += $pText
            }
        }
    } elseif ($node.LocalName -eq "tbl") {
        $rows = $node.SelectNodes("./w:tr", $nsManager)
        $isFirstRow = $true
        foreach ($row in $rows) {
            $cells = $row.SelectNodes("./w:tc", $nsManager)
            $cellTexts = @()
            foreach ($cell in $cells) {
                $cText = ""
                $cellParagraphs = $cell.SelectNodes(".//w:t", $nsManager)
                foreach ($t in $cellParagraphs) {
                    $cText += $t.InnerText
                }
                $cellTexts += $cText.Trim().Replace("|", "\|")
            }
            $rowStr = "| " + ($cellTexts -join " | ") + " |"
            $lines += $rowStr
            
            if ($isFirstRow) {
                $sepStr = "| " + (($cellTexts | ForEach-Object { "---" }) -join " | ") + " |"
                $lines += $sepStr
                $isFirstRow = $false
            }
        }
        $lines += ""
    }
}

# Post-processing for clean line spacing and sections
$finalText = $lines -join "`n"

# Remove double linebreaks excess
$finalText = $finalText -replace "`n{3,}", "`n`n"

[System.IO.File]::WriteAllText($outputPath, $finalText, [System.Text.Encoding]::UTF8)
Write-Host "Converted cleanly to $outputPath"
