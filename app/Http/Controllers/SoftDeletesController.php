<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use DB;
use Illuminate\Http\Request;
use jeremykenedy\LaravelRoles\Models\Role;

class SoftDeletesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get Soft Deleted User.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public static function getDeletedUser($id)
    {
        $error = 0;
        $user = User::onlyTrashed()->where('id', $id)->get();
        if (count($user) != 1) {
            $error = 1;
            $data = [
                'error' => $error,
                'message' => trans('usersmanagement.errorUserNotFound')
            ];
            return response()->json($data);
        }

        $data = [
            'error' => $error,
            'data' => $user
        ];
        return response()->json($data);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::onlyTrashed()->get();
        $roles = Role::all();
        $users_roles = DB::table('role_user')->get();

        foreach($users_roles as $user_role){
            foreach($users as $key => $user){
                if( $user['id'] ==  $user_role->user_id ){
                    foreach( $roles as $role ){
                        if( $role->id == $user_role->role_id ){
                            $users[$key]['role'] = $role->name;
                        }
                    }
                }
            }
        }

        return response()->json($users);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = self::getDeletedUser($id);

        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $error = 0;
        $user = self::getDeletedUser($id);
        $user->restore();
        $data = [
            'error' => $error,
            'message' => trans('usersmanagement.successRestore'),
            'data' => $user
        ];

        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $error = 0;
        $user = self::getDeletedUser($id);
        $user->forceDelete();
        $data = [
            'error' => $error,
            'message' => trans('usersmanagement.successDestroy'),
            'data' => $user
        ];

        return response()->json($data);
    }
}
