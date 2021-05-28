<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BalanceItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BalanceItemsController extends Controller
{
    public function show()
    {
        $user_id = Auth::id();

        //get all items for the user
        $items = BalanceItem::select(DB::raw('id, label, entry_date, (amount / 100) as amount, DATE(entry_date) as date'))
                              ->orderBy('entry_date', 'desc')
                              ->where('user_id', '=', $user_id)
                              ->get();

        //get daily subtotals for the user
        $groupedItems = BalanceItem::select(DB::raw('sum(amount / 100) as `amountSum`'), DB::raw('DATE(entry_date) as date'))
                                     ->groupBy('date')
                                     ->where('user_id', '=', $user_id)
                                     ->orderBy('date', 'desc')
                                     ->paginate(5);

        //get total amount for the user
        $sumOfItems = BalanceItem::select(DB::raw('sum(amount / 100) as `amountSum`'))
                                   ->where('user_id', '=', $user_id)
                                   ->get();
        
        
        
        //return items to the vue component
        return response()->json(['items' => $items, 'daily_totals' => $groupedItems, 'total_amount' => $sumOfItems], 200);
    }


    public function store(Request $request)
    {

        $this->validate($request, [
            'label' => 'required|string',
            'entry_date' => 'required|date',
            'amount' => 'required|numeric',
        ]);

        //store all monetary values in the database as cents
        $amountValue = $request->amount * 100;

        //merge userid and 
        $request->merge([
            'user_id' => Auth::id(),
            'amount' => $amountValue,
        ]);

        $balanceitem = BalanceItem::create($request->all());

        return response()->json($balanceitem, 201);
    }

    //method to update a balance entry
    public function update(Request $request)
    {

        $this->validate($request, [
            'label' => 'required|string',
            'entry_date' => 'required|date',
            'amount' => 'required|numeric',
        ]);

        //store all monetary values in the database as cents
        $amountValue = $request->amount * 100;

        //merge userid and 
        $request->merge([
            'user_id' => Auth::id(),
            'amount' => $amountValue,
        ]);

        //find the entry by it's id and update it
        $balanceitem = BalanceItem::find($request->input('id'))->update($request->all());

        return response()->json($balanceitem, 201);
    }

    public function delete(Request $request)
    {

        

        

        $balanceitem = BalanceItem::find($request->input('id'))->delete();

        return response()->json(null, 201);
    }
}
