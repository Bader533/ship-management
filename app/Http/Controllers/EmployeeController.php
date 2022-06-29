<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EmployeeController extends Controller
{

    public function __construct()
    {
        // $this->authorizeResource(Employee::class, 'employee');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('Read-Employees')) {
            abort(403);
        } else {
            $employee = Employee::all();
            return view('Dashboard.admin.employee.index', ['employee' => $employee]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('Create-Employee')) {
            abort(403);
        } else {
            $roles = Role::where('guard_name', '=', 'employee')->get();
            return view('Dashboard.admin.employee.create', ['roles' => $roles]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Gate::allows('Create-Employee')) {
            abort(403);
        } else {
            $validator = Validator($request->all(), [
                'name' => 'required | max:50',
                'email' => 'required|string | min:2 |max:20',
                'phone' => 'required |numeric|digits:11',
                'password' => 'required',
                'password_confirmation' => 'required',
                'role_id' => 'required|numeric|exists:roles,id',


            ]);
            if (!$validator->fails()) {
                $employee = new Employee();
                $employee->name = $request->input('name');
                $employee->email = $request->input('email') . '@shipexeg.com';
                $employee->phone = $request->input('phone');
                $employee->password = Hash::make($request->input('password'));
                $isSaved = $employee->save();
                if ($isSaved) {
                    $employee->syncRoles(Role::findById($request->input('role_id'), 'employee'));
                }
                return response()->json(
                    [
                        'message' => $isSaved ? 'employee created successfully' : 'Create failed!'
                    ],
                    $isSaved ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST,
                );
            } else {
                return response()->json(['message' => $validator->getMessageBag()->first()], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show(Employee $employee)
    {
        if (!Gate::allows('Read-Shippments')) {
            abort(403);
        } else {
            $user = User::all();
            return view('Dashboard.admin.search', ['user' => $user]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function edit(Employee $employee)
    {
        if (!Gate::allows('Update-Employee', $employee)) {
            abort(403);
        } else {
            $roles = Role::where('guard_name', '=', 'employee')->get();
            return view('Dashboard.admin.employee.edit', ['employee' => $employee, 'roles' => $roles]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Employee $employee)
    {
        if (!Gate::allows('Update-Employee', $employee)) {
            abort(403);
        } else {
            $validator = Validator($request->all(), [
                'name' => ' max:50',
                'email' => 'string',
                'phone' => 'numeric|digits:11',
                'role_id' => 'required|numeric|exists:roles,id',


            ]);

            if (!$validator->fails()) {


                $employee->name = $request->input('name');
                $employee->email = $request->input('email') . '@shipexeg.com';
                $employee->phone = $request->input('phone');
                $employee->password = Hash::make($request->input('password'));
                $isSaved = $employee->save();
                if ($isSaved) {
                    $employee->syncRoles(Role::findById($request->input('role_id'), 'employee'));
                }

                return response()->json(
                    [
                        'message' => $isSaved ? 'employee updated successfully' : 'Create failed!'
                    ],
                    $isSaved ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST,
                );
            } else {
                return response()->json(['message' => $validator->getMessageBag()->first()], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee)
    {
        if (!Gate::allows('Delete-Employee', $employee)) {
            abort(403);
        } else {

            $isDeleted = $employee->delete();
            return response()->json(
                ['message' => $isDeleted ? 'Deleted successfully' : 'Delete failed!'],
                $isDeleted ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST
            );
        }
    }
}
