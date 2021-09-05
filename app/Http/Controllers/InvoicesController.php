<?php

namespace App\Http\Controllers;

use App\Models\invoice_attachments;
use App\Models\invoices;
use App\Models\invoices_details;
use App\Models\sections;
use App\Models\User;
use App\Notifications\AddInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoicesExport;

class InvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $invoices = invoices::all();
        return view('invoices.invoices', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $sections = sections::all();
        return view('invoices.add_invoice', compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $Amount_Commission2 = floatval($request->amount_commission) - floatval($request->discount);
        $intResults = $Amount_Commission2 * floatval($request->rate_vat) / 100;
        $intResults2 = floatval($intResults + $Amount_Commission2);
        $sumq = number_format(floatval($intResults), 2, '.', "");
        $sumt = number_format(floatval($intResults2), 2, '.', "");
        invoices::create([
            'invoice_number' => $request->invoice_number,
            'invoice_date' => $request->invoice_date,
            'due_date' => $request->due_date,
            'product' => $request->product,
            'section_id' => $request->Section ,
            'amount_collection' => $request->amount_collection,
            'amount_commission' => $request->amount_commission,
            'discount' => $request->discount,
            'value_vat' => $sumq,
            'rate_vat' => $request->rate_vat,
            'total' => $sumt,
            'status' => 'غير مدفوعة',
            'value_status' => 2,
            'note' => $request->note,
        ]);

        $invoice_id = invoices::latest()->first()->id;
        invoices_details::create([
            'id_Invoice' => $invoice_id,
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            'Section' => $request->Section,
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
            'user' => auth()->user()->name,
        ]);

        if ($request->hasFile('pic')) {
            $file = $request->file('pic');
            $file_name = $file->getClientOriginalName();

            $attachments = new invoice_attachments();
            $attachments->file_name = $file_name;
            $attachments->invoice_number = $request->invoice_number;
            $attachments->Created_by = auth()->user()->name;
            $attachments->invoice_id = $invoice_id;
            $attachments->save();

            $request->pic->move(public_path('Attachments/Invoice-Num-'. $request->invoice_number), $file_name);
        }

        $user = User::get();
        Notification::send($user, new AddInvoice($invoice_id));
        session()->flash('Add', 'تم اضافة الفاتورة بنجاح');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoice = invoices::where('id', $id)->first();
        return view('invoices.status_update', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $invoice = invoices::find($id);
        $sections = sections::all();
        return view('invoices.edit_invoice', compact('sections','invoice'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $Amount_Commission2 = floatval($request->amount_commission) - floatval($request->discount);
        $intResults = $Amount_Commission2 * floatval($request->rate_vat) / 100;
        $intResults2 = floatval($intResults + $Amount_Commission2);
        $sumq = number_format(floatval($intResults), 2, '.', "");
        $sumt = number_format(floatval($intResults2), 2, '.', "");
        invoices::findOrFail($request->invoice_id)->update([
            'invoice_number' => $request->invoice_number,
            'invoice_date' => $request->invoice_date,
            'due_date' => $request->due_date,
            'product' => $request->product,
            'section_id' => $request->section ,
            'amount_collection' => $request->amount_collection,
            'amount_commission' => $request->amount_commission,
            'discount' => $request->discount,
            'value_vat' => $sumq,
            'rate_vat' => $request->rate_vat,
            'total' => $sumt,
            'note' => $request->note,
        ]);

        invoices_details::where('id_Invoice', $request->invoice_id)->update([
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            'Section' => $request->section,
            'note' => $request->note,
        ]);
        session()->flash('edit', 'تم تعديل الفاتورة بنجاح');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $invoice = invoices::withTrashed()->find($request->invoice_id);
        if ($request->id_page) {
            $invoice->delete();
            session()->flash('archive_invoice');
        } else {
            $dirPath = public_path().'/Attachments/Invoice-Num-'.$invoice->invoice_number;
            if(File::exists($dirPath)) {
                File::deleteDirectory($dirPath);
            }
            $invoice->forceDelete();
            session()->flash('delete_invoice');
        }
        return back();
    }

    public function getproducts($id)
    {
        $products = DB::table('products')->where('section_id', $id)->pluck('Product_name', 'id');
        return json_encode($products);
    }

    public function statusUpdate($id, Request $request)
    {
        $invoice = invoices::findOrFail($id);
        $value_status = !empty($request->status) ?
            ( $value_status = $request->status === 'مدفوعة' ?
            1 : 3 ) : 2 ;
        if ($value_status === 3 || $value_status === 1) {
            $invoice->update([
                'value_status' => $value_status,
                'status' => $request->status,
                'payment_date' => $request->payment_date,
                'note' => $request->note
            ]);
            invoices_details::create([
                'id_Invoice' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'product' => $invoice->product,
                'Section' => $invoice->section_id,
                'Status' => $request->status,
                'Value_Status' => $value_status,
                'payment_date' => $request->payment_date,
                'note' => $request->note,
                'user' => auth()->user()->name
            ]);
            session()->flash('status_update');
            return redirect('/invoices');
        }
        session()->flash('status_update_faild');
        return back();
    }


    public function paidInvoices()
    {
        $paidInvoices = invoices::where('value_status', 1)->get();
        return view('invoices.invoices_paid', compact('paidInvoices'));
    }
    public function unpaidInvoices()
    {
        $unPaidInvoices = invoices::where('value_status', 2)->get();
        return view('invoices.invoices_unpaid', compact('unPaidInvoices'));
    }
    public function partialInvoices()
    {
        $patialPaidInvoices = invoices::where('value_status', 3)->get();
        return view('invoices.invoices_partial', compact('patialPaidInvoices'));
    }
    public function archivedInvoices()
    {
        $archivedInvoices = invoices::onlyTrashed()->get();
        return view('invoices.archive_Invoices', compact('archivedInvoices'));
    }
    public function unArchiveInvoice(Request $request)
    {
        invoices::withTrashed()->where('id', $request->invoice_id)->restore();
        session()->flash('restore_invoice');
        return back();
    }
    public function printInvoice($id)
    {
        $invoice = invoices::where('id', $id)->first();
        return view('invoices.print_invoice', compact('invoice'));
    }

    public function export()
    {
        return Excel::download(new InvoicesExport, 'invoices.xlsx');
    }
    public function markAllAsRead()
    {
        $unreadNotifications = auth()->user()->unreadNotifications;
        if ($unreadNotifications) {
            $unreadNotifications->markAsRead();
            return back();
        }
    }

}
