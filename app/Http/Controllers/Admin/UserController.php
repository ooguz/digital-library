<?php

namespace App\Http\Controllers\Admin;

use App\Country;
use App\Events\UserAdded;
use App\Helper\Datatables;
use App\Issue;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class UserController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!isset($_GET['json']))
            return view('admin.datatables', [
                'title' => 'Kullanıcılar',
                'thead' => ['id', 'Admin?', 'Ban Durumu', 'Adı - Soyadı', 'Eposta', 'Ülke', 'Dil', 'Meslek', 'Düzenle'],
            ]);

        return response()->json(
            Datatables::simple($request->all(), 'users', 'id', $this->userService->getDatatableColumns())
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.users.create', [
            'countries' => Country::all(),
            'issues_all_count' => Issue::all('id')->count()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:users'],
            'is_admin' => ['required', 'boolean'],
            'language' => ['required', 'string', 'regex:(tr|en)'],
            'country_id' => ['required', 'exists:countries,id'],
        ]);

        $data = $this->userService->store($request);

        // Create user
        $user = User::create($data);

        // Trigger events
        event(new UserAdded($user));

        // Return
        Session::flash('class', 'success');
        Session::flash('message', 'Kullanıcı başarıyla eklendi!');

        return redirect()->route('admin.users.edit', $user->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', [
            'user' => $user,
            'countries' => Country::all(),
            'issues_all_count' => Issue::all('id')->count(),
            'purchases_tr' => json_decode($user->purchases_tr, true),
            'purchases_en' => json_decode($user->purchases_en, true)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'is_admin' => ['required', 'boolean'],
            'is_banned' => ['required', 'boolean'],
            'language' => ['required', 'string', 'regex:(tr|en)'],
            'country_id' => ['required', 'exists:countries,id'],
        ]);

        $data = $this->userService->update($request);

        // Update user
        User::findOrFail($id)->update($data);

        // Return
        Session::flash('class', 'success');
        Session::flash('message', 'Kullanıcı başarıyla güncellendi!');

        return redirect()->route('admin.users.edit', $id);
    }

}
