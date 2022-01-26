<?php

namespace App\Console\Commands;

use App\Classes\SalesReportHandler;
use App\Mail\ReportMail;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MonthlyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:monthly-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send monthly reports to some specific employees..';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $companies = Company::all();
        foreach ($companies as $company) {
            $reportHandler = new SalesReportHandler($company->id, false);
            $mail_recipients = Employee::where(function ($q) use ($company) {
                $q->where('company_id', $company->id)
                    ->orWhereNull('company_id');
            })
                ->where('receive_reports', true)->pluck('email')->toArray();
            $data = $reportHandler->ReportStatistics();
            $subject = "Ezwash - Monthly Report | Company::{$company->name}";
            $header = "Sales Report For " . now()->subMonth()->format('F Y');
            Mail::to($mail_recipients)->send(
                new ReportMail([
                    'stats_data' => $data,
                    'subject' => $subject,
                    'header' => $header,
                    'reportHandler' => $reportHandler
                ])
            );
        }
        return true;
    }

}
