<?php

namespace App\Console\Commands;

use App\Classes\SalesReportHandler;
use App\Mail\ReportMail;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class DailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:daily-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily report to some set of employees.';

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
        $companies = Company::has('locations')->get();
        foreach ($companies as $company) {
            $reportHandler = new SalesReportHandler($company->id);
            $mail_recipients = Employee::where(function (Builder $q) use ($company) {
                    $q->where('company_id', $company->id)
                        ->orWhereNull('company_id');
                })
                ->where('receive_reports', true)->pluck('email')->toArray();
            $data = $reportHandler->ReportStatistics();
            $subject = "MyWashApp - Daily Report | Company::{$company->name}";
            $header = "Sales Report For " . now()->format('F jS Y');
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
