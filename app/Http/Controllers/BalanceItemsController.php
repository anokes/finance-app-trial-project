<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\BalanceItem;
use App\Models\EntriesCSV;
use App\Jobs\ProcessCSV;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


use Illuminate\Support\Facades\Log;

class BalanceItemsController extends Controller
{
    //define a constant for the valid 1st line of the csv to ensure it's compatability - would probably store this in the database to be edited by an admin panel
    public const VALID_CSV_FORMAT = "Label,Value,Date";

    //method that gets all of the user's balance entries and returns them to the main page for crud operations
    public function show()
    {
        $user_id = Auth::id();

        //get all items for the user
        $items = BalanceItem::select(DB::raw('id, label, entry_date, (amount / 100) as amount, DATE(entry_date) as date, user_id'))
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

    //method to store new balance entries
    public function store(Request $request)
    {
        //validate th request
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

        //create the entry
        $balanceitem = BalanceItem::create($request->all());

        //return the response
        return response()->json($balanceitem, 201);
    }

    //method to update a balance entry
    public function update(Request $request)
    {


        $this->validate($request, [
            'label' => 'required|string',
            'entry_date' => 'required|date',
            'amount' => 'required|numeric',
            'user_id' => 'required|in:' . Auth::id(),
        ]);



        //store all monetary values in the database as cents
        $amountValue = $request->amount * 100;

        //merge userid and 
        $request->merge([
            'amount' => $amountValue,
        ]);

        //find the entry by it's id and update it
        $balanceitem = BalanceItem::find($request->input('id'))->update($request->all());

        return response()->json($balanceitem, 201);
    }

    //method to delete a specified entry
    public function delete(Request $request)
    {
        //delete the entry using the id passed in the request
        $balanceitem = BalanceItem::find($request->input('id'))->delete();

        //return the status code
        return response()->json(null, 201);
    }

    //method for handling the import of a csv file
    public function import(Request $request)
    {

        //setup a better message for the user
        $messages = [
            'file.mimes' => 'You must select a .CSV file for upload.'
        ];

        //validate the file
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt',
        ], $messages);



        //check to see if there is a file in the response
        if ($request->hasFile('file')) {

            //read the first line to ensure it's a valid format
            $file = fopen($request->file, "r");

            $i = 1;
            $lineCount = 0;
            while (!feof($file)) {

                $line = fgets($file);

                //check the first line for the format
                if (strlen($line) > 0 && $i == 1) {

                    if (trim($line) != self::VALID_CSV_FORMAT) {

                        //if it's not a valid .CSV, add the error message to the validation
                        $validator->after(function ($validator) {

                            $validator->errors()->add('file', 'The uploaded file is not in a valid .CSV format for import.');
                        });

                        break;
                    }
                }
                $i++;
                $lineCount++;
            }
        }


        if ($validator->fails()) {
            //return response if it's not valid
            return response()->json(['errors' => $validator->errors()], 422);
        } else {

            //upload the file and dispatch the job if it's a valid file


            $fileObject = $request->file;

            //get the file's extension
            $extension = strtolower($fileObject->getClientOriginalExtension());

            //store the file
            $path = Storage::putFileAs("balance_entries", $fileObject, uniqid() . "." . $extension);

            //create the CSV entry vie it's model
            $uploadedFile = EntriesCSV::create([
                'path' => $path,
                'processed' => false,
                'user_id' => Auth::id(),
            ]);

            //dispatch the job
            dispatch(new ProcessCSV($uploadedFile));
        }

        //return the status code an line count (-1 for the header)
        return response()->json(['lineCount' => ($lineCount - 1)], 200);
    }


}
