<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubjectPrice;
use Illuminate\Support\Facades\Validator;

class SubjectPriceController extends Controller
{
    /**
     * Mostrar el formulario con los precios por defecto para con profesor / sin profesor.
     */
    public function index()
    {
        // Cargar defaults (subject_type_id = NULL) para ambas categorías
        $defaults = SubjectPrice::whereNull('subject_type_id')->get()->keyBy(function ($r) {
            return ($r->has_teacher ? 'teacher_' : 'no_teacher_') . $r->times_per_week;
        });

        // Aseguramos que existan filas para 1..5 en ambas categorías (no las creamos en DB, sólo en la vista)
        $teacherPrices = [];
        $noTeacherPrices = [];
        for ($i = 1; $i <= 5; $i++) {
            $teacherPrices[$i] = isset($defaults["teacher_{$i}"]) ? $defaults["teacher_{$i}"]->price : null;
            $noTeacherPrices[$i] = isset($defaults["no_teacher_{$i}"]) ? $defaults["no_teacher_{$i}"]->price : null;
        }

        return view('subject-prices.index', compact('teacherPrices', 'noTeacherPrices'));
    }

    /**
     * Actualizar los precios por defecto (subject_type_id = NULL).
     * Recibe arrays: teacher[1..5], no_teacher[1..5]
     */
    public function update(Request $request)
    {
        $data = $request->all();

        // Validación: los precios pueden ser null o numeric >= 0
        $rules = [];
        for ($i = 1; $i <= 5; $i++) {
            $rules["teacher.{$i}"] = ['nullable', 'numeric', 'min:0'];
            $rules["no_teacher.{$i}"] = ['nullable', 'numeric', 'min:0'];
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return redirect()->route('subject-prices.index')
                ->withErrors($validator)
                ->withInput();
        }

        // Guardamos los valores: updateOrCreate por (subject_type_id=null, has_teacher, times_per_week)
        for ($i = 1; $i <= 5; $i++) {
            $tval = $data['teacher'][$i] ?? null;
            if ($tval !== null && $tval !== '') {
                SubjectPrice::updateOrCreate(
                    ['subject_type_id' => null, 'has_teacher' => true, 'times_per_week' => $i],
                    ['price' => (float)$tval]
                );
            } else {
                // Si dejó vacío, borrar fila existente (opcional). Aquí no borramos, solo ignoramos.
                // Si querés borrar: SubjectPrice::whereNull('subject_type_id')->where('has_teacher', true)->where('times_per_week', $i)->delete();
            }

            $ntval = $data['no_teacher'][$i] ?? null;
            if ($ntval !== null && $ntval !== '') {
                SubjectPrice::updateOrCreate(
                    ['subject_type_id' => null, 'has_teacher' => false, 'times_per_week' => $i],
                    ['price' => (float)$ntval]
                );
            } else {
                // same as above
            }
        }

        return redirect()->route('subject-prices.index')->with('success', 'Precios actualizados correctamente.');
    }
}