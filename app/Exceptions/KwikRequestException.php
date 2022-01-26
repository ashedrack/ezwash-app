<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use function Psy\debug;

class KwikRequestException extends Exception
{
    protected $errorToDisplay = 'An Error occurred while processing a delivery/pickup request';
    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        logCriticalError($this->errorToDisplay, $this);
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render(Request $request)
    {
        logger('back()->getTargetUrl()  ::' . $request->hasPreviousSession());
        $data = ['status' => 'error', 'title' => 'Something went wrong', 'message' => $this->errorToDisplay];
        if($request->hasPreviousSession()) {
            return redirect()->back()->with($data);
        }
        return redirect()->route('home')->with($data);
    }
}
