<?php
namespace APP\controllers;
use APP\core\Cache;
use APP\models\Addp;
use APP\models\Panel;
use APP\core\base\Model;
use RedBeanPHP\R;

class StatController extends AppController {
    public $layaout = 'PANEL';
    public $BreadcrumbsControllerLabel = "Панель управления";
    public $BreadcrumbsControllerUrl = "/panel";

    public $ApiKey = "gC3VzeZgtgrdnseLJT";
    public $SecretKey = "yAahEaLsLZG7qi50bNL4dPXWu8JrvLTWHzSh";


    // ТЕХНИЧЕСКИЕ ПЕРЕМЕННЫЕ
    public function indexAction()
    {

        $this->layaout = false;

        date_default_timezone_set('UTC');
        // Браузерная часть
        $Panel =  new Panel();
        $META = [
            'title' => 'Панель BURAN',
            'description' => 'Панель BURAN',
            'keywords' => 'Панель BURAN',
        ];
        $BREADCRUMBS['HOME'] = ['Label' => $this->BreadcrumbsControllerLabel, 'Url' => $this->BreadcrumbsControllerUrl];
        $BREADCRUMBS['DATA'][] = ['Label' => "FAQ"];
        \APP\core\base\View::setBreadcrumbs($BREADCRUMBS);
        $ASSETS[] = ["js" => "/global_assets/js/plugins/tables/datatables/datatables.min.js"];
        $ASSETS[] = ["js" => "/assets/js/datatables_basic.js"];
        \APP\core\base\View::setAssets($ASSETS);
        \APP\core\base\View::setMeta($META);
        \APP\core\base\View::setBreadcrumbs($BREADCRUMBS);
        // Браузерная часть


        //  show(\ccxt\Exchange::$exchanges); // print a list of all available exchange classes

        //Запуск CCXT
        $this->EXCHANGECCXT = new \ccxt\bybit (array(
            'apiKey' => $this->ApiKey,
            'secret' => $this->SecretKey,
            'timeout' => 30000,
            'enableRateLimit' => true,
            'marketType' => "linear",
            'options' => array(
                // 'code'=> 'USDT',
                //  'marketType' => "linear"
            )
        ));


        $this->BALANCE = $this->GetBal()['USDT'];

        $this->BALANCE['total'] = round($this->BALANCE['total'], 2);

        $Balyesterday = 420.57;

        echo "ОТКРЫТИЕ ДНЯ:".$Balyesterday."<br>";

        echo "ТЕКУЩИЙ БАЛАНС:".$this->BALANCE['total']."<br>";
        echo "<hr>";

        $deltatodat = changemet($Balyesterday, $this->BALANCE['total']);

        echo "<b>ПРОФИТ СЕГОДНЯ </b>".$deltatodat." %<br>";


        $DAYTABLE = $this->GetBalTable($this->BALANCE['total']);

       // show($DAYTABLE);



        // Получение ТРЕКОВ

        // Получение статистики по трекам (трекхистори)

        // Чтение таблицы с историей баланса


//        $this->set(compact(''));

    }


    public function GetBal(){
        $balance = $this->EXCHANGECCXT->fetch_balance();
        return $balance;
    }


    private function GetTreksBD($side)
    {
        $terk = R::findAll($this->namebdex, 'WHERE emailex =? AND workside=?', [$this->emailex, $side]);
        return $terk;
    }


    private function GetBalTable($balance)
    {
        $table = R::findOne("balancehistory");


        // Если таблица пустая, то создаем таблицу
        if (empty($table)){

            $ARR['date']  = date("d-m-Y");
            $ARR['balance'] = $balance;
            $this->AddARRinBD($ARR, "balancehistory");
            $table = R::findOne("balancehistory");
        }

        // Если дата сегодняшняя, то обновляем
        if ($table['date'] == date("d-m-Y")){
            echo "Обновляем цену на текущую дату<br>";
            $ARRTREK['balance'] = $balance;
            $this->ChangeARRinBD($ARRTREK, $table['id'], "balancehistory");

        }

        // Если текущая дата не равна последней в БД
        if ($table['date'] != date("d-m-Y")){
            echo "Добавляем новую запись на текущий день<br>";
            $ARR['date']  = date("d-m-Y");
            $ARR['balance'] = $balance;
            $this->AddARRinBD($ARR, "balancehistory");
            $table = R::findOne("balancehistory");

        }



        $table['balance'] = $balance;



        return $table;
    }


    private function AddARRinBD($ARR, $BD = false)
    {

        if ($BD == false) $BD = $this->namebdex;

        $tbl = R::dispense($BD);
        //ДОБАВЛЯЕМ В ТАБЛИЦУ

        foreach ($ARR as $name => $value) {
            $tbl->$name = $value;
        }

        $id = R::store($tbl);

        echo "<font color='green'><b>ДОБАВИЛИ ЗАПИСЬ В БД!</b></font><br>";

        return $id;


    }

    private function ChangeARRinBD($ARR, $id, $BD = false)
    {

        if ($BD == false) $BD = $this->namebdex;

        echo('-----------------');
        echo('-----------------');
        echo('-----------------');
        show($ARR);
        echo('-----------------');
        echo('-----------------');
        echo('-----------------');

        echo "<hr>";

        $tbl = R::load($BD, $id);
        foreach ($ARR as $name => $value) {
            $tbl->$name = $value;
        }
        R::store($tbl);

        return true;


    }




}
?>