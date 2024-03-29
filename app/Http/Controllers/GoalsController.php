<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Goal;
use App\Policies\ViewPolicy;


class GoalsController extends Controller
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

    public function index() {
      
    	$user = Auth::user();

    //fetching the goal
    $goals = Goal::where('owner_id', $user->id)->get();

       return response()->json(['data' => ['success' => true, 'goals' => $goals]], 200);

    }

    public function show(Goal $goal, ViewPolicy $viewpolicy, $goal_id) {
         Auth::user();
         $check_id = Goal::where('id', $goal_id)->exists();

         if ($check_id) {

             $detail = $viewpolicy->userPassage($goal_id);

             if ($detail) {

                $data = $goal->findOrfail($goal_id);

                return response()->json(['data' => [ 'success' => true, 'goal' => $data]], 200);

             }else {

                return response()->json(['data' => [ 'error' => false, 'message' => 'Unauthorize Access']], 401);
             }

         }else{

             return response()->json(['data' => [ 'error' => false, 'message' => 'Not Found']], 404);
         }
        	
     }
    public function store(Request $request, Goal $goal) {

      $user = Auth::user();
       
       $attributes = $this->validate($request, [
    		'title'       => 'required',
    		'description' => 'required|min:5',
            'begin_date'  => 'required',
            'due_date'    => 'required',
    		'level'       => 'required'
    	]);  

       $check_title = Goal::where('owner_id', $user->id)->where('title', $request->input('title'))->exists();

        if($check_title) {

           return response()->json(['data' => ['error' => false, 'message' => 'Title already exist']], 401);

        }else{

              $goal->owner_id    = $user->id;  
              $goal->title       =  ucwords($request->input('title'));
              $goal->description =  ucfirst($request->input('description'));
              $goal->begin_date  = $request->input('begin_date');
              $goal->due_date  = $request->input('due_date');  
              $goal->level       =  ucfirst($request->input('level'));
              $goal->goal_status     = 0;

              $saved = $goal->save();

                if ($saved) {
                   return response()->json(['data' => ['success' => true, 'goal' => $goal]], 201);
                }else{
                    return response()->json(['data' => ['error' => false, 'message' => 'An Error Occured!']], 401); 
                }
        }

    }

    public function update(Request $request, Goal $goal, ViewPolicy $viewpolicy, $goal_id) {
        $user = Auth::user();
         $check_id = Goal::where('id', $goal_id)->exists();

         if ($check_id) {
             $fact = $viewpolicy->userPassage($goal_id);

             if ($fact) {
                 $this->validate($request, [
                  'title'       => 'required',
                  'description' => 'required|min:5',
                  'level'       => 'required',
                  'begin_date'  => 'required',
                  'due_date'    => 'required'
                ]); 

                $check_title = Goal::where('owner_id', $user->id)->where('title', $request->input('title'))->first();

                  if($check_title) {
                        return response()->json(['data' => ['error' => false, 'message' => 'Title already exist']], 401);
                    }else{

                        $data = $goal->findOrfail($goal_id);

                        $data->title       = $request->input('title');
                        $data->description = $request->input('description');
                        $data->level       = $request->input('level');
                        $data->begin_date  = $request->input('begin_date');
                        $data->due_date  = $request->input('due_date');

                        $data->owner_id   = $user->id;
                        $saved = $data->save();

                        return response()->json(['data' => [ 'success' => true, 'goal' => $data]], 200);
                  }
             }else {
                return response()->json(['data' => [ 'error' => false, 'message' => 'Unauthorize Access']], 401);
             }
         }else{
             return response()->json(['data' => [ 'error' => false, 'message' => 'Not Found']], 404);
         }
    } 

    public function destroy(Goal $goal, ViewPolicy $viewpolicy, $goal_id) {
         Auth::user();
         $check_id = Goal::where('id', $goal_id)->exists();

         if ($check_id) {
             $fact = $viewpolicy->userPassage($goal_id);

             if ($fact) {
                $data = $goal->findOrfail($goal_id);

                $data->delete();

                return response()->json(['data' => [ 'success' => true, 'message' => 'deleted' ]], 200);
             }else {
                return response()->json(['data' => [ 'error' => false, 'message' => 'Unauthorize Access' ]], 401);
             }
         }else{
             return response()->json(['data' => [ 'error' => false, 'message' => 'Not Found']], 404);
         }
    }

}
