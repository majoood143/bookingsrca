<?php

namespace App\Services\Printing;

use RuntimeException;
use Throwable;
use App\Models\BookingSetting;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\PrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class PrintConnectorFactory
{
    private string $driver;
    private string $networkHost;
    private int    $networkPort;
    private int    $timeoutSeconds;
    private string $cupsPrinterName;
    private string $windowsPrinterName;
    private string $filePath;

    public function __construct()
    {
        $this->driver             = (string) BookingSetting::get('printer.driver', 'network');
        $this->networkHost        = (string) BookingSetting::get('printer.network.host', '');
        $this->networkPort        = (int)    BookingSetting::get('printer.network.port', 9100);
        $this->timeoutSeconds     = (int)    BookingSetting::get('printer.timeout_seconds', 5);
        $this->cupsPrinterName    = (string) BookingSetting::get('printer.cups.printer_name', '');
        $this->windowsPrinterName = (string) BookingSetting::get('printer.windows.printer_name', '');
        $this->filePath           = (string) BookingSetting::get('printer.file.path', storage_path('app/printing/last-print.bin'));
    }

    public function make(): PrintConnector
    {
        return match ($this->driver) {
            'network' => $this->makeNetworkConnector(),
            'cups'    => $this->makeCupsConnector(),
            'windows' => $this->makeWindowsConnector(),
            'file'    => $this->makeFileConnector(),
            default   => throw new RuntimeException("Unknown printer driver '{$this->driver}'."),
        };
    }

    public function isConfigured(): bool
    {
        return match ($this->driver) {
            'network' => $this->networkHost !== '',
            'cups'    => $this->cupsPrinterName !== '',
            'windows' => $this->windowsPrinterName !== '',
            'file'    => $this->filePath !== '',
            default   => false,
        };
    }

    private function makeNetworkConnector(): PrintConnector
    {
        if ($this->networkHost === '') {
            throw new RuntimeException('Thermal printer network host is not configured.');
        }

        try {
            return new NetworkPrintConnector($this->networkHost, $this->networkPort, $this->timeoutSeconds);
        } catch (Throwable $e) {
            throw new RuntimeException(
                "Could not reach thermal printer at {$this->networkHost}:{$this->networkPort} ({$e->getMessage()}).",
                previous: $e
            );
        }
    }

    private function makeCupsConnector(): PrintConnector
    {
        if (!extension_loaded('cups')) {
            throw new RuntimeException('CUPS printing is not available on this server (the "cups" PHP extension is not installed).');
        }

        if ($this->cupsPrinterName === '') {
            throw new RuntimeException('CUPS printer name is not configured.');
        }

        try {
            return new CupsPrintConnector($this->cupsPrinterName);
        } catch (Throwable $e) {
            throw new RuntimeException(
                "Could not open CUPS printer '{$this->cupsPrinterName}' ({$e->getMessage()}).",
                previous: $e
            );
        }
    }

    private function makeWindowsConnector(): PrintConnector
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            throw new RuntimeException('The "windows" printer driver only works when PHP itself runs on Windows. Use the network or CUPS driver instead.');
        }

        if ($this->windowsPrinterName === '') {
            throw new RuntimeException('Windows printer name is not configured.');
        }

        try {
            return new WindowsPrintConnector($this->windowsPrinterName);
        } catch (Throwable $e) {
            throw new RuntimeException(
                "Could not open Windows printer '{$this->windowsPrinterName}' ({$e->getMessage()}).",
                previous: $e
            );
        }
    }

    private function makeFileConnector(): PrintConnector
    {
        if ($this->filePath === '') {
            throw new RuntimeException('Printer diagnostic file path is not configured.');
        }

        if (!is_dir(dirname($this->filePath))) {
            mkdir(dirname($this->filePath), 0755, true);
        }

        try {
            return new FilePrintConnector($this->filePath);
        } catch (Throwable $e) {
            throw new RuntimeException(
                "Could not open printer output file '{$this->filePath}' ({$e->getMessage()}).",
                previous: $e
            );
        }
    }
}
