<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Validator;

class ThemesManagementController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $error = 0;
        $users = User::all();

        $themes = Theme::orderBy('name', 'asc')->get();        
        $data = [
            'error' => $error,
            'data' => $themes
        ];

        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('themesmanagement.add-theme');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = Input::only('name', 'link', 'notes', 'status');

        $validator = Validator::make($input, Theme::rules());

        if ($validator->fails()) {
            return $validator->messages();
        }

        $theme = Theme::create([
            'name'          => $request->input('name'),
            'link'          => $request->input('link'),
            'notes'         => $request->input('notes'),
            'status'        => $request->input('status'),
            'taggable_id'   => 0,
            'taggable_type' => 'theme',
        ]);

        $theme->taggable_id = $theme->id;
        $theme->save();        
        $data = [
            'error' => $error,
            'message' => trans('themes.createSuccess'),
            'data' => $theme
        ];
        
        return response()->json($data);
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
        $theme = Theme::find($id);
        $users = User::all();
        $themeUsers = [];

        foreach ($users as $user) {
            if ($user->profile && $user->profile->theme_id === $theme->id) {
                $themeUsers[] = $user;
            }
        }

        $data = [
            'theme'      => $theme,
            'themeUsers' => $themeUsers,
        ];

        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $theme = Theme::find($id);
        $users = User::all();
        $themeUsers = [];

        foreach ($users as $user) {
            if ($user->profile && $user->profile->theme_id === $theme->id) {
                $themeUsers[] = $user;
            }
        }

        $data = [
            'theme'      => $theme,
            'themeUsers' => $themeUsers,
        ];

        return response()->json($data);
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
        $theme = Theme::find($id);

        $input = Input::only('name', 'link', 'notes', 'status');

        $validator = Validator::make($input, Theme::rules($id));

        if ($validator->fails()) {
            return $validator->messages();
        }

        $theme->fill($input)->save();
        $data = [
            'error' => $error,
            'message' => trans('themes.updateSuccess'),
            'data' => $theme
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
        $default = Theme::findOrFail(1);
        $theme = Theme::findOrFail($id);
        $error = 1;
        $message = trans('themes.deleteSelfError');
        if ($theme->id != $default->id) {
            $theme->delete();
            $error = 0;
            $message = trans('themes.deleteSuccess');
        }
        $data = [
            'error' => $error,
            'message' => $message
        ];

        return response()->json($data);
    }
}
