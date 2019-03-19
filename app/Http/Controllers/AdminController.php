<?php

namespace App\Http\Controllers;

use App\Services\Admin\IssueService;
use App\Services\Admin\UserService;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AdminController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $userService;
    protected $issueService;

    /**
     * Create a new controller instance.
     *
     * @param UserService $userService
     * @param IssueService $issueService
     *
     * @return void
     */
    public function __construct(UserService $userService, IssueService $issueService)
    {
        $this->middleware('auth');
        $this->middleware('is_admin');

        $this->userService = $userService;
        $this->issueService = $issueService;
    }

}
