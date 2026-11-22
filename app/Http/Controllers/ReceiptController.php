<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Services\WhatsAppInvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReceiptController extends Controller
{
    protected WhatsAppInvoiceService $whatsAppService;

    public function __construct(WhatsAppInvoiceService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    public function thermalReceipt(Order $order)
    {
        $order->loadMissing(['table', 'items.menuItem', 'items.variant']);
        return view('receipts.thermal', compact('order'));
    }

    public function kotReceipt(Order $order, Request $request)
    {
        $order->loadMissing(['table', 'items.menuItem', 'items.variant']);

        // Filter only pending KOT items unless all=1 is requested
        if ($request->query('all') == '1') {
            $items = $order->items;
        } else {
            $items = $order->items->where('kot_status', 'pending');
        }

        // If items were pending, mark them as printed
        if ($items->isNotEmpty()) {
            foreach ($items as $item) {
                if ($item->kot_status === 'pending') {
                    $item->update(['kot_status' => 'printed']);
                }
            }

            AuditLog::record(
                $order->id,
                'kot_printed',
                null,
                ['printed_items_count' => $items->count()],
                'KOT printed for ' . $items->count() . ' items.',
                Auth::id()
            );
        }

        return view('receipts.kot', compact('order', 'items'));
    }

    public function downloadPdf(Order $order, Request $request)
    {
        $order->loadMissing(['table', 'items.menuItem', 'items.variant']);

        $restaurant = [
            'name' => config('app.restaurant_name', 'Dikshita Restro & Cafe'),
            'address' => config('app.restaurant_address', 'Main Street, Tech Zone, Bengaluru, KA - 560100'),
            'phone' => config('app.restaurant_phone', '+91 98765 43210'),
            'email' => config('app.restaurant_email', 'billing@dikshitarestro.com'),
            'gstin' => config('app.restaurant_gstin', '29AABCU9603R1ZM'),
            'fssai' => config('app.restaurant_fssai', '11223344556677'),
        ];

        if ($request->query('format') === 'a4') {
            $pdf = Pdf::loadView('invoices.pdf', compact('order', 'restaurant'));
            $pdf->setPaper('a4', 'portrait');
            return $pdf->stream('Invoice-' . $order->order_number . '.pdf');
        }

        // 80mm thermal receipt PDF (default for WhatsApp & POS receipts)
        $itemCount = $order->items->count();
        $heightPt = max(450, 370 + ($itemCount * 26));

        $pdf = Pdf::loadView('receipts.thermal_pdf', compact('order', 'restaurant'));
        $pdf->setPaper([0, 0, 226.77, $heightPt], 'portrait');

        return $pdf->stream('ThermalReceipt-' . $order->order_number . '.pdf');
    }

    public function whatsappShare(Order $order, Request $request)
    {
        $phone = $request->query('phone', $order->customer_phone);
        $shareUrl = $this->whatsAppService->getWhatsAppShareUrl($order, $phone);

        return redirect()->away($shareUrl);
    }

    public function printTableQrs()
    {
        $tables = RestaurantTable::orderBy('table_number')->get();
        return view('admin.print_qrs', compact('tables'));
    }
}
