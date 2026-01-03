<?php

namespace Modules\Superadmin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Superadmin\Models\ManualPayment;
use Modules\Superadmin\Models\Subscription;
use Modules\Superadmin\Services\ManualPaymentService;
use App\Business;

class ManualPaymentController extends Controller
{
    protected $paymentService;

    public function __construct(ManualPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $query = ManualPayment::with(['business', 'subscription']);
        
        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        $payments = $query->latest()->paginate(20);
        
        $stats = [
            'total' => ManualPayment::count(),
            'pending' => ManualPayment::where('status', 'pending')->count(),
            'approved' => ManualPayment::where('status', 'approved')->count(),
            'rejected' => ManualPayment::where('status', 'rejected')->count(),
        ];
        
        return view('superadmin::manual-payments.index', compact('payments', 'stats'));
    }

    public function pending()
    {
        $payments = ManualPayment::with(['business', 'subscription'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);
            
        return view('superadmin::manual-payments.pending', compact('payments'));
    }

    public function show($id)
    {
        $payment = ManualPayment::with(['business', 'subscription.package'])->findOrFail($id);
        
        return view('superadmin::manual-payments.show', compact('payment'));
    }

    public function approve(Request $request, $id)
    {
        try {
            $payment = ManualPayment::findOrFail($id);
            
            $validated = $request->validate([
                'approved_amount' => 'nullable|numeric|min:0',
                'admin_notes' => 'nullable|string',
            ]);
            
            $this->paymentService->approvePayment(
                $payment,
                $validated['approved_amount'] ?? null,
                $validated['admin_notes'] ?? null,
                auth()->id()
            );
            
            return redirect()->back()->with('success', 'Payment approved successfully!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to approve payment: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            $payment = ManualPayment::findOrFail($id);
            
            $validated = $request->validate([
                'rejection_reason' => 'required|string',
            ]);
            
            $this->paymentService->rejectPayment(
                $payment,
                $validated['rejection_reason'],
                auth()->id()
            );
            
            return redirect()->back()->with('success', 'Payment rejected!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to reject payment: ' . $e->getMessage());
        }
    }
}
