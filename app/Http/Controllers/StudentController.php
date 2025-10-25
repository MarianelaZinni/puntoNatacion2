<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
//use App\Models\Pago;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::all();
        return view('students.index', compact('students'));
    }

    public function create()
    {
        return view('students.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'dni' => 'required|unique:students,dni',
            'name' => 'required',
            'mail' => 'required|email|unique:students,mail',
            'address' => 'nullable',
            'phone' => 'nullable',
        ]);
        Student::create($request->only('dni', 'name', 'mail', 'address', 'phone'));
        return redirect()->route('students.index')->with('success', 'Alumno creado correctamente.');
    }

    public function show(Student $student)
    {
        return view('students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'dni' => 'required|unique:students,dni,' . $student->id,
            'name' => 'required',
            'mail' => 'required|email|unique:students,mail,' . $student->id,
            'address' => 'nullable',
            'phone' => 'nullable',
        ]);
        $student->update($request->only('dni', 'name', 'mail', 'address', 'phone'));
        return redirect()->route('students.index')->with('success', 'Alumno actualizado correctamente.');
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->route('students.index')->with('success', 'Alumno eliminado correctamente.');
    }

    public function enrollClassForm(Student $student)
    {
        $subjects = Subject::all();
        return view('students.enroll-class', compact('student', 'subjects'));
    }

    public function enrollClass(Request $request, Student $student)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);
        $student->subjects()->attach($request->subject_id);
        return redirect()->route('students.index')->with('success', 'Alumno anotado en clase correctamente.');
    }

    /*public function registerPaymentForm(Student $student)
    {
        return view('students.register-payment', compact('student'));
    }*/

    /*public function registerPayment(Request $request, Student $student)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
        ]);
        Payment::create([
            'student_id' => $student->id,
            'amount' => $request->amount,
            'date' => $request->date,
        ]);
        return redirect()->route('students.index')->with('success', 'Pago registrado correctamente.');
    }*/
}