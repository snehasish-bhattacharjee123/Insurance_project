<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class AccountController extends Controller
{
    //
    public function registration()
    {
        return view('front.account.registration');
    }

    public function processRegistration(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required' //|min:5|same:confirm_password
        ]);

        if($validator->passes() )
        {
            $user =  new User();        
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->pass_view = $request->password;
            $user->save(); 
            return redirect()->route('account.login')->with('success','you have registered');
        }
        else
        {   
            return redirect()->route('account.register')
            ->withInput()
            ->withErrors($validator);
           
        } 
        
        

        
        

    }

    public function login()
    { 
        return view('front.account.login');
    }

    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required' //|min:5|same:confirm_password
        ]);
    
        if ($validator->passes()) {
            $credentials = $request->only('email', 'password');
    
            if (Auth::attempt($credentials)) {
                $user = Auth::user(); 
                // dd($user);
                if ($user->user_type == '1') {
                    return redirect()->route('account.authUser')->with('message', 'You are logged in');
                } else {
                    // Auth::logout(); // Logout the user if the user type is not 1
                    return redirect()->route('account.login')->withErrors(['message' => 'Unauthorized access']);
                }
            } else {
                // Authentication failed
                return redirect()->route('account.login')->withErrors(['message' => 'Invalid credentials']);
            }
        } else {
            return redirect()->route('account.login')
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }
    }
    

    // this is profile Page
    public function authUser(){

        $id = Auth::user()->id;

        // $user = User::where('id','$id')->first();
        $user = User::find($id);
        // dd($user);

        return view('front.afterLogin.authUser',[
        'user' => $user
        ]);
    }

    public function updateProfile(Request $request)

    {
        $id = Auth::user()->id;
        // dd($id);

        $validator = Validator::make($request->all(),[

            'name' => 'required|min:5|max:20',
            'email' => 'required|email' //unique:users,email,'.$id.',id'
        ]);
        // dd($request->all());
        if($validator->passes())
        {
            
            $user =  User::find($id);        
            $user->name = $request->name; 
            
            if($request->has('email')){ 
                $user->email = $request->email;
            }

            $user->designation = $request->designation;
            $user->mobile = $request->mobile; 
            
            if($request->password){ 
                $user->password = Hash::make($request->password);
                $user->pass_view = $request->password;
            }

            $user->save(); 
            // dd($user);
            
            session()->flash('success','profile updated successfully');

            return response()->json([
                'status' => true,
                'errors' => []
            ]);
            
        }
        else
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

    }

    public function updateProfilePic(Request $request)
    {
        // dd($request->all());

        $id = Auth::user()->id;
        // dd($id);

        $validator = Validator::make($request->all(),[
            'image' => 'required|image',
        ]);
        if($validator->passes())
        {
            $user =  User::find($id);        
            
            $images = $request->image;
            // dd($image);
            if($images)
            { 
                $image = time().'.'.$request->image->extension(); 
                $imagePath = $request->image->move(public_path('images'),$image);
                // dd($image);
                $user->image = $image;
            } 
            $user-> save();
        }
        else
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }


    public function createJob()
    {
        $catagories = Category::orderBy('name','ASC')->where('status',1)->get();

        $job_types = JobType::orderBy('name','ASC')->where('status',1)->get();

        return view('front.account.job.create',[
            'categories' => $catagories,
            'job_types' => $job_types
        ]);
    }

    public function saveJobs(Request $request)
    {
        $rules = [
            'title' => 'required',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required',
            'description' => 'required',
            'company_name' => 'required'
        ];

        $validator = Validator::make($request->all(),$rules);

        if($validator->passes())
        {
            $job = new Job();

            // $user->name = $request->name;
            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->jobType_id = $request->jobType;
            $job->user_id = Auth::user()->id;
            $job->vacancy = $request->vacancy;
            $job->salary = $request->salary;
            $job->location = $request->location;
            $job->description = $request->description;
            $job->benefits = $request->benefits;
            $job->responsibility = $request->responsibility;
            $job->qualification = $request->qualification;
            $job->keyword = $request->keywords;
            $job->experience = $request->experience;
            $job->company_name = $request->company_name;
            $job->company_location = $request->com_location;
            $job->company_website = $request->website;
            
            $job->save();

            session()->flash('success', 'data added successfully');

            return response()->json([
                'status' => true,
                'errors' => []
            ]);
        }
        else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function myJobs()
    {
        $jobs = job::where('user_id',Auth::user()->id)->paginate(2);

        // dd($jobs);

        // return view('front.account.job.my_job',[
        //     'jobs' => $jobs
        // ]);
        return view('front.account.job.my_job', compact('jobs'));
    }

    public function editJob(Request $request, $id)
    {
        $categories = Category::orderBy('name','ASC')->where('status',1)->get();
        $job_types = JobType::orderBy('name','ASC')->where('status',1)->get();

        $job = Job::where([
            'user_id' => Auth::user()->id,
            'id' => $id
        ])->first();

        if($job == null)
        {
            abort(404);
        }

        return view('front.account.job.edit_job',[
            'categories' => $categories,
            'job_types' => $job_types,
            'job' => $job,
        ]);
    }

    public function updateJob(Request $request, $id)
    {
        $rules = [
            'title' => 'required',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required',
            'description' => 'required',
            'company_name' => 'required'
        ];

        $validator = Validator::make($request->all(),$rules);

        if($validator->passes())
        {
            $job = Job::findorfail($id);

            // $user->name = $request->name;
            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->jobType_id = $request->jobType;
            $job->user_id = Auth::user()->id;
            $job->vacancy = $request->vacancy;
            $job->salary = $request->salary;
            $job->location = $request->location;
            $job->description = $request->description;
            $job->benefits = $request->benefits;
            $job->responsibility = $request->responsibility;
            $job->qualification = $request->qualification;
            $job->keyword = $request->keywords;
            $job->experience = $request->experience;
            $job->company_name = $request->company_name;
            $job->company_location = $request->com_location;
            $job->company_website = $request->website;
            
            $job->save();

            session()->flash('success', 'data updated successfully');

            return response()->json([
                'status' => true,
                'errors' => []
            ]);
        }
        else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function deleteJob($id)
    {
        
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('account.login');  
    }

}
