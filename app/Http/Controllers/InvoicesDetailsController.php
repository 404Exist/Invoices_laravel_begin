<?php

namespace App\Http\Controllers;

use App\Models\invoice_attachments;
use App\Models\invoices;
use App\Models\invoices_details;
use App\Models\sections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoicesDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\invoices_details  $invoices_details
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoices = invoices::find($id);
        $invoices_details = invoices_details::where('id_Invoice', $id)->get();
        $invoice_attachments  = invoice_attachments::where('invoice_id',$id)->get();
        return view('invoices.details_invoice', compact('invoices', 'invoices_details', 'invoice_attachments'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\invoices_details  $invoices_details
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
       $invoice = invoices_details::where('id', $id)->first();
       $sections = sections::all();
       return view('invoices.edit_invoice_details', compact('invoice','sections'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\invoices_details  $invoices_details
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $invoices_details = invoices_details::findOrFail($request->invoice_details_id);
        $invoices_details->update([
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            'Section' => $request->section,
            'note' => $request->note,
        ]);

        invoices::findOrFail($invoices_details->first()->id_Invoice)->update([
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            'section_id' => $request->section ,
            'note' => $request->note,
        ]);
        session()->flash('edit', 'تم تعديل تفاصيل الفاتوره بنجاح');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\invoices_details  $invoices_details
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $invoice_attachment = invoice_attachments::findOrFail($request->id_file);
        $invoice_attachment->delete();
        Storage::disk('public_uploads')->delete('Invoice-Num-'.$request->invoice_number.'/'.$request->file_name);
        session()->flash('delete', 'تم حذف المرفق بنجاح');
        return back();
    }

    public function viewFile($invoice_number, $file_name)
    {
        // $files = Storage::disk('public_uploads')->getDriver()->getAdapter()->applyPathPrefix('Invoice-Num-'.$invoice_number.'/'.$file_name);
        $files = public_path().'/Attachments/Invoice-Num-'.$invoice_number.'/'.$file_name;
        if(file_exists($files)) {
            return response()->file($files);
        } else {
            return view('404');
        }

    }

    public function downloadFile($invoice_number, $file_name)
    {
        $files = public_path().'/Attachments/Invoice-Num-'.$invoice_number.'/'.$file_name;
        if(file_exists($files)) {
            return response()->download($files);
        } else {
            return view('404');
        }
    }
}
