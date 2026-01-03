<?php

namespace App\Services;

use App\Models\Transaction;
use App\Settings\PrintSettings;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Exception;

class PrintService
{
    public function printReceipt(Transaction $transaction)
    {
        $settings = app(PrintSettings::class);

        try {
            $connector = new WindowsPrintConnector($settings->printer_name);
            $printer = new Printer($connector);

            // --- HEADER ---
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH | Printer::MODE_DOUBLE_HEIGHT);
            $printer->text($settings->brand_name . "\n");
            $printer->selectPrintMode(); // Normal mode

            if ($settings->header_1)
                $printer->text($settings->header_1 . "\n");
            if ($settings->header_2)
                $printer->text($settings->header_2 . "\n");
            $printer->text(str_repeat("-", 32) . "\n");

            // --- INFO TRANSAKSI ---
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Nota   : " . $transaction->invoice_number . "\n");
            $printer->text("Tgl    : " . $transaction->created_at->format('d/m/y H:i') . "\n");
            $printer->text("Cust   : " . substr($transaction->customer_name, 0, 20) . "\n");
            $printer->text("Nopol  : " . ($transaction->plate_number ?? '-') . "\n");
            $printer->text("Mekanik: " . ($transaction->queueService->mechanic->name ?? '-') . "\n");
            $printer->text(str_repeat("-", 32) . "\n");

            // --- ITEMS ---
            foreach ($transaction->items as $item) {
                // Baris 1: Nama Produk
                $printer->text($item->name . "\n");
                // Baris 2: Qty x Harga ...... Subtotal
                $left = $item->qty . " x " . number_format($item->price, 0, ',', '.');
                $right = number_format($item->subtotal, 0, ',', '.');
                $printer->text($this->formatLeftRight($left, $right, 32) . "\n");
            }
            $printer->text(str_repeat("-", 32) . "\n");

            // --- FOOTER / TOTAL ---
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            if ($transaction->discount_amount > 0) {
                $printer->text("Diskon: " . number_format($transaction->discount_amount, 0, ',', '.') . "\n");
            }
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->text("TOTAL : " . number_format($transaction->total_amount, 0, ',', '.') . "\n");
            $printer->selectPrintMode();

            if ($transaction->payment_method === 'cash') {
                $printer->text("Bayar : " . number_format($transaction->payment_received, 0, ',', '.') . "\n");
                $printer->text("Kembali: " . number_format($transaction->payment_received - $transaction->total_amount, 0, ',', '.') . "\n");
            }
            $printer->text("\n");

            // --- CUSTOM FOOTER ---
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            if ($settings->footer_1)
                $printer->text($settings->footer_1 . "\n");
            if ($settings->footer_2)
                $printer->text($settings->footer_2 . "\n");
            if ($settings->footer_3)
                $printer->text($settings->footer_3 . "\n");

            $printer->feed(3);
            $printer->cut();
            $printer->close();

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // Helper untuk meratakan teks kiri dan kanan dalam satu baris
    private function formatLeftRight($left, $right, $width)
    {
        $totalLen = strlen($left) + strlen($right);
        $spaces = str_repeat(" ", max(0, $width - $totalLen));
        return $left . $spaces . $right;
    }
}