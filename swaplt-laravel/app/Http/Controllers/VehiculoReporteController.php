<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class VehiculoReporteController extends Controller
{
    public function generarReporte($id)
    {
        // Buscar el vehículo por ID
        $vehiculo = Vehiculo::find($id);

        // Verificar si el vehículo existe
        if (!$vehiculo) {
            return response()->json(['message' => 'Vehículo no encontrado'], 404);
        }

        // Crear el HTML para el reporte del vehículo
        $html = '
        <html>
        <head>
            <style>
                body {
                    font-family: "Arial", sans-serif;
                    background-color: #ffffff;
                    margin: 0;
                    padding: 0;
                    color: #333;
                    position: relative;
                }
                .watermark {
                    position: absolute;
                    opacity: 0.1;
                    font-size: 120px;
                    color: #0056b3;
                    transform: rotate(-45deg);
                    left: 25%;
                    top: 40%;
                    z-index: -1;
                    font-weight: bold;
                }
                .container {
                    width: 80%;
                    margin: 0 auto;
                    padding: 40px;
                    background-color: #ffffff;
                    border-radius: 10px;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                    text-align: center;
                    position: relative;
                    border: 1px solid #e0e0e0;
                }
                .header {
                    margin-bottom: 30px;
                    border-bottom: 2px solid #0056b3;
                    padding-bottom: 20px;
                }
                .header h1 {
                    color: #0056b3;
                    font-size: 36px;
                    margin: 20px 0 5px 0;
                    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
                }
                .header p {
                    color: #666;
                    font-size: 14px;
                }
                .logo {
                    height: 80px;
                    margin-bottom: 10px;
                }
                table {
                    width: 100%;
                    margin: 20px 0;
                    border-collapse: collapse;
                    text-align: left;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                }
                th, td {
                    padding: 12px;
                    font-size: 16px;
                    border-bottom: 1px solid #ddd;
                }
                th {
                    background-color: #0056b3;
                    color: white;
                    font-weight: bold;
                    text-transform: uppercase;
                    font-size: 14px;
                }
                td {
                    background-color: #f9f9f9;
                }
                tr:nth-child(even) td {
                    background-color: #f1f1f1;
                }
                tr:hover td {
                    background-color: #e6f7ff;
                }
                .footer {
                    text-align: center;
                    margin-top: 30px;
                    font-size: 12px;
                    color: #888;
                    border-top: 1px solid #0056b3;
                    padding-top: 20px;
                }
                .footer small {
                    color: #aaa;
                }
                .content h3 {
                    color: #0056b3;
                    font-size: 22px;
                    margin-bottom: 20px;
                    text-align: center;
                    border-bottom: 1px solid #e0e0e0;
                    padding-bottom: 10px;
                }
                .content {
                    text-align: justify;
                    text-justify: inter-word;
                    margin-bottom: 20px;
                }
                .badge {
                    display: inline-block;
                    padding: 3px 8px;
                    background-color: #0056b3;
                    color: white;
                    border-radius: 4px;
                    font-size: 12px;
                    font-weight: bold;
                    margin-left: 10px;
                }
            </style>
        </head>
        <body>
            <div class="watermark">SWAPLT</div>

            <div class="container">
                <div class="header">
                    <img class="logo" src="' . public_path('images/logo.png') . '" alt="SWAPLT">
                    <h1>Informe de Vehículo <span class="badge">CONFIDENCIAL</span></h1>
                    <p>Reporte detallado de las especificaciones del vehículo</p>
                </div>

                <div class="content">
                    <h3>Detalles del Vehículo</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Campo</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Vehiculo Robado</td><td>' . $vehiculo->vehiculo_robado . '</td></tr>
                            <tr><td>Vehiculo Libre de Accidentes</td><td>' . $vehiculo->vehiculo_libre_accidentes . '</td></tr>
                            <tr><td>Marca</td><td>' . $vehiculo->marca . '</td></tr>
                            <tr><td>Modelo</td><td>' . $vehiculo->modelo . '</td></tr>
                            <tr><td>Año</td><td>' . $vehiculo->anio . '</td></tr>
                            <tr><td>Precio</td><td><strong>' . number_format($vehiculo->precio, 2) . ' €</strong></td></tr>
                            <tr><td>Estado</td><td>' . $vehiculo->estado . '</td></tr>
                            <tr><td>Transmisión</td><td>' . $vehiculo->transmision . '</td></tr>
                            <tr><td>Tipo de Combustible</td><td>' . $vehiculo->tipo_combustible . '</td></tr>
                            <tr><td>Kilometraje</td><td>' . number_format($vehiculo->kilometraje, 0) . ' km</td></tr>
                            <tr><td>Fuerza</td><td>' . $vehiculo->fuerza . ' HP</td></tr>
                            <tr><td>Capacidad del Motor</td><td>' . $vehiculo->capacidad_motor . ' cc</td></tr>
                            <tr><td>Color</td><td>' . $vehiculo->color . '</td></tr>
                            <tr><td>Ubicación</td><td>' . $vehiculo->ubicacion . '</td></tr>
                            <tr><td>Matrícula</td><td><strong>' . $vehiculo->matricula . '</strong></td></tr>
                            <tr><td>Descripción</td><td>' . $vehiculo->descripcion . '</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="footer">
                    <p>Reporte generado el ' . date('d-m-Y H:i:s') . ' | ID: VH-' . str_pad($vehiculo->id, 6, '0', STR_PAD_LEFT) . '</p>
                    <small>© ' . date('Y') . ' SWAPLT. Todos los derechos reservados. Este documento es confidencial y para uso exclusivo del destinatario.</small>
                </div>
            </div>
        </body>
        </html>';

        // Generar el PDF
        $pdf = Pdf::loadHTML($html);

        // Devolver el PDF como respuesta de descarga
        return $pdf->download('reporte_vehiculo_' . $vehiculo->id . '.pdf');
    }
}