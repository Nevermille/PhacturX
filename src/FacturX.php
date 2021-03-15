<?php

namespace Lianhua\PhacturX;

use Exception;

/*
PhacturX Library
Copyright (C) 2020  Lianhua Studio

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class FacturX
{
    private $pdfFile;
    private $xmlFile;
    private $iccProfile;
    private $psScript;

    public function setPdfFile(string $pdfFile): void
    {
        if (!file_exists($pdfFile)) {
            throw new Exception("File not found");
        }

        $this->pdfFile = realpath($pdfFile);
    }

    public function setXmlFile(string $xmlFile): void
    {
        if (!file_exists($xmlFile)) {
            throw new Exception("File not found");
        }

        $this->xmlFile = realpath($xmlFile);
    }

    public function setIccProfile(string $iccProfile): void
    {
        if (!file_exists($iccProfile)) {
            throw new Exception("File not found");
        }

        $this->iccProfile = realpath($iccProfile);
    }

    public function setPsScript(string $psScript): void
    {
        if (!file_exists($psScript)) {
            throw new Exception("File not found");
        }

        $this->psScript = realpath($psScript);
    }

    public function createFacturX(string $outputFile): void
    {
        // Save current working dir
        $cwd = getcwd();

        // Output file path
        $outDir = realpath(dirname($outputFile));
        $outPdf = $outDir . DIRECTORY_SEPARATOR . $outputFile;

        // Create the temp directory
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . bin2hex(openssl_random_pseudo_bytes(8));
        mkdir($dir);

        // Copy needed files
        copy($this->pdfFile, $dir . DIRECTORY_SEPARATOR . "in.pdf");
        copy($this->xmlFile, $dir . DIRECTORY_SEPARATOR . "factur-x.xml");
        copy($this->iccProfile, $dir . DIRECTORY_SEPARATOR . "in.icc");
        copy($this->psScript, $dir . DIRECTORY_SEPARATOR . "script.ps");

        // Prepare cmd
        $cmd = "gs -dNOSAFER -dPrinted -dShowAnnots=false -sDEVICE=pdfwrite -dPDFA=3 -sColorConversionStrategy=RGB";
        $cmd .= " -sZUGFeRDXMLFile=factur-x.xml -sZUGFeRDProfile=in.icc";
        $cmd .= " -o " . escapeshellarg($outPdf);
        $cmd .= " script.ps in.pdf";

        // Execute
        chdir($dir);
        exec($cmd);

        // Clean
        unlink($dir . DIRECTORY_SEPARATOR . "in.pdf");
        unlink($dir . DIRECTORY_SEPARATOR . "factur-x.xml");
        unlink($dir . DIRECTORY_SEPARATOR . "in.icc");
        unlink($dir . DIRECTORY_SEPARATOR . "script.ps");

        // Change working directory back
        chdir($cwd);
        rmdir($dir);
    }

    /**
     * Constructor
     * @param string|null $pdfFile The path of the invoice PDF file
     * @param string|null $xmlFile The path of the Factur-X XML file
     * @return void
     */
    public function __construct(string $pdfFile = null, string $xmlFile = null)
    {
        if (!empty($pdfFile)) {
            $this->setPdfFile($pdfFile);
        }

        if (!empty($xmlFile)) {
            $this->setXmlFile($xmlFile);
        }

        $defaultIcc = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
        $defaultIcc .= "etc" . DIRECTORY_SEPARATOR . "AdobeRGB1998.icc";

        $defaultPs = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
        $defaultPs .= "etc" . DIRECTORY_SEPARATOR . "Factur-X.ps";

        $this->setIccProfile($defaultIcc);
        $this->setPsScript($defaultPs);
    }
}
