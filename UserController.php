<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Log;

use Illuminate\Http\Request;
use Orchid\Screen\Layout;
use Orchid\Screen\Repository;
use App\Orchid\Blueprints\PanelUser;
use App\Http\Requests\Platform\User\CreateUserRequest;
use App\Http\Requests\Platform\User\UpdateUserRequest;
use Illuminate\Support\Facades\Auth;
use App\Events\UserEdited;
use App\Events\UserCreated;
use App\Events\UserDeleted;

class UserController extends Controller {
    
    /**
     * Display the users' list.
     *
     * @return \Illuminate\Http\Response
     */
    public function list() {
        $users = User::orderBy('name')->paginate(10);
        $repository = new Repository(compact('users'));
        
        $layout = Layout::blank([
            Layout::view('vendor.platform.users.list')
        ]);
        
        return $layout->build($repository);
    }

    public function add() {
        $blueprint = PanelUser::class;
        $user = null;
        $repository = new Repository(compact('user', 'blueprint'));
        
        
        $layout = Layout::blank([
            Layout::view('vendor.platform.panelUser')
        ]);
        
        return $layout->build($repository);
    }

    /**
     * Create a new user
     * @param  CreateUserRequest $request
     * @return redirect
     */
    public function create(CreateUserRequest $request) {
        $user = $request->commit();

        //Trigger user edited event
        event(new UserCreated($user));

        //Reloads page
        return redirect()->action('Platform\UserController@edit', $user)->with('alert', [
            'type' => 'success',
            'message' => 'User created'
        ]);
    }


    public function edit(User $user = null) {
        $blueprint = PanelUser::class;

        //Loads logs if user has permission to see them.
        if(Auth::user()->hasAccess('platform.widgets.log')) { 
            $logs = Log::with(['user'])->where("model", "User")->where("model_id", $user->id)->latest()->take(10)->get()->map(function ($log) {
                $log->date = $log->updated_at->format("d/m/Y H:i");
                return $log;
            });
        }

        $repository = new Repository(compact('user', 'blueprint', 'logs'));
        
        $layout = Layout::blank([
            Layout::view('vendor.platform.panelUser')
        ]);
        
        return $layout->build($repository);
    }

    /**
     * Update an existing user
     * @param  UpdateUserRequest $request
     * @return redirect
     */
    public function save(UpdateUserRequest $request, User $user) {
        $original = ["user" => $user->getOriginal(), "role" => $user->role];
        $request->commit($user);

        //Trigger user edited event
        event(new UserEdited($user, $original));

        //Reloads page
        return redirect()->action('Platform\UserController@edit', $user)->with('alert', [
            'type' => 'success',
            'message' => 'User saved'
        ]);
    }

    /**
     * Delete a panel user
     * @param  User   $user 
     * @return \Illuminate\Http\Response
     */
    public function delete(User $user) {
        //Checks permission first
        if(!Auth::user()->hasAccess('platform.users.delete')) {
            return redirect()->action('Platform\UserController@list')->with('alert', [
                'type' => "error",
                'message' => "You don't have permission to delete users."
            ]);
        }

        //Deletes user
        $user->delete();

        //Trigger user edited event
        event(new UserDeleted($user->toArray()));

        //Returns to users' list
        return redirect()->action('Platform\UserController@list')->with('alert', [
            'type' => 'success',
            'message' => 'User has been deleted'
        ]);;
    }
    
    
}