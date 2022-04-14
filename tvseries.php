<?php
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

class ExoTvSeries {
    public $capsule;

    private $week_day_names;

    public function __construct($connection = []) {
        $this->capsule = new Capsule;

        $this->week_day_names = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday'
        ];

        $this->capsule->addConnection([
            'driver'    => isset($connection['driver']) ?: 'mysql',
            'host'      => isset($connection['host']) ?: 'mariadb',
            'database'  => isset($connection['database']) ?: 'tvseries',
            'username'  => isset($connection['username']) ?: 'root',
            'password'  => isset($connection['password']) ?: 'root',
            'charset'   => isset($connection['charset']) ?: 'utf8',
            'collation' => isset($connection['collation']) ?: 'utf8_unicode_ci',
            'prefix'    => isset($connection['prefix']) ?: '',
        ]);

        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();

        /*
        CREATE TABLE `tv_series` ( `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT , `title` VARCHAR(255) NOT NULL , `channel` VARCHAR(255) NOT NULL , `gender` VARCHAR(255) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE `tv_series_intervals` (`id_tv_series` bigint(20) UNSIGNED NOT NULL, `week_day` int(11) NOT NULL, `show_time` time NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        ALTER TABLE `tv_series_intervals` ADD CONSTRAINT `fk_tv_series_id` FOREIGN KEY (`id_tv_series`) REFERENCES `tv_series`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

        INSERT INTO `tv_series` (`id`, `title`, `channel`, `gender`) VALUES (1, 'Game of Tomatoes', 'BBC 1', 'Adult / Medieval'), (2, 'Anne of Green Tables', 'BBC 2', 'Adult / Drama');

        INSERT INTO `tv_series_intervals` (`id_tv_series`, `week_day`, `show_time`) VALUES (1, 2, '18:30:00'), (1, 3, '18:30:00'), (2, 6, '13:00:00'), (2, 0, '13:30:00'), (2, 2, '14:00:00'), (1, 3, '15:00:00');
        */
    }

    public function get_next_series($date_time = '', $title = '') {
        header('Content-Type: application/json;charset=utf-8');
        $series = Capsule::table('tv_series_intervals')->join('tv_series', 'tv_series_intervals.id_tv_series', '=', 'tv_series.id')->select('tv_series.title as title', 'tv_series_intervals.week_day as week_day', 'tv_series_intervals.show_time as show_time')->orderBy('tv_series.title', 'asc')
        ->orderBy('tv_series_intervals.week_day', 'asc')
        ->orderBy('tv_series_intervals.show_time', 'asc');
        if (!empty($title)) {
            //search for a specific title
            $series->where('tv_series.title', 'like', "%$title%");
        }

        $next_displays = [];
        if (!empty($date_time)) {
            // add date filter and show next shows on current or after selected date
        } else {
            $current_weekday = date('w');
            $series_rows = $series->get();
            foreach ($series_rows as $key => $row) {
                $next_displays[$key]['title'] = $row->title;
                $next_displays[$key]['show_time'] = $row->show_time;
                if ($current_weekday <= $row->week_day) {
                    // show will be aired in the current week
                    $next_displays[$key]['date'] = date("Y-m-d", strtotime($this->week_day_names[$row->week_day]." this week"));
                } else {
                    $next_week = strtotime('next week');
                    $next_displays[$key]['date'] = date("Y-m-d", strtotime($this->week_day_names[$row->week_day], $next_week));
                }
            }
        }
        echo json_encode($next_displays)."\n";
        //echo $series->toJson();
        die;
    }
}

$exo_tv_series = new ExoTvSeries();
$exo_tv_series->get_next_series();