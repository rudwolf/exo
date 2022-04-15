<?php
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

class ExoTvSeries {
    public $capsule;

    private $week_day_names;

    public function __construct($connection = [])
    {
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

    /**
     * Getter for The Next Series
     *
     * @param string $date_time This should be a date in a human format, ISO or any formar that can used to be converted into a time data like 'now' or 'next week', it defaults to current time and date
     * @param string $title This should contain the full title or any part of it
     * @param string $time_zone This should be a valid time zone like 'Europe/London', it defaults to UTC
     * @return string Returns a json string of the results found based on the selected criteria
     */
    public function get_next_series($date_time = '', $title = '', $time_zone = '')
    {
        header('Content-Type: application/json;charset=utf-8');
        $series = Capsule::table('tv_series_intervals')->join('tv_series', 'tv_series_intervals.id_tv_series', '=', 'tv_series.id')->select('tv_series.title as title', 'tv_series.channel as channel', 'tv_series.gender as gender', 'tv_series_intervals.week_day as week_day', 'tv_series_intervals.show_time as show_time')
        ->orderBy('tv_series_intervals.week_day', 'asc')
        ->orderBy('tv_series_intervals.show_time', 'asc')
        ->orderBy('tv_series.title', 'asc');
        if (!empty($title)) {
            //search for a specific title
            $series->where('tv_series.title', 'like', "%$title%");
        } else {
            $title = 'EMPTY';
        }

        if (!empty($time_zone)) {
            date_default_timezone_set($time_zone);
        }

        $next_displays = [];

        if (!empty($date_time)) {
            $current_weekday = date('w', strtotime($date_time));
            $week_zone = strtotime($date_time);
        } else {
            $current_weekday = date('w');
            $week_zone = strtotime("now");
            $date_time = date('Y-m-d H:i:s');
        }

        $selected_weekday = date('w',strtotime($date_time));
        $selected_time = date('H:i:s',strtotime($date_time));

        $series_rows = $series->get();
        $next_week = strtotime('next week', $week_zone);

        foreach ($series_rows as $key => $row) {
            $next_displays[$key]['title'] = $row->title;
            $next_displays[$key]['channel'] = $row->channel;
            $next_displays[$key]['gender'] = $row->gender;
            $next_displays[$key]['show_time'] = $row->show_time;
            $next_displays[$key]['week_day'] = $row->week_day;
            if ($current_weekday <= $row->week_day || ( $row->week_day == 0 && $current_weekday >= 0) ) {
                // show will be aired in current week
                if ($current_weekday <= $row->week_day && $selected_time <= $row->show_time) {
                    $next_displays[$key]['date'] = date("Y-m-d", strtotime($this->week_day_names[$row->week_day] ." this week", $week_zone));
                } else {
                    // show will be aired next week
                    $next_displays[$key]['date'] = date("Y-m-d", strtotime($this->week_day_names[$row->week_day], $next_week));
                }
            } else {
                // show will be aired next week
                $next_displays[$key]['date'] = date("Y-m-d", strtotime($this->week_day_names[$row->week_day], $next_week));
            }
        }

        usort($next_displays, function($a, $b) {
            $t1 = strtotime($a['date']);
            $t2 = strtotime($b['date']);
            return $t1 - $t2;
        });

        $return = [
            'title_filter' => $title,
            'date_time_selected' => $date_time,
            'week_day_selected' => $selected_weekday,
            'time_selected' => $selected_time,
            'next_displays' => $next_displays
        ];

        echo json_encode($return)."\n";
        die;
    }
}

$exo_tv_series = new ExoTvSeries();
$exo_tv_series->get_next_series();