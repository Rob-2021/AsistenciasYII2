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
            $query->andWhere(['AsistenciaAdministrativos.IdPersona' => $busqueda]);
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

        if (!$busqueda && $request->get('id')) {
            $busqueda = $request->get('id');
        }
        if ($busqueda) {
            $query->andWhere(['AsistenciaAdministrativos.IdPersona' => $busqueda]);
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

        // Generar nombre de archivo personalizado
        $nombreArchivo = 'reporte_asistencias';
        if (!empty($asistencias) && $asistencias[0]->persona) {
            $persona = $asistencias[0]->persona;
            $nombres = strtolower(trim($persona->Nombres ?? ''));
            $apellido = strtolower(trim($persona->Paterno ?? ''));
            
            // Obtener mes y año del filtro
            $mesTexto = '';
            $anioTexto = '';
            if ($mes = $request->get('mes')) {
                list($anioTexto, $mesNum) = explode('-', $mes);
                $meses = [
                    '01' => 'enero', '02' => 'febrero', '03' => 'marzo', '04' => 'abril',
                    '05' => 'mayo', '06' => 'junio', '07' => 'julio', '08' => 'agosto',
                    '09' => 'septiembre', '10' => 'octubre', '11' => 'noviembre', '12' => 'diciembre'
                ];
                $mesTexto = $meses[$mesNum] ?? '';
            }
            
            // Construir nombre: nombres-apellido-mes-año
            $partes = array_filter([$nombres, $apellido, $mesTexto, $anioTexto]);
            if (!empty($partes)) {
                $nombreArchivo = implode('-', $partes);
                // Limpiar caracteres especiales
                $nombreArchivo = preg_replace('/[^a-z0-9\-]/', '', $nombreArchivo);
            }
        }

        if ($tipo === 'pdf') {
            // Requiere instalar kartik-v/yii2-mpdf o yiisoft/yii2-mpdf
            $pdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']);
            $pdf->WriteHTML($content);
            return Yii::$app->response->sendContentAsFile($pdf->Output('', 'S'), $nombreArchivo . '.pdf', [
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
            return Yii::$app->response->sendContentAsFile($excelOutput, $nombreArchivo . '.xlsx', [
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
            $atrasosAcumuladosPorClave = [];
            foreach ($asistencias as $asistencia) {
                $personaId = $asistencia->IdPersona;
                $mesClave = $mes; // mes en formato YYYY-MM
                $clave = $personaId . '-' . $mesClave;
                if (!isset($acumulados[$clave])) {
                    $acumulados[$clave] = [
                        'persona' => $asistencia->persona,
                        'minutos' => 0,
                        'atrasos' => 0,
                        'IdPersona' => $personaId,
                    ];
                }
                if (!isset($atrasosAcumuladosPorClave[$clave])) $atrasosAcumuladosPorClave[$clave] = 0;
                if ($asistencia->HoraEntrada && $asistencia->HoraRegistroEntrada) {
                    $entrada  = strtotime($asistencia->HoraEntrada);
                    $registro = strtotime($asistencia->HoraRegistroEntrada);
                    if ($registro > $entrada) {
                        $segundosRetraso = $registro - $entrada;
                        $minutosRetraso = round($segundosRetraso / 60, 2);
                        $acumulados[$clave]['minutos'] += $minutosRetraso;
                    }
                }
                // Acumulado progresivo de atrasos (EstadoEntrada == 'AT')
                if (($asistencia->EstadoEntrada ?? '') === 'AT') {
                    $atrasosAcumuladosPorClave[$clave]++;
                }
                $acumulados[$clave]['atrasos'] = $atrasosAcumuladosPorClave[$clave];
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

    public function actionAsistenciaAdmcomun()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        $mes = $request->get('mes');
        $dia = $request->get('dia');
        $asistencias = [];
        if ($id && ($mes || $dia)) {
            $query = AsistenciaAdministrativo::find()->joinWith('persona')->where(['AsistenciaAdministrativos.IdPersona' => $id]);
            if ($dia) {
                $query->andWhere(["=", "CONVERT(date, HoraEntrada)", $dia]);
            }
            if ($mes) {
                list($anio, $mesNum) = explode('-', $mes);
                $query->andWhere(["=", "DATEPART(year, HoraEntrada)", $anio]);
                $query->andWhere(["=", "DATEPART(month, HoraEntrada)", ltrim($mesNum, '0')]);
            }
            $asistencias = $query->all();
        }
        return $this->render('asistenciaADMcomun', [
            'asistencias' => $asistencias,
        ]);
    }

    public function actionPlanillas()
    {
        $model = new \backend\models\AdmAtrasosForm();
        $data = [];
        $mes = null;
        $page = Yii::$app->request->get('page', 1);
        $pageSize = 20;
        $total = 0;
        if ($model->load(Yii::$app->request->get()) && $model->validate() && $model->mes) {
            $mes = $model->mes;
            list($anio, $mesNum) = explode('-', $mes);
            $query = \common\models\AsistenciaAdministrativo::find()
                ->select(['IdPersona'])
                ->where(["=", "DATEPART(year, HoraEntrada)", $anio])
                ->andWhere(["=", "DATEPART(month, HoraEntrada)", ltrim($mesNum, '0')])
                ->groupBy('IdPersona');
            $total = $query->count();
            $pagination = new \yii\data\Pagination([
                'totalCount' => $total,
                'pageSize' => $pageSize,
                'page' => $page - 1,
            ]);
            $ids = $query
                ->offset($pagination->offset)
                ->limit($pagination->limit)
                ->column();
            $data = [];
            foreach ($ids as $id) {
                $persona = \common\models\Persona::findOne($id);
                $data[] = [
                    'IdPersona' => $id,
                    'persona' => $persona,
                ];
            }
        } else {
            $pagination = new \yii\data\Pagination([
                'totalCount' => 0,
                'pageSize' => $pageSize,
                'page' => $page - 1,
            ]);
        }
        return $this->render('planillas', [
            'model' => $model,
            'data' => $data,
            'pagination' => $pagination,
            'mes' => $mes,
        ]);
    }
}
