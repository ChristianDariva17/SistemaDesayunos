<?php
namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Empleado;
use App\Models\Producto;
use App\Models\User;
use App\Models\Cliente;    
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with(['cliente', 'empleado'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('pedidos.index', compact('pedidos'));
    }

    public function create()
    {
        $productos = Producto::all();
        $clientes = Cliente::all();
        $empleados = Empleado::where('role', 'mesero')->get();
        
        return view('pedidos.create', compact('productos', 'clientes', 'empleados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:users,id',
            'empleado_id' => 'required|exists:users,id',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|numeric|min:1',
        ]);

        DB::transaction(function () use ($request) {
            // Crear el pedido
            $pedido = Pedido::create([
                'cliente_id' => $request->cliente_id,
                'empleado_id' => $request->empleado_id,
                'fecha' => now()->toDateString(),
                'hora' => now()->toTimeString(),
                'estado' => 'pendiente',
                'subtotal' => 0,
                'impuesto' => 0,
                'total' => 0,
            ]);

            $subtotal = 0;

            // Crear los detalles del pedido
            foreach ($request->productos as $productoData) {
                $producto = Producto::find($productoData['id']);
                
                $detalle = DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $productoData['cantidad'],
                    'precio_unitario' => $producto->precio,
                    'subtotal' => $productoData['cantidad'] * $producto->precio
                ]);

                $subtotal += $detalle->subtotal;
            }

            // Actualizar totales del pedido
            $impuesto = $subtotal * 0.16; // 16% de impuesto
            $total = $subtotal + $impuesto;

            $pedido->update([
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'total' => $total
            ]);
        });

        return redirect()->route('pedidos.index')->with('success', 'Pedido creado exitosamente');
    }

    public function show(Pedido $pedido)
    {
        $pedido->load(['cliente', 'empleado', 'detalles.producto']);
        return view('pedidos.show', compact('pedido'));
    }

    public function edit(Pedido $pedido)
    {
        $productos = Producto::all();
        $clientes = User::where('role', 'cliente')->get();
        $empleados = User::where('role', 'empleado')->get();
        $pedido->load('detalles.producto');
        
        return view('pedidos.edit', compact('pedido', 'productos', 'clientes', 'empleados'));
    }

    public function update(Request $request, Pedido $pedido)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,procesando,completado,cancelado',
            'observaciones' => 'nullable|string|max:500'
        ]);

        $pedido->update([
            'estado' => $request->estado,
            'observaciones' => $request->observaciones
        ]);

        return redirect()->route('pedidos.show', $pedido)->with('success', 'Pedido actualizado exitosamente');
    }

    public function destroy(Pedido $pedido)
    {
        $pedido->delete();
        return redirect()->route('pedidos.index')->with('success', 'Pedido eliminado exitosamente');
    }
}
