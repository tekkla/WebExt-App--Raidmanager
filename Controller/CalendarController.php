<?php
namespace Web\Apps\Raidmanager\Controller;

use Web\Framework\Lib\Controller;

class CalendarController extends Controller
{
    public function Index($id_raid)
    {
        $data = $this->model->getCalendar($id_raid);

        $this->setVar(array(
            'list_recent' => $data->recent,
            'list_future' => $data->future,
            'headline' => $this->txt('calendar_headline'),
            'no_raids' => $this->txt('calendar_none'),
            'current' => $this->txt('calendar_current'),
            'future' => $this->txt('calendar_future'),
            'recent' => $this->txt('calendar_recent')
        ));
    }

    public function WidgetNextRaid()
    {
        if (!$this->app->generalAccess())
            return false;

        $data = $this->model->nextRaid();

        if (!$data)
            return false;

        $this->setVar('raid', $data);
    }

    public function Menu()
    {
        $this->setVar('raid', $this->model->getMenu());
    }
}
?>
