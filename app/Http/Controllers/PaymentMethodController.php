<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{

    /**
     * Mostrar listado de tipos de pago
     */
    public function index()
    {
        $paymentMethods = PaymentMethod::orderBy('id')->get();

        return view('payment_methods.index', compact('paymentMethods'));
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        return view('payment_methods.create');
    }

    /**
     * Guardar nuevo tipo de pago
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191|unique:payment_methods,name',
            'slug' => 'nullable|string|max:191|unique:payment_methods,slug',
        ]);

        PaymentMethod::create($data);

        return redirect()->route('payment_methods.index')->with('success', 'Tipo de pago creado correctamente.');
    }

    /**
     * Formulario de edición
     */
    public function edit(PaymentMethod $payment_method)
    {
        return view('payment_methods.edit', ['paymentMethod' => $payment_method]);
    }

    /**
     * Actualizar tipo de pago
     */
    public function update(Request $request, PaymentMethod $payment_method)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191|unique:payment_methods,name,' . $payment_method->id,
            'slug' => 'nullable|string|max:191|unique:payment_methods,slug,' . $payment_method->id,
        ]);

        $payment_method->update($data);

        return redirect()->route('payment_methods.index')->with('success', 'Tipo de pago actualizado correctamente.');
    }

    /**
     * Eliminar tipo de pago
     */
    public function destroy(PaymentMethod $payment_method)
    {
        $payment_method->delete();

        return redirect()->route('payment_methods.index')->with('success', 'Tipo de pago eliminado correctamente.');
    }
}
