<?php

namespace App\Http\Controllers;

use App\Models\invoices;
use Illuminate\Http\Request;

class InvoicesReport extends Controller
{
    public function index()
    {
        return view('reports.invoices_report');
    }

    public function search(Request $request)
    {
        $searchType = $request->searchType;
        $status = $request->invoiceStatus;
        if ($searchType == 1) {

            if ($status && $request->start_at == '' && $request->end_at == '') {
                $details = invoices::select('*')->where('status', '=', $status)->get();
                return view('reports.invoices_report', compact('status', 'details'));
            } else {
                $start_at = date($request->start_at);
                $end_at = date($request->end_at);

                $details = invoices::whereBetween('invoice_date',[$start_at,$end_at])->where('status','=',$status)->get();
                return view('reports.invoices_report',compact('status','start_at','end_at','details'));
            }

        } else {
            $details = invoices::select('*')->where('invoice_number','=',$request->invoice_number)->get();
            return view('reports.invoices_report', compact('details'));
        }

    }
}
