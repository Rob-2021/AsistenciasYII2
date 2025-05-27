<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use common\models\AsistenciaAdministrativo;
use yii\filters\VerbFilter;

class AsistenciaAdministrativoController extends Controller
{
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $query = AsistenciaAdministrativo::find()->joinWith('persona')->orderBy(['IdPersona' => SORT_DESC]);

        $busqueda = $request->get('busqueda');
        if ($busqueda) {
            $palabras = preg_split('/\s+/', trim($busqueda));
            foreach ($palabras as $palabra) {
                $query->andWhere([
                    'or',
                    ['like', 'Nombres', $palabra],
                    ['like', 'Paterno', $palabra],
                    ['like', 'Materno', $palabra],
                ]);
            }
        }
        if ($dia = $request->get('dia')) {
            $query->andWhere(["=", "CONVERT(date, HoraEntrada)", $dia]);
        }
        if ($mes = $request->get('mes')) {
            list($anio, $mesNum) = explode('-', $mes);
            $query->andWhere(["=", "DATEPART(year, HoraEntrada)", $anio]);
            $query->andWhere(["=", "DATEPART(month, HoraEntrada)", ltrim($mesNum, '0')]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => null,
        ]);
    }

    public function actionReporte()
    {
        // Aquí puedes implementar la lógica de reporte/exportación
        return $this->render('reporte');
    }

    public function actionExportar($tipo = 'pdf')
    {
        $request = Yii::$app->request;
        $query = AsistenciaAdministrativo::find()->joinWith('persona')->orderBy(['IdPersona' => SORT_DESC]);
        $busqueda = $request->get('busqueda');
        if ($busqueda) {
            $palabras = preg_split('/\s+/', trim($busqueda));
            foreach ($palabras as $palabra) {
                $query->andWhere([
                    'or',
                    ['like', 'Nombres', $palabra],
                    ['like', 'Paterno', $palabra],
                    ['like', 'Materno', $palabra],
                ]);
            }
        }
        if ($dia = $request->get('dia')) {
            $query->andWhere(["=", "CONVERT(date, HoraEntrada)", $dia]);
        }
        if ($mes = $request->get('mes')) {
            list($anio, $mesNum) = explode('-', $mes);
            $query->andWhere(["=", "DATEPART(year, HoraEntrada)", $anio]);
            $query->andWhere(["=", "DATEPART(month, HoraEntrada)", ltrim($mesNum, '0')]);
        }
        $asistencias = $query->all();

        $content = $this->renderPartial('reporte', [
            'asistencias' => $asistencias,
        ]);

        if ($tipo === 'pdf') {
            // Requiere instalar kartik-v/yii2-mpdf o yiisoft/yii2-mpdf
            $pdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']);
            $pdf->WriteHTML($content);
            return Yii::$app->response->sendContentAsFile($pdf->Output('', 'S'), 'reporte_asistencias.pdf', [
                'mimeType' => 'application/pdf',
                'inline' => true,
            ]);
        } elseif ($tipo === 'excel') {
            // Usar el generador de Excel personalizado
            $spreadsheet = \backend\models\ReporteExcel::generar($asistencias);
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            ob_start();
            $writer->save('php://output');
            $excelOutput = ob_get_clean();
            return Yii::$app->response->sendContentAsFile($excelOutput, 'reporte_asistencias.xlsx', [
                'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'inline' => false,
            ]);
        }
        throw new \yii\web\NotFoundHttpException('Tipo de exportación no soportado.');
    }

    public function actionAdmAtrasos()
    {
        $model = new \backend\models\AdmAtrasosForm();
        $data = [];
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $mes = $model->mes;
            list($anio, $mesNum) = explode('-', $mes);
            $query = AsistenciaAdministrativo::find()->joinWith('persona')
                ->where(["=", "DATEPART(year, HoraEntrada)", $anio])
                ->andWhere(["=", "DATEPART(month, HoraEntrada)", ltrim($mesNum, '0')]);
            $asistencias = $query->all();
            $acumulados = [];
            foreach ($asistencias as $asistencia) {
                $personaId = $asistencia->IdPersona;
                $clave = $personaId . '-' . $mes;
                if (!isset($acumulados[$clave])) {
                    $acumulados[$clave] = [
                        'persona' => $asistencia->persona,
                        'minutos' => 0,
                        'atrasos' => 0,
                        'IdPersona' => $personaId,
                    ];
                }
                if ($asistencia->HoraEntrada && $asistencia->HoraRegistroEntrada) {
                    $entrada  = strtotime($asistencia->HoraEntrada);
                    $registro = strtotime($asistencia->HoraRegistroEntrada);
                    if ($registro > $entrada) {
                        $segundosRetraso = $registro - $entrada;
                        $minutosRetraso = round($segundosRetraso / 60, 2);
                        $acumulados[$clave]['minutos'] += $minutosRetraso;
                        if ($segundosRetraso > 300) {
                            $acumulados[$clave]['atrasos']++;
                        }
                    }
                }
            }
            // Filtrar solo administrativos con 5 o más atrasos
            $acumulados = array_filter($acumulados, function($item) {
                return $item['atrasos'] >= 5;
            });
            // Ordenar por atrasos y minutos descendente
            usort($acumulados, function($a, $b) {
                if ($a['atrasos'] == $b['atrasos']) {
                    return $b['minutos'] <=> $a['minutos'];
                }
                return $b['atrasos'] <=> $a['atrasos'];
            });
            $data = $acumulados;
        }
        return $this->render('adm_atrasos', [
            'model' => $model,
            'data' => $data,
        ]);
    }
}
