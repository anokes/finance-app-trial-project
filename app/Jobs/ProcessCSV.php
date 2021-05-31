<?php

namespace App\Jobs;

use App\Events\ProcessCSVCompleted;
use App\Models\BalanceItem;
use App\Models\EntriesCSV;
use Illuminate\Support\Facades\Log;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class ProcessCSV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $csv;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(EntriesCSV $csv)
    {
        $this->csv = $csv;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            //Process the file:

            $file = fopen("storage/app/" . $this->csv->path, "r");

            //array to hold the data from the csv
            $importData_arr = array();
            $i = 0;
            while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
                $num = count($filedata);

                // Skip first row for the header
                if($i == 0){
                   $i++;
                   continue; 
                }
                for ($c = 0; $c < $num; $c++) {

                    //populating the array from the csv rows
                    $importData_arr[$i][] = $filedata[$c];
                }
                $i++;
            }
            fclose($file);
            // set the csv as processed
            $this->csv->processed = true;
            $this->csv->save();

            // Insert into database
          foreach($importData_arr as $importData){
            
            $insertData = array(
               "label"=>$importData[0],
               "amount"=>$importData[1],
               "entry_date"=>$importData[2],
               "user_id"=>$this->csv->user_id);

            BalanceItem::insertData($insertData);            

          }

          //fire the event once all of the records have been inserted
          event(new ProcessCSVCompleted("Job Completed"));

        } catch (\Exception $exception) {
            Log::debug($exception->getMessage());
            return $exception->getMessage();
        }
    }
}
