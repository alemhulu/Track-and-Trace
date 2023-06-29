<?php

namespace App\Http\Controllers;

use App\Models\PrintOrder;
use Illuminate\Http\Request;

class PrintOrderController extends Controller
{

    public function index()
    {
        //
    }


    public function create()
    {
        //
    }

    public function store($id)
    {
       
    }


    public function show( $printOrder)
    {
        $order = PrintOrder::findOrFail($printOrder);
        return view('main.print-order.show', compact('order'));
    }


    public function edit(PrintOrder $printOrder)
    {
        //
    }


    public function update(Request $request, PrintOrder $printOrder)
    {
        //
    }


    public function destroy(PrintOrder $printOrder)
    {
        //
    }
}
