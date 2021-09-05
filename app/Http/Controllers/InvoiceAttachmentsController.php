<?php

namespace App\Http\Controllers;

use App\Models\invoice_attachments;
use Illuminate\Http\Request;

class InvoiceAttachmentsController extends Controller
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
        $this->validate($request, [
            'file_name' => 'mimes:png,jpg,jpeg,pdf',
        ],[
            'file_name.mimes' => 'صيغة المرفق يجب أن تكون png,jpg,jpeg,pdf',
        ]);

        $file = $request->file_name;
        $file_name = $file->getClientOriginalName();

        invoice_attachments::create([
            'file_name' => $file_name,
            'invoice_number' => $request->invoice_number,
            'invoice_id' => $request->invoice_id,
            'Created_by' => auth()->user()->name,
        ]);
        $request->file_name->move(public_path('Attachments/Invoice-Num-'.$request->invoice_number), $file_name);
        session()->flash('Add', 'تم إضافة المرفق بنجاح');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\invoice_attachments  $invoice_attachments
     * @return \Illuminate\Http\Response
     */
    public function show(invoice_attachments $invoice_attachments)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\invoice_attachments  $invoice_attachments
     * @return \Illuminate\Http\Response
     */
    public function edit(invoice_attachments $invoice_attachments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\invoice_attachments  $invoice_attachments
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, invoice_attachments $invoice_attachments)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\invoice_attachments  $invoice_attachments
     * @return \Illuminate\Http\Response
     */
    public function destroy(invoice_attachments $invoice_attachments)
    {
        //
    }
}
